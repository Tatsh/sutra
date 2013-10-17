<?php
namespace Sutra\Component\Filesystem\Tests;

use Sutra\Component\Filesystem\Filesystem;

class FilesystemTest extends TestCase
{
    protected static $instance;

    public static function setUpBeforeClass()
    {
        static::$instance = new Filesystem();
    }

    public static function convertToBytesProvider()
    {
        return array(
            array('1 MB', 1048576),
            array('55.27m', 57954796),
            array(46, 46),
            array('1.1k', 1126),
            array('1.5 tera bytes', 1649267441664),
            array('1 MiB', 1048576),
        );
    }

    /**
     * @dataProvider convertToBytesProvider
     */
    public function testConvertToBytes($input, $output)
    {
        $this->assertEquals($output, static::$instance->convertToBytes($input));
    }

    /**
     * @expectedException Sutra\Component\Filesystem\Exception\ProgrammerException
     * @expectedExceptionMessage The size specified, -400, does not appears to be a valid size
     */
    public function testConvertToBytesNegativeNumber()
    {
        static::$instance->convertToBytes(-400);
    }

    /**
     * @expectedException Sutra\Component\Filesystem\Exception\ProgrammerException
     * @expectedExceptionMessage The size specified, 1 block, does not appears to be a valid size
     */
    public function testConvertToBytesBadString()
    {
        static::$instance->convertToBytes('1 block');
    }

    public static function makeCanonicalProvider()
    {
        return array(
            array(__DIR__, __DIR__.DIRECTORY_SEPARATOR),
            array('a/', 'a/'),
            array('a\\', 'a\\'),
        );
    }

    /**
     * @dataProvider makeCanonicalProvider
     */
    public function testMakeCanonical($input, $output)
    {
        $this->assertEquals($output, static::$instance->makeCanonical($input));
    }

    public function testGetPathInfo()
    {
        $infoKeys = array('dirname', 'basename', 'extension', 'filename');

        $info = static::$instance->getPathInfo(__DIR__);
        foreach ($infoKeys as $key) {
            $this->assertArrayHasKey($key, $info);
        }
        $this->assertNull($info['extension']);

        $info = static::$instance->getPathInfo(__FILE__);
        $this->assertNotNull($info['extension']);

        foreach (array('dirname', 'basename', 'extension', 'filename') as $key) {
            $info = static::$instance->getPathInfo(__FILE__, $key);
            $this->assertInternalType('string', $info);
        }

        // No cache
        foreach (array('dirname', 'basename', 'extension', 'filename') as $key) {
            $info = static::$instance->getPathInfo(__FILE__, $key, false);
            $this->assertInternalType('string', $info);
        }
    }

    public function testClearPathInfoCache()
    {
        static::$instance->clearPathInfoCache();

        $refl = new \ReflectionClass(static::$instance);
        $prop = $refl->getProperty('pathInfo');
        $prop->setAccessible(true);
        $arr = $prop->getValue(static::$instance);

        $this->assertEmpty($arr);

        static::$instance->getPathInfo(__DIR__);
        static::$instance->getPathInfo(__FILE__);
        $arr = $prop->getValue(static::$instance);
        $this->assertNotEmpty($arr);

        static::$instance->clearPathInfoCache(__DIR__);
        $arr = $prop->getValue(static::$instance);
        $this->assertNotEmpty($arr);
        $this->assertArrayNotHasKey(__DIR__, $arr);
    }

    public static function makeUrlSafeProvider()
    {
        return array(
            array('my-filename.a.b"+', 'my-filename.a.b_'),
            array('  extra spaces   ', 'extra_spaces'),
        );
    }

    /**
     * @dataProvider makeUrlSafeProvider
     */
    public function testMakeUrlSafe($input, $output)
    {
        $this->assertEquals($output, static::$instance->makeUrlSafe($input));
    }

    public function testMakeUniqueName()
    {
        $file = __FILE__;
        $unique = static::$instance->makeUniqueName($file);
        $this->assertNotEquals($file, $unique);
        $this->assertRegExp('/_copy1.php$/', $unique);

        $file = $unique;

        touch($file);

        $unique = static::$instance->makeUniqueName($file);
        $this->assertNotEquals($file, $unique);
        $this->assertRegExp('/_copy\d+.php$/', $unique);

        unlink($file);

        $incFile = str_replace('.php', '.inc', __FILE__);
        touch($incFile);

        $unique = static::$instance->makeUniqueName($file, 'inc');
        $this->assertStringEndsWith('_copy1.inc', $unique);

        unlink($incFile);
    }

    public static function translateToWebPathProvider()
    {
        return array(
            array('C:\path\to\something', 'C:/path/to/something'),
            array('/home/myname/', '/home/myname/'),
        );
    }

    /**
     * @dataProvider translateToWebPathProvider
     */
    public function testTranslateToWebPath($input, $output)
    {
        $this->assertEquals($output, static::$instance->translateToWebPath($input));
    }

    public function testAddWebPathTranslation()
    {
        static::$instance->addWebPathTranslation('/home/myname', '/var/www');

        $this->assertEquals('/var/www/a_file.jpeg', static::$instance->translateToWebPath('/home/myname/a_file.jpeg'));
    }

    public static function humanizeSizeProvider()
    {
        return array(
            array(1048576, '1 MiB', 0),
            array(1048576, '1.0 MiB', 1),
            array(1248576, '1.2 MiB', 1),
            array(1248576 * 1024, '1.19 GiB', 2),
            array(0, '0 B', 2), // even with decimal argument still get 0 B
            array(-1, '0 B', 0),
        );
    }

    /**
     * @dataProvider humanizeSizeProvider
     */
    public function testHumanizeSize($input, $output, $decimalPlaces)
    {
        $this->assertEquals($output, static::$instance->humanizeSize($input, $decimalPlaces));
    }

    /**
     * @expectedException Sutra\Component\Filesystem\Exception\ProgrammerException
     * @expectedExceptionMessage The element must be one of the following: dirname, basename, extension, filename
     */
    public function testGetPathInfoInvalidElement()
    {
        static::$instance->getPathInfo('a', 'bad');
    }
}
