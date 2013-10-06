<?php
namespace Sutra\Component\String\Tests;

use Sutra\Component\String\Utf8Helper;

class Utf8HelperTest extends TestCase
{
    public static $instance;

    public static function setUpBeforeClass()
    {
        static::$instance = new Utf8Helper();
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

    // Just to cover the constructor
    public function testConstructor()
    {
        $instance = new Utf8Helper();
        $this->assertInstanceOf('Sutra\Component\String\Utf8HelperInterface', $instance);
    }

    /**
     * @dataProvider cleanProvider
     */
    public function testClean($input, $output)
    {
        $this->assertEquals($output, static::$instance->clean($input));
    }

    public static function splitProvider()
    {
        return array(
            array('', null, array('')),
            array(' ', null, array(' ')),
            array("a\nb", null, array("a", "\n", "b")),
            array("\na\nb\n\n", null, array("\n", "a", "\n", "b", "\n", "\n")),
            array('abcdefg', null, array('a', 'b', 'c', 'd', 'e', 'f', 'g')),
            array('Iñtërnâtiônàlizætiøn', null, array('I', 'ñ', 't', 'ë', 'r', 'n', 'â', 't', 'i', 'ô', 'n', 'à', 'l', 'i', 'z', 'æ', 't', 'i', 'ø', 'n')),
            array("a\nb", '', array("a", "\n", "b")),
            array("a\nb", 'a', array("", "\nb")),
            array("a\nb", "\n", array("a", "b")),
            array('Iñtërnâtiônàlizætiøn', 'nà', array('Iñtërnâtiô', 'lizætiøn')),
        );
    }

    /**
     * @dataProvider splitProvider
     */
    public function testSplit($string, $delimiter, $output)
    {
        $this->assertEquals($output, static::$instance->split($string, $delimiter));
    }

    public static function titleProvider()
    {
        return array(
            array('hello', 'Hello'),
            array('This is a longer phrase', 'This Is A Longer Phrase'),
            array('This phrase (contains some) punctuation/that might cause "issues"', 'This Phrase (Contains Some) Punctuation/That Might Cause "Issues"'),
            array("Single prime \"apostrophes\" aren't an issue, and 'single prime' quotes work", "Single Prime \"Apostrophes\" Aren't An Issue, And 'Single Prime' Quotes Work"),
            array("Apostrophes aren’t an issue", "Apostrophes Aren’t An Issue"),
            array("‘single’ and “double” quotes are handled too", "‘Single’ And “Double” Quotes Are Handled Too"),
            array("Hyphens-get-handled-too", "Hyphens-Get-Handled-Too"),
            array("\\'Backslashed single quote'", "\\'Backslashed Single Quote'"),
        );
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
     * @dataProvider indexOfProvider
     */
    public function testLastIndexOf($string, $needle, $offset, $expected)
    {
        $this->assertSame($expected, static::$instance->lastIndexOf($string, $needle, $offset));
    }

    public static function asciiProvider()
    {
        return array(
            array('my string has العربي', 'my string has '),
            array('道德經', ''),
            array('maybe this is uñicode', 'maybe this is unicode'),
        );
    }

    /**
     * @dataProvider asciiProvider
     */
    public function testAscii($string, $expected)
    {
        $this->assertEquals($expected, static::$instance->ascii($string));
    }

    /**
     * @dataProvider titleProvider
     */
    public function testTitle($input, $output)
    {
        $this->assertEquals($output, static::$instance->title($input));
    }

    public static function substrProvider()
    {
        return array(
            array('my string', 3, null, 'string'),
            // array('العربيaaaa', 3, null, 'ربيaaaa'), // Doesn't work yet
            array('《》『', 4, null, false),
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
}
