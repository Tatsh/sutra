<?php
require './autoload.inc';

class sTimestampTest extends PHPUnit_Framework_TestCase {
  /**
   * @expectedException fProgrammerException
   */
  public function testTimezoneStringWithOffset() {
    sTimestamp::timezoneStringWithOffset(14);
  }

  public function testFormatTimezoneWithNumber() {
    $this->assertEquals('-12:00', sTimestamp::formatTimezoneWithNumber(-12));
    $this->assertEquals('+08:00', sTimestamp::formatTimezoneWithNumber(8));
  }

  public function testGetTimezones() {
    $this->assertInternalType('array', sTimestamp::getTimezones());
  }

  /**
   * @expectedException fValidationException
   */
  public function testRfc3339ToUNIXNotParseable() {
    sTimestamp::rfc3339ToUNIX('a', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage Invalid hour value.
   */
  public function testRfc3339ToUNIXBadHour() {
    $invalid2 = '2002-10-02T25:00:00Z';
    sTimestamp::rfc3339ToUNIX($invalid2, TRUE);
  }
}
