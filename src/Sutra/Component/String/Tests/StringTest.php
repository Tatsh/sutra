<?php
namespace Sutra\Component\String\Tests;

use Sutra\Component\String\String;

class StringTest extends TestCase
{
    public function testGetUtf8Helper()
    {
        $helper = String::getUtf8Helper();
        $str = new String('my string');
        $this->assertSame($helper, String::getUtf8Helper());

        $currentHelper = $helper;
        $helper = String::getUtf8Helper(true);
        $str = new String('my string');
        $this->assertSame($currentHelper, String::getUtf8Helper());
        $this->assertNotSame($currentHelper, String::getUtf8Helper(true, true));

        // Go back to MbUtf8Helper if possible
        String::getUtf8Helper(false, true);
        $this->assertNotSame($currentHelper, String::getUtf8Helper());
        if (extension_loaded('mbstring')) {
            $this->assertInstanceOf('Sutra\Component\String\MbUtf8Helper', String::getUtf8Helper());
        }
    }

    /**
     * @expectedException Sutra\Component\String\Exception\ProgrammerException
     * @expectedExceptionMessage String argument must be non-zero-length string
     */
    public function testStringWithNoLength()
    {
        new String(null);
    }

    public function testStringFromObject()
    {
        $str = new String('my string');
        $str2 = new String($str);
        $this->assertSame((string) $str, (string) $str2);
    }

    public function testReplace()
    {
        $str = new String('my string');
        $this->assertEquals('their string', $str->replace('my', 'their'));
        $this->assertNotSame($str, $str->replace('my', 'their'));

        $this->assertEquals('my string', $str->replace('does not exist', 'something'));

        $this->assertEquals('THEIR string', $str->replace('MY', 'THEIR', false));
        $this->assertNotSame($str, $str->replace('MY', 'THEIR', false));
    }

    public function testArrayAccess()
    {
        // offsetSet
        $str = new String('a string to change');
        $str[0] = 'b';
        $this->assertSame('b string to change', (string) $str);

        // offsetGet
        $this->assertSame('s', $str[2]);

        // offsetExists
        $this->assertTrue(isset($str[2]));
        $this->assertFalse(isset($str[99]));

        // offsetUnset
        unset($str[0]);
        $this->assertSame(' string to change', (string) $str);
    }

    /**
     * @expectedException Sutra\Component\String\Exception\ProgrammerException
     * @expectedExceptionMessage Offsets can only be integer. Given: "random key"
     */
    public function testBadArrayAccessSet()
    {
        $str = new String('a string');
        $str['random key'] = 'a';
    }

    /**
     * @expectedException Sutra\Component\String\Exception\ProgrammerException
     * @expectedExceptionMessage The value length may not be greater than 1
     */
    public function testBadArrayAccessSetMoreThanOneChar()
    {
        $str = new String('a string');
        $str[0] = 'ab';
    }

    /**
     * @expectedException Sutra\Component\String\Exception\ProgrammerException
     * @expectedExceptionMessage Offsets can only be integer. Given: "random key"
     */
    public function testBadArrayAccessUnset()
    {
        $str = new String('a string');
        unset($str['random key']);
    }

    /**
     * @expectedException Sutra\Component\String\Exception\ProgrammerException
     * @expectedExceptionMessage Offsets can only be integer. Given: "random key"
     */
    public function testBadArrayAccessGet()
    {
        $str = new String('a string');
        $str['random key'];
    }

    /**
     * @expectedException Sutra\Component\String\Exception\ProgrammerException
     * @expectedExceptionMessage Offsets can only be integer. Given: "random key"
     */
    public function testBadArrayAccessExists()
    {
        $str = new String('a string');
        isset($str['random key']);
    }

    public function testGetIterator()
    {
        $str = new String('a string');
        $map = $str->toArray();

        foreach ($str as $i => $c) {
            $this->assertSame($map[$i], $c);
        }
    }

    public function testCharAt()
    {
        $str = new String('a string');
        $this->assertNull($str->charAt(-1));
        $this->assertNull($str->charAt(99));
        $this->assertEquals('t', $str->charAt(3));
        $this->assertNotSame($str, $str->charAt(3));
    }

    public function testCharCodeAt()
    {
        $str = new String('a string');
        $this->assertNull($str->charCodeAt(-1));
        $this->assertNull($str->charCodeAt(99));
        $this->assertSame(0x20, $str->charCodeAt(1));
    }

    public function testQuote()
    {
        $str = new String('a string');
        $this->assertNotSame($str, $str->quote());
        $this->assertEquals('"a string"' , $str->quote());
    }

    public function testSplit()
    {
        $str = new String('string');
        $this->assertCount(6, $str->split());
        $this->assertInternalType('array', $str->split());

        $str = new String('a b c');
        $this->assertCount(3, $str->split(' '));
    }

    public function testReplaceRegex()
    {
    }

    public function testToInteger()
    {
        $str = new String('a');
        $this->assertSame(0, $str->toInteger());

        $str = new String('0');
        $this->assertSame(0, $str->toInteger());

        $str = new String('1');
        $this->assertSame(1, $str->toInteger());

        $str = new String('2.0');
        $this->assertSame(2, $str->toInteger());

        $str = new String('34aaa');
        $this->assertSame(34, $str->toInteger());
    }

    public function testToFloat()
    {
        $str = new String('a');
        $this->assertSame(0.0, $str->toFloat());

        $str = new String('0');
        $this->assertSame(0.0, $str->toFloat());

        $str = new String('1');
        $this->assertSame(1.0, $str->toFloat());

        $str = new String('2.0');
        $this->assertNotSame(2, $str->toFloat());

        $str = new String('34aaa');
        $this->assertSame(34.0, $str->toFloat());
    }

    public function testToBase64()
    {
        $str = new String('my string');
        $this->assertNotSame($str, $str->toBase64());
    }

    public function testToBoolean()
    {
        $str = new String('a');
        $this->assertSame(false, $str->toBoolean());

        $str = new String('true');
        $this->assertSame(true, $str->toBoolean());

        $str = new String('1');
        $this->assertSame(true, $str->toBoolean());

        $str = new String('false');
        $this->assertSame(false, $str->toBoolean());

        $str = new String('0');
        $this->assertSame(false, $str->toBoolean());
    }

    public function testToJson()
    {
        $str = new String('my string');
        $this->assertNotSame($str, $str->toJson());
    }

    public function testToUriComponent()
    {
        $str = new String('my string');
        $this->assertNotSame($str, $str->toUriComponent());
    }

    public function testToRawUriComponent()
    {
        $str = new String('my string');
        $this->assertNotSame($str, $str->toRawUriComponent());
    }

    public function testToUpperCase()
    {
        $str = new String('my string');
        $this->assertNotSame($str, $str->toUpperCase());
        $this->assertNotEquals($str, $str->toUpperCase());
    }

    public function testToLowerCase()
    {
        $str = new String('MY STRING');
        $this->assertNotSame($str, $str->toLowerCase());
        $this->assertNotEquals($str, $str->toLowerCase());
    }

    public function testWordsToUpper()
    {
        $str = new String('my string of');
        $this->assertNotSame($str, $str->wordsToUpper());
        $this->assertNotEquals($str, $str->wordsToUpper());
    }

    public function testFirstCharToUpper()
    {
        $str = new String('my string of');
        $this->assertNotSame($str, $str->firstCharToUpper());
        $this->assertNotEquals($str, $str->firstCharToUpper());
    }

    public function testSubstr()
    {
    }

    public function testTrim()
    {
        $str = new String('   my string of    ');
        $this->assertNotSame($str, $str->trim());
        $this->assertNotEquals($str, $str->trim());

        $str = new String('   my string of    trz');
        $this->assertNotSame($str, $str->trim('trz'));
        $this->assertNotEquals($str, $str->trim('trz'));

        $str = new String('   my string of    ');
        $this->assertNotSame($str, $str->trimRight());
        $this->assertNotEquals($str, $str->trimRight());

        $str = new String('   my string of    trz');
        $this->assertNotSame($str, $str->trimRight('trz'));
        $this->assertNotEquals($str, $str->trimRight('trz'));

        $str = new String('   my string of    ');
        $this->assertNotSame($str, $str->trimLeft());
        $this->assertNotEquals($str, $str->trimLeft());

        $str = new String('trz   my string of    trz');
        $this->assertNotSame($str, $str->trimLeft('trz'));
        $this->assertNotEquals($str, $str->trimLeft('trz'));
    }

    public function testIndexOf()
    {
    }

    public function testLastIndexOf()
    {
    }

    public function testReverse()
    {
    }

    public function testWordWrap()
    {
    }

    public function testPad()
    {
    }

    public function testClean()
    {
    }

    public function testLength()
    {
        $str = new String('my string');
        $this->assertSame(9, $str->length);
        $this->assertSame(9, $str->getLength());
        $this->assertSame(9, count($str));
    }

    public function testAnyOtherAccessEqualsNull()
    {
        $str = new String('a string');
        $this->assertNull($str->randomProp);
    }
}
