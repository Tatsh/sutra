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

  public function testRfc3339ToUNIX() {
    $timestamp = new sTimestamp('2002-10-02T22:00:59+10:00', NULL);
    $this->assertGreaterThan(0, $timestamp->format('U'));

    $timestamp = new sTimestamp('2002-10-02T22:00:59.573+10:00', NULL);
    $this->assertGreaterThan(0, $timestamp->format('U'));

    $timestamp = new sTimestamp('2002-10-02T22:00:59Z', NULL);
    $this->assertGreaterThan(0, $timestamp->format('U'));

    $timestamp = new sTimestamp('2002-10-02T22:00:59.573Z', NULL);
    $this->assertGreaterThan(0, $timestamp->format('U'));
  }
}
