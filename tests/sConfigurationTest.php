<?php
require './autoload.inc';
require './stubs.inc';

class sConfigurationTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    sDatabase::getInstance();
  }

  /**
   * @expectedException fValidationException
   */
  public function testSetPathBadPath() {
    sConfiguration::setPath('bad');
  }

  public function testSetPath() {
    sConfiguration::setPath('/etc/sutra');
  }

  /**
   * depends testSetPath
   */
  public function testGetPath() {
    $this->assertEquals('/etc/sutra', sConfiguration::getPath());

    // Set back to original
    sConfiguration::setPath('./config');
  }

  public function testAdd() {
    sConfiguration::add('mykey', 'true');
  }

  /**
   * @depends testAdd
   */
  public function testGet() {
    $this->assertEquals('true', sConfiguration::get('mykey'));
    $this->assertEquals(true, sConfiguration::get('mykey', 'false', 'bool'));
    $this->assertEquals(0, sConfiguration::get('mykey', 'false', 'integer'));
    $this->assertEquals(0, sConfiguration::get('mykey', 'false', 'float'));

    $this->assertEquals('false', sConfiguration::get('nonexist2', 'false'));
  }

  /**
   * @depends testAdd
   */
  public function testSet() {
    sConfiguration::set('mykey', 'false');
    $this->assertEquals('false', sConfiguration::get('mykey'));
  }

  public function testCallStatic() {
    if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
      $this->assertEquals('Test site', sConfiguration::getSiteName());
    }
  }

  /**
   * @depends testAdd
   */
  public function testCall() {
    $config = sConfiguration::getInstance();
    $this->assertInstanceOf('sConfiguration', $config);
    $this->assertEquals('Test site', $config->getSiteName());
    $this->assertEquals('false', $config->getMykey());
    $this->assertInternalType('int', $config->getMykey('int'));
    $this->assertInternalType('float', $config->getMykey('float'));
  }
}
