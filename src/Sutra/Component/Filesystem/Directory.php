<?php
namespace Sutra\Component\Filesystem;

use Sutra\Component\Filesystem\Exception\EnvironmentException;
use Sutra\Component\Filesystem\Exception\ProgrammerException;
use Sutra\Component\Filesystem\Exception\ValidationException;
use Symfony\Component\Finder\Finder;

/**
 * Represents a directory on a file system.
 */
class Directory
{
    /**
     * Methods allowed when filtering in `scan` method with the `Finder`
     *   object.
     *
     * @var array
     */
    protected static $allowedFinderMethodsForFilter = array(
        'directories' => true,
        'files' => true,
        'depth' => true,
        'name' => true,
        'notName' => true,
        'contains' => true,
        'notContains' => true,
        'path' => true,
        'notPath' => true,
        'size' => true,
        'exclude' => true,
        'ignoreDotFiles' => true,
        'ignoreVCS' => true,
        'addVCSPattern' => true,
        'filter' => true,
        'followLinks' => true,
        'date' => true,
    );

    /**
     * Filesystem object.
     *
     * @var Filesystem
     */
    protected static $fs;

    /**
     * If this directory has been deleted.
     *
     * @var boolean
     */
    protected $deleted = false;

    /**
     * Full path to the directory.
     *
     * @var string
     */
    protected $directory;

    /**
     * Constructor.
     *
     * @param string  $directory  Path to directory.
     * @param boolean $skipChecks Skips validation. This may be desired for
     *   performance reasons.
     *
     * @todo Make a container for static Filesystem instance across `File` and
     *   `Directory` classes.
     */
    public function __construct($directory, $skipChecks = false)
    {
        static::initializeFilesystem();

        if (!$skipChecks) {
            if (!is_readable($directory)) {
                throw new ValidationException('The directory specified, %s, does not exist or is not readable', $directory);
            }

            if (!is_dir($directory)) {
                throw new ValidationException('The directory specified, %s, is not a directory', $directory);
            }
        }

        $this->directory = static::$fs->makeCanonical(realpath($directory));
    }

    /**
     * Removes all files and directories inside the directory.
     *
     * @replaces #clear
     */
    public function clear()
    {
        $this->tossIfDeleted();

        foreach ($this->scan() as $file) {
            $file->delete();
        }
    }

    /**
     * Deletes the directory recursively.
     *
     * @replaces #delete
     */
    public function delete()
    {
        $this->tossIfDeleted();

        if (!$this->getParent()->isWritable()) {
            throw new EnvironmentException('The directory %s cannot be deleted because the parent directory is not writable', $this->directory);
        }

        $this->clear();
        rmdir($this->directory);

        $this->deleted = true;
    }

    /**
     * Gets the name of the directory.
     *
     * @return string The name of the directory.
     *
     * @replaces #getName
     */
    public function getName()
    {
        return static::$fs->getPathInfo($this->directory, 'basename');
    }

    /**
     * Gets the parent directory.
     *
     * @return Directory Object representing the parent directory.
     *
     * @replaces #getParent
     */
    public function getParent()
    {
        $this->tossIfDeleted();

        $dirname = static::$fs->getPathInfo($this->directory, 'dirname');

        if ($dirname == $this->directory) {
            throw new EnvironmentException('The current directory does not have a parent directory');
        }

        return new static($dirname);
    }

    /**
     * Gets the directory's current path.
     *
     * @replaces #getPath Note that translating to web path is no longer
     *   supported here. Use the `Filesystem` class directly.
     */
    public function getPath()
    {
        $this->tossIfDeleted();

        return $this->directory;
    }

    /**
     * Gets the disk usage of the directory and all files and folders contained within
     *
     * This method may return incorrect results if files over 2GB exist and the
     * server uses a 32 bit operating system
     *
     * @param boolean $format        If the filesize should be formatted for
     *   human readability.
     * @param integer $decimalPlaces The number of decimal places to format to
     *   (if enabled).
     *
     * @return integer|string If formatted, a string with filesize in
     *   B/KiB/etc, otherwise an integer
     *
     * @replaces #getSize
     */
    public function getSize($format = false, $decimalPlaces = 1)
    {
        $this->tossIfDeleted();

        $size = 0;

        foreach ($this->scan() as $child) {
            $size += $child->getSize();
        }

        if (!$format) {
            return $size;
        }

        return static::$fs->humanizeSize($size, $decimalPlaces);
    }

    /**
     * Checks if the directory is writable.
     *
     * @return boolean If the directory is writable.
     */
    public function isWritable()
    {
        $this->tossIfDeleted();

        return is_writable($this->directory);
    }

    /**
     * Moves the directory.
     *
     * @param Directory|string $newParentDir Parent directory (string or object).
     * @param boolean          $overwrite    Overwrite an existing directory.
     *
     * @replaces #move Note that the original class claimed to return `$this`
     *   but called `#rename()` which always returned `null`. This version
     *   retains the incorrectly documented behaviour.
     */
    public function move($newParentDir, $overwrite = false)
    {
        if (!($newParentDir instanceof static)) {
            $newParentDir = new static($newParentDir);
        }

        if (strpos($newParentDir->getPath(), $this->getPath()) === 0) {
            throw new ValidationException('It is not possible to move a directory into one of its sub-directories');
        }

        return $this->rename($newParentDir->getPath().$this->getName(), $overwrite);
    }

    /**
     * Renames the directory.
     *
     * @param string  $newDirName New directory name.
     * @param boolean $overwrite  Overwrite an existing directory.
     *
     * @replaces #rename
     */
    public function rename($newDirName, $overwrite = false)
    {
        $this->tossIfDeleted();

        if (!$this->getParent()->isWritable()) {
            throw new EnvironmentException('The directory, %s, can not be renamed because the directory containing it is not writable', $this->directory);
        }

        // If the dirname does not contain any folder traversal, rename the dir in the current parent directory
        if (preg_match('#^[^/\\\\]+$#D', $newDirName)) {
            $newDirName = $this->getParent()->getPath().$newDirName;
        }

        $info = static::$fs->getPathInfo($newDirName);

        if (!file_exists($info['dirname'])) {
            throw new ProgrammerException('The new directory name specified, %s, is inside of a directory that does not exist', $newDirName);
        }

        if (file_exists($newDirName)) {
            if (!is_writable($newDirName)) {
                throw new EnvironmentException('The new directory name specified, %s, already exists, but is not writable', $newDirName);
            }

            if (!$overwrite) {
                $newDirName = static::$fs->makeUniqueName($newDirName);
            }
            else {
                $dir = new static($newDirName);
                $dir->delete();
            }
        }
        else {
            $parentDir = new static($info['dirname']);
            if (!$parentDir->isWritable()) {
                throw new EnvironmentException('The new directory name specified, %s, is inside of a directory that is not writable', $parentDir);
            }
        }

        rename($this->directory, $newDirName);

        $this->directory = static::$fs->makeCanonical(realpath($newDirName));
    }

    /**
     * Scans the directory for files and directories non-recursively.
     *
     * @param mixed $filter Array of methods to arguments for `Finder`,
     *   `\Closure` instance for filter, strings 'directories' or 'files',
     *   regular expression, or glob string.
     *
     * @return array Array of `File` and `Directory` objects.
     *
     * @see Finder
     *
     * @replaces #scanRecursive
     */
    public function scan($filter = null)
    {
        $this->tossIfDeleted();

        $objects = array();
        $finder = new Finder();
        $allowedFinderMethodsForFilter = static::$allowedFinderMethodsForFilter;

        unset($allowedFinderMethodsForFilter['depth']);

        $finder
            ->in($this->directory)
            ->depth('< 1')
            ->ignoreDotFiles(false)
            ->ignoreVCS(false);

        if ($filter) {
            if (is_array($filter)) {
                foreach ($filter as $method => $arg) {
                    if (!isset($allowedFinderMethodsForFilter[$method])) {
                        continue;
                    }

                    $finder->$method($arg);
                }
            }
            else if ($filter instanceof \Closure) {
                $finder->filter($filter);
            }
            else if ($filter === 'directories') {
                $finder->directories();
            }
            else if ($filter === 'files') {
                $finder->files();
            }
            else {
                $finder->name($filter);
            }
        }

        foreach ($finder as $file) {
            $objects[] = static::$fs->createObject($file);
        }

        return $objects;
    }

    /**
     * Scans the directory for files and directories recursively.
     *
     * @param mixed $filter Array of methods to arguments for `Finder`,
     *   \Closure instance for filter, strings 'directories' or 'files',
     *   regular expression, or glob string.
     *
     * @return array Array of `File` and `Directory` objects.
     *
     * @see Finder
     *
     * @replaces #scanRecursive
     */
    public function scanRecursive($filter = null)
    {
        $this->tossIfDeleted();

        $objects = array();
        $finder = new Finder();

        $finder
            ->in($this->directory)
            ->ignoreDotFiles(false)
            ->ignoreVCS(false);

        if ($filter) {
            if (is_array($filter)) {
                foreach ($filter as $method => $arg) {
                    if (!isset(static::$allowedFinderMethodsForFilter[$method])) {
                        continue;
                    }

                    $finder->$method($arg);
                }
            }
            else if ($filter instanceof \Closure) {
                $finder->filter($filter);
            }
            else if ($filter === 'directories') {
                $finder->directories();
            }
            else if ($filter === 'files') {
                $finder->files();
            }
            else {
                $finder->name($filter);
            }
        }

        foreach ($finder as $file) {
            $objects[] = static::$fs->createObject($file);
        }

        return $objects;
    }

    /**
     * Returns the full filesystem path for the directory.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->directory;
    }

    /**
     * Throws exception if the directory is deleted.
     *
     * @throws ProgrammerException If the directory is deleted.
     */
    protected function tossIfDeleted()
    {
        if ($this->deleted) {
            throw new ProgrammerException('The action requested cannot be performed because the directory has been deleted');
        }
    }

    /**
     * Lazily loads Filesytem object.
     */
    protected static function initializeFilesystem()
    {
        if (!static::$fs) {
            static::$fs = new Filesystem();
        }
    }
}
