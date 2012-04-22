<?php
require './includes/global.inc';

class sStringTest extends PHPUnit_Framework_TestCase {
  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage String argument must be non-zero-length string.
   */
  public function testConstructorEmptyString() {
    new sString('');
  }

  public function testReplace() {
    $s = new sString('abcdef');
    $replace = $s->replace('abc', 'def');
    $this->assertEquals('defdef', $replace->__toString());
    $this->assertEquals('fedfed', $replace->replace('DEF', 'fed', FALSE)->__toString());
  }

  public function testReplaceRegex() {
    $s = new sString('abcdef');
    $replace = $s->replaceRegex('/abc/', 'def');
    $this->assertEquals('defdef', $replace);
  }

  public function testToTime() {
    $s = new sString('2 weeks ago');
    $time = $s->toTime();
    $this->assertInstanceOf('fTime', $time);
  }

  public function testToTimestamp() {
    $s = new sString('2 weeks ago');
    $time = $s->toTimestamp();
    $this->assertInstanceOf('sTimestamp', $time);
  }

  public function testToDate() {
    $s = new sString('2 weeks ago');
    $time = $s->toDate();
    $this->assertInstanceOf('fDate', $time);
  }

  public function testToInteger() {
    $s = new sString('2 weeks ago');
    $int = $s->toInt();
    $this->assertInternalType('int', $int);
    $this->assertEquals(2, $int);
  }

  public function testToNumber() {
    $s = new sString('20000');
    $int = $s->toNumber();
    $this->assertInstanceOf('sNumber', $int);
  }

  public function testToFloat() {
    $s = new sString('3.14');
    $f = $s->toFloat();
    $this->assertInternalType('float', $f);
    $this->assertEquals(3.14, $f);
  }

  public function testToBase64() {
    $s = new sString('my string');
    $this->assertEquals(base64_encode('my string'), $s->toBase64());
  }

  public function testToBoolean() {
    $s = new sString('my string');
    $this->assertFalse($s->toBoolean());
    $s = new sString('tRuE');
    $this->assertTrue($s->toBoolean());
    $s = new sString('1');
    $this->assertTrue($s->toBoolean());
  }

  public function testToJSON() {
    $s = new sString('my string"');
    $this->assertEquals(fJSON::encode('my string"'), $s->toJSON());
  }

  public function testToURIComponent() {
    $s = new sString('     a string');
    $this->assertEquals('+++++a+string', $s->toURIComponent());
  }

  public function testToRawURIComponent() {
    $s = new sString('     a string');
    $this->assertEquals('%20%20%20%20%20a%20string', $s->toRawURIComponent());
  }

  public function testCharAt() {
    $str = new sString('abcdef');
    $this->assertEquals('a', $str->charAt(0));

    $this->assertNull($str->charAt(-100));
    $this->assertNull($str->charAt(7));
  }

  public function testCharCodeAt() {
    $str = new sString('0abcdef');
    $this->assertEquals(48, $str->charCodeAt(0));
    $this->assertNull($str->charCodeAt(8));
  }

  public function testQuote() {
    $str = new sString('abcdef');
    $this->assertEquals('"abcdef"', $str->quote());
  }

  public function testSplit() {
    $str = new sString('abcdef');
    $ret = $str->split();
    $this->assertInternalType('array', $ret);
    $this->assertEquals('a', $ret[0]);

    $str = new sString('abcdef');
    $ret = $str->split('d');
    $this->assertInternalType('array', $ret);
    $this->assertEquals(array('abc', 'ef'), $ret);
  }

  public function testToLowerCase() {
    $a = new sString('ABCDEFa');
    $this->assertEquals('abcdefa', $a->toLowerCase());
  }

  public function testToUpperCase() {
    $a = new sString('abcdefA');
    $this->assertEquals('ABCDEFA', $a->toUpperCase());
  }

  public function testWordsToUpper() {
    $a = new sString('a b c d e f');
    $this->assertEquals('A B C D E F', $a->wordsToUpper());
  }

  public function testFirstCharToUpper() {
    $a = new sString('a b c d e f');
    $this->assertEquals('A b c d e f', $a->firstCharToUpper());
  }

  public function testSubstr() {
    $a = new sString('abcdef');
    $this->assertEquals('bcdef', $a->substr(1));
    $this->assertEquals('bc', $a->substr(1, 2));
  }

  public function testTrimLeft() {
    $a = new sString('               abcdef');
    $this->assertEquals('abcdef', $a->trimLeft());
  }

  public function testTrimRight() {
    $a = new sString('               abcdef                ');
    $this->assertEquals('               abcdef', $a->trimRight());
  }

  public function testTrim() {
    $a = new sString('               abcdef                ');
    $this->assertEquals('abcdef', $a->trim());
  }

  public function testIndexOf() {
    $a = new sString('abcdef');
    $this->assertEquals(1, $a->indexOf('bcdef'));
    $this->assertEquals(-1, $a->indexOf('my aoijeoa'));
  }

  public function testLastIndexOf() {
    $a = new sString('abcdefabcdef');
    $this->assertEquals(7, $a->lastIndexOf('bcdef'));
    $this->assertEquals(-1, $a->lastIndexOf('ioaejgioeajgioaejgo'));
  }

  public function testWordWrap() {
    $a = new sString('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.');
    $this->assertEquals('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem
Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an
unknown printer took a galley of type and scrambled it to make a type specimen
book. It has survived not only five centuries, but also the leap into electronic
typesetting, remaining essentially unchanged. It was popularised in the 1960s
with the release of Letraset sheets containing Lorem Ipsum passages, and more
recently with desktop publishing software like Aldus PageMaker including
versions of Lorem Ipsum.', $a->wordWrap(80, "\n", TRUE));
  }

  public function testReverse() {
    $normal = 'ABCDEFG';
    $rev = 'GFEDCBA';
    $str = new sString($normal);
    $this->assertEquals($rev, $str->reverse());
  }

  public function testPad() {
    $str = new sString('abcdef');
    $this->assertEquals('0000abcdef', $str->pad(10, '0', 'left'));
  }

  public function testClean() {
    $a = new sString('abcdef');
    $this->assertEquals('abcdef', $a->clean()->__toString());
  }

  public function testGetLength() {
    $str = new sString('abcdef');
    $this->assertEquals(6, $str->length);
    $this->assertEquals(6, $str->getLength());
    $this->assertEquals(6, count($str));

    $this->assertNull($str->unknown);
  }

  public function testArrayAccess() {
    $normal = 'ABCDEFG';
    $str = new sString($normal);
    foreach ($str as $key => $value) {
      $this->assertEquals($value, $str[$key]);
    }
    $this->assertEquals('A', $str[0]);
    $this->assertEquals('B', $str[1]);
    $this->assertEquals('C', $str[2]);
    $this->assertEquals('D', $str[3]);

    $this->assertTrue(isset($str[0]));

    $str[1] = 'D';
    $this->assertEquals('ADCDEFG', $str);

    unset($str[1]);
    $this->assertEquals('ACDEFG', $str);
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Offsets can only be integer. Given: "a"
   */
  public function testArrayAccessOffsetExistsException() {
    $str = new sString('abcdef');
    isset($str['a']);
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Offsets can only be integer. Given: "a"
   */
  public function testArrayAccessOffsetGetException() {
    $str = new sString('abcdef');
    $b = $str['a'];
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Offsets can only be integer. Given: "a"
   */
  public function testArrayAccessOffsetSetBadIndex() {
    $str = new sString('abcdef');
    $str['a'] = 'a';
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Offsets can only be integer. Given: "a"
   */
  public function testArrayAccessOffsetUnsetException() {
    $str = new sString('abcdef');
    unset($str['a']);
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The value length may not be greater than 1
   */
  public function testArrayAccessOffsetSetBadValue() {
    $str = new sString('abcdef');
    $str[0] = 'ab';
  }
}
