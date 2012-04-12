<?php
require './includes/global.inc';

class tCore extends sCore {}

class t2Core extends sCore {
  public static function getDatabase() {}
}

class t3Core extends sCore {
  public static function getDatabase() {}
  public static function getCache() {}
}

class sCoreTest extends PHPUnit_Framework_TestCase {
  /**
   * @expectedException fProgrammerException
   */
  public function testMainNoGetDatabase() {
    tCore::main();
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testGetCacheNoImplementation() {
    tCore::getCache();
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testMainNoGetCache() {
    t2Core::main();
  }

  /**
   * @covers sCore::configureSession
   * @covers sCore::configureAuthorization
   * @covers sCore::main
   */
  public function testMain() {
    t3Core::main();
  }
}
