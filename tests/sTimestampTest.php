<?php
require './00-global.php';

class sTimestampTest extends PHPUnit_Framework_TestCase {
  /**
   * @expectedException fProgrammerException
   */
  public function testTimezoneStringWithOffset() {
    sTimestamp::timezoneStringWithOffset(14);
  }

  public function testTimezoneStringWithOffsetValid() {
    $this->assertEquals('Pacific/Auckland', sTimestamp::timezoneStringWithOffset(12));
  }

  public function testFormatTimezoneWithNumber() {
    $this->assertEquals('-12:00', sTimestamp::formatTimezoneWithNumber(-12));
    $this->assertEquals('+08:00', sTimestamp::formatTimezoneWithNumber(8));
    $this->assertEquals('-08:00', sTimestamp::formatTimezoneWithNumber(-8));
    $this->assertEquals('+12:00', sTimestamp::formatTimezoneWithNumber(12));
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
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadSecond() {
    // The regex has range [0-5][0-9] for the second and minute sections
    sTimestamp::rfc3339ToUNIX('2002-10-02T22:00:60Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadMinute() {
    sTimestamp::rfc3339ToUNIX('2002-10-02T22:61:59Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage Invalid hour value.
   */
  public function testRfc3339ToUNIXBadHour() {
    sTimestamp::rfc3339ToUNIX('2002-10-02T25:00:00Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage Invalid day value.
   */
  public function testRfc3339ToUNIXBadDayNumber() {
    sTimestamp::rfc3339ToUNIX('2002-10-32T23:00:00Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage Invalid month value.
   */
  public function testRfc3339ToUNIXBadMonthNumber() {
    sTimestamp::rfc3339ToUNIX('2002-13-31T23:00:00Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadYearNumberIncorrectLength() {
    // Year range is 1000-2999
    sTimestamp::rfc3339ToUNIX('200-13-31T23:00:00Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadYearNumberTooLowCorrectLength() {
    // Year range is 1000-2999
    sTimestamp::rfc3339ToUNIX('0999-13-31T23:00:00Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage Invalid timezone value.
   */
  public function testRfc3339ToUNIXBadTimezoneTooLow() {
    sTimestamp::rfc3339ToUNIX('2002-10-02T22:00:59-13:00', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage Invalid timezone value.
   */
  public function testRfc3339ToUNIXBadTimezoneTooHigh() {
    sTimestamp::rfc3339ToUNIX('2002-10-02T22:00:59+13:00', TRUE);
  }

  public function testRfc3339ToUNIXNoExceptionBadTimestamp() {
    $this->assertEquals(0, sTimestamp::rfc3339ToUNIX('2002-10-02T22:00:59+13:00'));
  }

  public function testRfc3339ToUNIX() {
    $result = sTimestamp::rfc3339ToUNIX('2002-10-02T22:00:59+10:00');
    $this->assertInternalType('int', $result);

    $result = sTimestamp::rfc3339ToUNIX('2002-10-02T22:00:59Z');
    $this->assertInternalType('int', $result);
  }
}
