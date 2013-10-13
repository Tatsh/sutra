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

    public static function padProvider()
    {
        return array(
            array('ascii', 10, ' ', '  ascii   '),
            array('العربيaaaa', 3, ' ', 'العربيaaaa'),
            array('العربيaaaa', 16, ' ', '   العربيaaaa   '),
            array('《》『', 6, '  ', '  《》『 '),
        );
    }

    /**
     * @dataProvider padProvider
     */
    public function testPad($str, $length, $padStr, $output)
    {
        $this->assertEquals($output, static::$instance->pad($str, $length, $padStr));
    }

    public static function padLeftProvider()
    {
        return array(
            array('ascii', 10, ' ', '     ascii'),
            array('العربيaaaa', 3, ' ', 'العربيaaaa'),
            array('العربيaaaa', 16, ' ', '      العربيaaaa'),
            array('《》『', 6, '  ', '   《》『'),
        );
    }

    /**
     * @dataProvider padLeftProvider
     */
    public function testPadLeft($str, $length, $padLeftStr, $output)
    {
        $this->assertEquals($output, static::$instance->padLeft($str, $length, $padLeftStr));
    }

    public static function padRightProvider()
    {
        return array(
            array('ascii', 10, ' ', 'ascii     '),
            array('العربيaaaa', 3, ' ', 'العربيaaaa'),
            array('العربيaaaa', 16, ' ', 'العربيaaaa      '),
            array('《》『', 6, '  ', '《》『   '),
        );
    }

    /**
     * @dataProvider padRightProvider
     */
    public function testPadRight($str, $length, $padRightStr, $output)
    {
        $this->assertEquals($output, static::$instance->padRight($str, $length, $padRightStr));
    }

    public static function caseInsensitiveReplaceProvider()
    {
        return array(
            array('some string AaA', array('a', 'b', 'c'), 'd', 'some string ddd'),
            array('some string AaA', 'a', 'd', 'some string ddd'),
        );
    }

    /**
     * @dataProvider caseInsensitiveReplaceProvider
     */
    public function testCaseInsensitiveReplace($string, $find, $replace, $output)
    {
        $this->assertEquals($output, static::$instance->caseInsensitiveReplace($string, $find, $replace));
    }

    public static function reverseProvider()
    {
        return array(
            array('العربيaaaa', 'aaaaيبرعلا'),
            array('abcdῳ', 'ῳdcba'),
            array('Ǿǽ⅔', '⅔ǽǾ'),
            array('տպագրության', 'նայթւորգապտ'),
            array('元素週期表', '表期週素元'),
            array('𠜎 𠜱 𠝹 𠱓 𠱸 𠲖 𠳏 𠳕', '𠳕 𠳏 𠲖 𠱸 𠱓 𠝹 𠜱 𠜎'), // 4-byte
        );
    }

    /**
     * @dataProvider reverseProvider
     */
    public function testReverse($input, $output)
    {
        $this->assertEquals($output, static::$instance->reverse($input));
    }

    public function testWordwWrapAscii()
    {
        $str =<<<STR
Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
STR;
        $output =<<<STR
Lorem Ipsum is simply dummy text of the printing and typesetting industry.
Lorem Ipsum has been the industry's standard dummy text ever since the
1500s, when an unknown printer took a galley of type and scrambled it to
make a type specimen book. It has survived not only five centuries, but
also the leap into electronic typesetting, remaining essentially unchanged.
It was popularised in the 1960s with the release of Letraset sheets
containing Lorem Ipsum passages, and more recently with desktop publishing
software like Aldus PageMaker including versions of Lorem Ipsum.
STR;
        $this->assertEquals($output, static::$instance->wordWrap($str));

        $str =<<<STR
위키백과(Wiki百科, 듣기 (도움말·정보)) 혹은 위키피디어(Wikipedia 듣기 (도움말·정보)는 모두가 함께 만들어 가며 누구나 자유롭게 쓸 수 있는 다언어판 인터넷 백과사전이다. 대표적인 집단 지성의 사례로 평가받고 있다. 배타적인 저작권 라이선스가 아닌 자유 콘텐츠로 사용에 제약을 받지 않는다.
STR;
        $output =<<<STR
위키백과(Wiki百科, 듣기 (도움말·정보)) 혹은 위키피디어(Wikipedia 듣기 (도움말·정보)는 모두가 함께 만들어 가며
누구나 자유롭게 쓸 수 있는 다언어판 인터넷 백과사전이다. 대표적인 집단 지성의 사례로 평가받고 있다. 배타적인 저작권 라이선스가
아닌 자유 콘텐츠로 사용에 제약을 받지 않는다.
STR;
        $this->assertEquals($output, static::$instance->wordWrap($str));

        $output =<<<STR
위키백과(Wiki百
科, 듣기
(도움말·정보))
혹은
위키피디어(Wiki
pedia 듣기
(도움말·정보)는
모두가 함께
만들어 가며
누구나 자유롭게
쓸 수 있는
다언어판 인터넷
백과사전이다.
대표적인 집단
지성의 사례로
평가받고 있다.
배타적인 저작권
라이선스가 아닌
자유 콘텐츠로
사용에 제약을
받지 않는다.
STR;
        $this->assertEquals($output, static::$instance->wordWrap($str, 10, "\n", true));
    }
}
