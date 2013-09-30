<?php
namespace Sutra\Component\String\Tests;

use Sutra\Component\String\MbUtf8Helper;

class MbUtf8HelperTest extends TestCase
{
    public static $instance;

    public static function setUpBeforeClass()
    {
        static::$instance = new MbUtf8Helper();
    }

    public static function cleanProvider()
    {
        return array(
            array('', ''),
            array(array("a\nb", ''), array("a\nb", '')),
            // This only works if modified with a hex editor; anything else will apply encoding and break it
            // array('IÃÂÃÂ±tÃÂÃÂ«rnÃÂÃÂ¢tiÃÂÃÅnÃÂÃÂ lizÃÂÃÂ tiÃÂÃÅžn', 'IÃÂÃÂ±tÃÂÃÂ«rnÃÂÃÂ¢tiÃÂÃÅnÃÂÃÂ lizÃÂÃÂ tiÃÂÃÅžn'),
            // array('IÃÃnÃ¢tiÃŽnÃ lizÃŠtiÃžn', strtolower(ICONV_IMPL) != 'unknown' ? 'InÃ¢tiÃŽnÃ lizÃŠtiÃžn' : 'I'),
            );
    }

    public static function lengthProvider()
    {
        return array(
            array('my string', strlen('my string')),
            array('العربيaaaa', 10),
            array('《》『', 3),
        );
    }

    /**
     * @dataProvider lengthProvider
     */
    public function testLength($input, $output)
    {
        $this->assertEquals($output, static::$instance->length($input));
    }

    public static function lowerProvider()
    {
        return array(
            array('Ξ', 'ξ'),
            array('Ŧ', 'ŧ'),
            array('ABC', 'abc'),
            );
    }

    /**
     * @dataProvider lowerProvider
     */
    public function testLower($input, $output)
    {
        $this->assertEquals($output, static::$instance->lower($input));
    }

    /**
     * @dataProvider lowerProvider
     */
    public function testUpper($output, $input)
    {
        $this->assertEquals($output, static::$instance->upper($input));
    }

    public static function indexOfProvider()
    {
        return array(
            array('my string', 's', 0, 3),
            array('a string', 'y', 0, false),
            array('AŦBC', 'Ŧ', 1, 1),
            array('AŦBCD', 'F', 0, false),
            );
    }

    /**
     * @dataProvider indexOfProvider
     */
    public function testIndexOf($string, $needle, $offset, $expected)
    {
        $this->assertSame($expected, static::$instance->indexOf($string, $needle, $offset));
    }

    /**
     * @dataProvider cleanProvider
     */
    public function testClean($input, $output)
    {
        $this->assertEquals($output, static::$instance->clean($input));
    }

    public static function substrProvider()
    {
        return array(
            array('my string', 3, null, 'string'),
            array('العربيaaaa', 3, null, 'ربيaaaa'),
            array('《》『', 1, 1, '》'),
            array('a', 2, 1, false),
            array('abc', -1, null, 'c'),
        );
    }

    /**
     * @dataProvider substrProvider
     */
    public function testSubstr($string, $start, $length, $output)
    {
        $this->assertSame($output, static::$instance->substr($string, $start, $length));
    }
}
