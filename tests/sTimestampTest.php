<?php
require './00-global.php';

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
    sTimestamp::RFC3339ToTimestamp('a', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadSecond() {
    // The regex has range [0-5][0-9] for the second and minute sections
    sTimestamp::RFC3339ToTimestamp('2002-10-02T22:00:60Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadMinute() {
    sTimestamp::RFC3339ToTimestamp('2002-10-02T22:61:59Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The date/time specified, 2002-10-2 25:0:0, does not appear to be a valid date/time
   */
  public function testRfc3339ToUNIXBadHour() {
    sTimestamp::RFC3339ToTimestamp('2002-10-02T25:00:00Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The date/time specified, 2002-10-32 23:0:0, does not appear to be a valid date/time
   */
  public function testRfc3339ToUNIXBadDayNumber() {
    sTimestamp::RFC3339ToTimestamp('2002-10-32T23:00:00Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The date/time specified, 2002-13-31 23:0:0, does not appear to be a valid date/time
   */
  public function testRfc3339ToUNIXBadMonthNumber() {
    sTimestamp::RFC3339ToTimestamp('2002-13-31T23:00:00Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadYearNumberIncorrectLength() {
    // Year range is 1000-2999
    sTimestamp::RFC3339ToTimestamp('200-13-31T23:00:00Z', TRUE);
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The value specified could not be validated as a RFC3339 timestamp.
   */
  public function testRfc3339ToUNIXBadYearNumberTooLowCorrectLength() {
    // Year range is 1000-2999
    sTimestamp::RFC3339ToTimestamp('0999-13-31T23:00:00Z', TRUE);
  }

  public function testRfc3339ToUNIXNoExceptionBadTimestamp() {
    $this->assertEquals(FALSE, sTimestamp::RFC3339ToTimestamp('2002-10-02T25:00:59+13:00'));
    $this->assertEquals(FALSE, sTimestamp::RFC3339ToTimestamp('2002-10-02T25:00:59Z'));
    $this->assertEquals(FALSE, sTimestamp::RFC3339ToTimestamp('2002-10-02T25:00:59.573Z'));
  }

  public function testRfc3339ToUNIX() {
    $result = sTimestamp::RFC3339ToTimestamp('2002-10-02T22:00:59+10:00');
    $this->assertInternalType('integer', $result);

    $result = sTimestamp::RFC3339ToTimestamp('2002-10-02T22:00:59.573+10:00');
    $this->assertInternalType('integer', $result);

    $result = sTimestamp::RFC3339ToTimestamp('2002-10-02T22:00:59Z');
    $this->assertInternalType('integer', $result);

    $result = sTimestamp::RFC3339ToTimestamp('2002-10-02T22:00:59.573Z');
    $this->assertInternalType('integer', $result);
  }
}
