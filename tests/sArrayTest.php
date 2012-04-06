<?php
require './00-global.php';

class sArrayTest extends PHPUnit_Framework_TestCase {
  private static $must_have = array('a', 'b', 'c');

  public function testHasRequiredKeys() {
    $ret = sArray::hasRequiredKeys(array(), self::$must_have);
    $this->assertFalse($ret, "Asserting that $ret is FALSE from empty array");

    $ret = sArray::hasRequiredKeys(array('d'), self::$must_have);
    $this->assertEquals('a', $ret, 'array("d") should return "a"');

    $ret = sArray::hasRequiredKeys(array('a' => 1, 'b' => 1, 'd' => 1), self::$must_have);
    $this->assertEquals('c', $ret, 'Return missing key "c"');

    $ret = sArray::hasRequiredKeys(array('a' => 1, 'b' => 1, 'c' => 1), self::$must_have);
    $this->assertTrue($ret, "Asserting TRUE when comparing the same array");

    $ret = sArray::hasRequiredKeys(array('a' => 1, 'b' => 1, 'c' => 1, 'd' => 1), self::$must_have);
    $this->assertTrue($ret, 'Should return TRUE when the array has all required keys and more but third parameter is unspecified');
  }

  public function testHasOnlyRequiredKeys() {
    $ret = sArray::hasRequiredKeys(array(), self::$must_have, TRUE);
    $this->assertFalse($ret, "Asserting that $ret is FALSE from empty array (third parameter = TRUE)");

    $ret = sArray::hasRequiredKeys(array('d'), self::$must_have, TRUE);
    $this->assertEquals('a', $ret, 'array("d") should return "a" (third parameter = TRUE)');

    $ret = sArray::hasRequiredKeys(array('a' => 1, 'b' => 1, 'd' => 1), self::$must_have);
    $this->assertEquals('c', $ret, 'Return missing key "c" (third parameter = TRUE)');

    $ret = sArray::hasRequiredKeys(array('a' => 1, 'b' => 1, 'c' => 1), self::$must_have, TRUE);
    $this->assertTrue($ret, "Asserting that $ret is TRUE when comparing the same array (third parameter = TRUE)");

    $ret = sArray::hasRequiredKeys(array('a' => 1, 'b' => 1, 'c' => 1, 'd' => 1), self::$must_have, TRUE);
    $this->assertFalse($ret, 'Should return FALSE when the array has all required keys and more but third parameter is set to TRUE');
  }
}
