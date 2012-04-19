<?php
require './includes/global.inc';

class sTimestampTest extends PHPUnit_Framework_TestCase {
  public function testFormatTimezoneWithNumber() {
    $this->assertEquals('-12:00', sTimestamp::formatTimezoneNumber(-12));
    $this->assertEquals('+08:00', sTimestamp::formatTimezoneNumber(8));
    $this->assertEquals('-08:00', sTimestamp::formatTimezoneNumber(-8));
    $this->assertEquals('+12:00', sTimestamp::formatTimezoneNumber(12));
    $this->assertEquals('+12.5:00', sTimestamp::formatTimezoneNumber(12.5));
  }

  /**
   * @expectedException fValidationException
   */
  public function testRfc3339ToUNIXNotParseable() {
    new sTimestamp('a', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadSecond() {
    // The regex has range [0-5][0-9] for the second and minute sections
    new sTimestamp('2002-10-02T22:00:60Z', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadMinute() {
    new sTimestamp('2002-10-02T22:61:59Z', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The date/time specified, 2002-10-2 25:0:0, does not appear to be a valid date/time
   */
  public function testRfc3339ToUNIXBadHour() {
    new sTimestamp('2002-10-02T25:00:00Z', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The date/time specified, 2002-10-32 23:0:0, does not appear to be a valid date/time
   */
  public function testRfc3339ToUNIXBadDayNumber() {
    new sTimestamp('2002-10-32T23:00:00Z', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The date/time specified, 2002-13-31 23:0:0, does not appear to be a valid date/time
   */
  public function testRfc3339ToUNIXBadMonthNumber() {
    new sTimestamp('2002-13-31T23:00:00Z', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadYearNumberIncorrectLength() {
    // Year range is 1000-2999 in the regular expression
    new sTimestamp('200-13-31T23:00:00Z', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadYearNumberTooLowCorrectLength() {
    new sTimestamp('0999-13-31T23:00:00Z', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
  }

  public function testRfc3339ToUNIX() {
    $timestamp = new sTimestamp('2002-10-02T22:00:59+10:00', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
    $this->assertGreaterThan(0, $timestamp->format('U'));

    $timestamp = new sTimestamp('2002-10-02T22:00:59.573+10:00', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
    $this->assertGreaterThan(0, $timestamp->format('U'));

    $timestamp = new sTimestamp('2002-10-02T22:00:59Z', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
    $this->assertGreaterThan(0, $timestamp->format('U'));

    $timestamp = new sTimestamp('2002-10-02T22:00:59.573Z', NULL, sTimestamp::DATETIME_TYPE_RFC3339);
    $this->assertGreaterThan(0, $timestamp->format('U'));
  }
}
