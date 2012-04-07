<?php
require './00-global.php';

class sNumberTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    sNumber::setLocale('en_US');
    sNumber::setFallbackLocale('en_US');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testAddCallbackException() {
    sNumber::addCallback('fr_FR', 'bad', 'frOrdinal');
  }

  public static function frenchOrdinalSuffix($n) {
    return 'e';
  }

  public static function frenchOrdinal($n) {
    return $n.'e';
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testAddCallbackLocaleException() {
    sNumber::addCallback('jeoiajgeiajgoiejg', 'ordinal', __CLASS__.'::frenchOrdinal');
  }

  public function testAddCallback() {
    sNumber::addCallback('fr-FR', 'ordinal', __CLASS__.'::frenchOrdinal');
    sNumber::addCallback('fr_FR', 'ordinalSuffix', __CLASS__.'::frenchOrdinalSuffix');
    sNumber::setLocale('fr-FR');
    $this->assertEquals('1e', sNumber::ordinal(1));
    $this->assertEquals('e', sNumber::ordinalSuffix(1));
  }

  public function testSetLocaleNoCallbacks() {
    sNumber::setLocale('fi-FI');
    $this->assertEquals('2nd', sNumber::ordinal(2));
    $this->assertEquals('nd', sNumber::ordinalSuffix(2));
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testSetLocaleException() {
    sNumber::setLocale('jeoiajgeiajgoiejg');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testSetFallbackLocaleException() {
    sNumber::setFallbackLocale('jeoiajgeiajgoiejg');
  }

  public function testSetFallbackLocaleNonExistantLocale() {
    sNumber::addCallback('fr-FR', 'ordinal', __CLASS__.'::frenchOrdinal');
    sNumber::setFallbackLocale('fr_CA');
    $this->assertEquals('nd', sNumber::ordinalSuffix(2));
  }

  public function testDefaultLocale() {
    $this->assertEquals('1st', sNumber::ordinal(1));
    $this->assertEquals('st', sNumber::ordinalSuffix(1));
    $this->assertEquals('3rd', sNumber::ordinal(3));
    $this->assertEquals('rd', sNumber::ordinalSuffix(3));
    $this->assertEquals('16th', sNumber::ordinal(16));
    $this->assertEquals('th', sNumber::ordinalSuffix(16));
  }

  public function testRemoveLocale() {
    sNumber::setLocale('fr_FR');
    sNumber::removeLocale('fr_FR');
  }

  public function testIsEqualToIntCast() {
    $this->assertFalse(sNumber::isEqualToIntCast(new stdClass));
    $this->assertFalse(sNumber::isEqualToIntCast('aaa'));
    $this->assertTrue(sNumber::isEqualToIntCast('1'));
  }

  public function testGetOrdinalSuffix() {
    $number = new sNumber(2);
    $this->assertEquals('nd', $number->getOrdinalSuffix());
  }

  public function testFormatWithOrdinalSuffix() {
    $number = new sNumber(2);
    $this->assertEquals('2nd', $number->formatWithOrdinalSuffix());
  }
}
