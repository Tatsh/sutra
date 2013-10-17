<?php
namespace Sutra\Component\Filesystem;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Sutra\Component\Filesystem\Exception\ProgrammerException;

/**
 * File system class.
 *
 * @replaces fFilesystem This is *not* the transactional file system.
 */
class Filesystem extends SymfonyFilesystem
{
    /**
     * Path info cache.
     *
     * @var array
     */
    protected $pathInfo = array();

    /**
     * Web path translations.
     *
     * @var array
     */
    protected $webPathTranslations = array();

    /**
     * Makes sure a directory has a `/` or `\` at the end.
     *
     * @param string $directory The directory to check.
     *
     * @return string The directory name in canonical form.
     *
     * @replaces fDirectory::makeCanonical
     */
    public function makeCanonical($directory)
    {
        if (substr($directory, -1) != '/' && substr($directory, -1) != '\\') {
            $directory .= DIRECTORY_SEPARATOR;
        }

        return $directory;
    }

    /**
     * Takes a file size including a unit of measure (i.e. kb, GB, M) and
     *   converts it to bytes.
     *
     * Sizes are interpreted using base 2, not base 10. Sizes above 2GB may not
     *   be accurately represented on 32-bit operating systems.
     *
     * @param string $size The size to convert to bytes.
     *
     * @return integer The number of bytes represented by the size.
     *
     * @replaces ::convertToBytes
     */
    public function convertToBytes($size)
    {
        if (!preg_match('#^(\d+(?:\.\d+)?)\s*(k|m|g|t)?(i|ilo|ega|era|iga)?( )?b?(yte(s)?)?$#D', strtolower(trim($size)), $matches)) {
            throw new ProgrammerException('The size specified, %s, does not appears to be a valid size', $size);
        }

        if (empty($matches[2])) {
            $matches[2] = 'b';
        }

        $sizeMap = array(
            'b' => 1,
            'k' => 1024,
            'm' => 1048576,
            'g' => 1073741824,
            't' => 1099511627776
        );

        return round($matches[1] * $sizeMap[$matches[2]]);
    }

    /**
     * Gets path information.
     *
     * @param string  $path     Path to file or directory.
     * @param string  $element  Path information to retrieve. One of: dirname,
     *   basename, extension, filename.
     * @param boolean $useCache Use the path info cache.
     *
     * @return string|array Array of path information or piece requested in
     *   second argument.
     *
     * @throws ProgrammerException If second argument is invalid.
     *
     * @replaces ::getPathInfo
     */
    public function getPathInfo($path, $element = null, $useCache = true)
    {
        $validElements = array('dirname', 'basename', 'extension', 'filename');

        if ($element !== null && !in_array($element, $validElements)) {
            throw new ProgrammerException('The element must be one of the following: %s', join(', ', $validElements));
        }

        if ($useCache && isset($this->pathInfo[$path])) {
            return $element ? $this->pathInfo[$path][$element] : $this->pathInfo[$path];
        }

        $pathInfo = $this->pathInfo[$path] = pathinfo($path);

        if (!isset($pathInfo['extension'])) {
            $pathInfo['extension'] = null;
        }

        // NOTE Does this ever happen?
        // @codeCoverageIgnoreStart
        if (!isset($pathInfo['filename'])) {
            $pathInfo['filename'] = preg_replace('#\.' . preg_quote($pathInfo['extension'], '#') . '$#D', '', $pathInfo['basename']);
        }
        // @codeCoverageIgnoreEnd

        $pathInfo['dirname'] .= DIRECTORY_SEPARATOR;

        $this->pathInfo[$path] = $pathInfo;

        if ($element) {
            return $pathInfo[$element];
        }

        return $pathInfo;
    }

    /**
     * Clears path information cache.
     *
     * @param string $path If given, will only clear cache for the given path.
     */
    public function clearPathInfoCache($path = null)
    {
        if ($path) {
            unset($this->pathInfo[$path]);

            return;
        }

        $this->pathInfo = array();
    }

    /**
     * Makes a path safe for a URL.
     *
     * @param string $filename File name or directory.
     *
     * @return string String made safe for URL.
     *
     * @replaces ::makeURLSafe
     */
    public function makeUrlSafe($filename)
    {
        $filename = strtolower(trim($filename));
        $filename = str_replace("'", '', $filename);
        $filename = preg_replace('#[^a-z0-9\-\.]+#', '_', $filename);

        return $filename;
    }

    /**
     * Makes a unique file name similar to another file name.
     *
     * @param string $file         File name.
     * @param string $newExtension New extension to use.
     *
     * @return string File name to use.
     *
     * @replaces ::makeUniqueName
     */
    public function makeUniqueName($file, $newExtension = null)
    {
        $this->clearPathInfoCache($file);

        $info = $this->getPathInfo($file);

        // Change file extension
        if ($newExtension !== null) {
            $newExtension = $newExtension ? '.'.$newExtension : $newExtension;
            $file = $info['dirname'] . $info['filename'] . $newExtension;
            $info = $this->getPathInfo($file);
        }

        // If there is an extension, be sure to add . before it
        $extension = $info['extension'] ? '.'.$info['extension'] : '';

        // Remove _copy# from the filename to start
        $file = preg_replace('#_copy(\d+)' . preg_quote($extension, '#') . '$#D', $extension, $file);

        // Look for a unique name by adding _copy# to the end of the file
        while (file_exists($file)) {
            $info = $this->getPathInfo($file);
            if (preg_match('#_copy(\d+)' . preg_quote($extension, '#') . '$#D', $file, $match)) {
                $file = preg_replace('#_copy(\d+)' . preg_quote($extension, '#') . '$#D', '_copy' . ($match[1]+1) . $extension, $file);
            }
            else {
                $file = $info['dirname'] . $info['filename'] . '_copy1' . $extension;
            }
        }

        return $file;
    }

    /**
     * Returns a formatted size string with highest possible unit of a size in
     *   bytes for human readability.
     *
     * @param integer $bytes         Size in bytes.
     * @param integer $decimalPlaces Decimal places.
     *
     * @return string Formatted size string.
     *
     * @replaces ::formatFilesize
     */
    public function humanizeSize($bytes, $decimalPlaces = 1)
    {
        if ($bytes < 0) {
            $bytes = 0;
        }

        $suffixes = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
        $sizes = array(1, 1024, 1048576, 1073741824, 1099511627776);
        $suffix = (!$bytes) ? 0 : floor(log($bytes) / 6.9314718);
        $decimalPlaces = $suffix == 0 ? 0 : $decimalPlaces;

        return sprintf('%s %s', number_format($bytes / $sizes[$suffix], $decimalPlaces), $suffixes[$suffix]);
    }

    /**
     * Adds a web path translation.
     *
     * @param string $searchPath  Substring to look for.
     * @param string $replacePath Replacement for the substring.
     *
     * @replaces ::addWebPathTranslation
     */
    public function addWebPathTranslation($searchPath, $replacePath)
    {
        // Ensure we have the correct kind of slash for the OS being used
        $searchPath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $searchPath);
        $replacePath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $replacePath);
        $this->webPathTranslations[$searchPath] = $replacePath;
    }

    /**
     * Translates a path to a web path.
     *
     * @param string $path Path to translate.
     *
     * @return string Web path version of `$path` argument.
     *
     * @replaces ::translateToWebPath
     */
    public function translateToWebPath($path)
    {
        $translations = array(realpath($_SERVER['DOCUMENT_ROOT']) => '') + $this->webPathTranslations;

        foreach ($translations as $search => $replace) {
            $path = preg_replace(
                '#^' . preg_quote($search, '#') . '#',
                strtr($replace, array('\\' => '\\\\', '$' => '\\$')),
                $path
            );
        }

        return str_replace('\\', '/', $path);
    }

    /**
     * Creates the correct type of object for a path given.
     *
     * @param string $path Path to use.
     *
     * @return Directory|File `Directory` or `File` object.
     *
     * @replaces ::createObject
     */
    public function createObject($path)
    {
        if (!is_readable($path)) {
            throw new ValidationException('The path specified, %s, does not exist or is not readable', $path);
        }

        if (is_dir($path)) {
            return new Directory($path, true);
        }

        // TODO Image here

        return new File($path, true);
    }
}
