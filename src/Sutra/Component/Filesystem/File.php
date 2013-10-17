<?php
namespace Sutra\Component\Filesystem;

use Sutra\Component\Filesystem\Exception\EnvironmentException;

/**
 * Represents a file.
 *
 * @replaces fFile
 */
class File extends \SplFileInfo
{
    protected static $fs;
    protected $path;
    protected $file = false;
    protected $deleted = false;

    public function __construct($path, $skipChecks = false)
    {
        static::initializeFilesystem();

        $this->file = (string) $path;
    }

    public function __toString()
    {
        return $this->file;
    }

    public function delete()
    {
        if ($this->deleted) {
            return;
        }

        if (!$this->getParent()->isWritable()) {
            throw new EnvironmentException('The file, %s, can not be deleted because the directory containing it is not writable', $this->file);
        }

        $ret = unlink($this->file);

        if (!$ret) {
            throw new \UnexpectedValueException(sprintf('Could not delete %s', $this->file));
        }
    }

    public function getParent()
    {
        return new Directory(static::$fs->getPathInfo($this->file, 'dirname'));
    }

    public function getSize($format = false, $decimalPlaces = 1)
    {
        $size = sprintf("%u", filesize($this->file));

        if (!$format) {
            return (int) $size;
        }

        return static::$fs->formatFilesize($size, $decimalPlaces);
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
