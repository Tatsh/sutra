<?php
namespace Sutra\String\Tests;

use Sutra\String\UTF8Helper;

class UTF8HelperTest extends TestCase
{
    public static $instance;

    public static function setUpBeforeClass()
    {
        static::$instance = new UTF8Helper();
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
            array('', NULL, array('')),
            array(' ', NULL, array(' ')),
            array("a\nb", NULL, array("a", "\n", "b")),
            array("\na\nb\n\n", NULL, array("\n", "a", "\n", "b", "\n", "\n")),
            array('abcdefg', NULL, array('a', 'b', 'c', 'd', 'e', 'f', 'g')),
            array('Iñtërnâtiônàlizætiøn', NULL, array('I', 'ñ', 't', 'ë', 'r', 'n', 'â', 't', 'i', 'ô', 'n', 'à', 'l', 'i', 'z', 'æ', 't', 'i', 'ø', 'n')),
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

    /**
     * @dataProvider titleProvider
     */
    public function testTitle($input, $output)
    {
        $this->assertEquals($output, static::$instance->title($input));
    }
}
