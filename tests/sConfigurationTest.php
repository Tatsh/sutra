<?php
require './00-global.php';

class sConfigurationTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    sCache::getInstance()->clear();
    sDatabase::getInstance();
  }

  /**
   * @expectedException fEnvironmentException
   */
  public function testSetPathWithNoFiles() {
    sConfiguration::setPath('./config2');
    $cwd = getcwd();
    sCache::getInstance()->delete('sConfiguration::'.$cwd.'::site_settings_last_cached');
    sConfiguration::getInstance();
  }

  /**
   * @depends testSetPathWithNoFiles
   */
  public function testSetPath() {
    // Set back to original
    sConfiguration::setPath('./config');
    $cwd = getcwd();
    sCache::getInstance()->delete('sConfiguration::'.$cwd.'::site_settings_last_cached');
    sConfiguration::getInstance();
  }

  public function testNoRecache() {
    $cwd = getcwd();
    sCache::getInstance()->set('sConfiguration::'.$cwd.'::site_settings_last_cached', 1331035455);
    sConfiguration::getInstance();
  }

  /**
   * @expectedException fValidationException
   */
  public function testSetPathBadPath() {
    sConfiguration::setPath('bad');
  }

  /**
   * depends testSetPath
   */
  public function testGetPath() {
    $this->assertStringEndsWith('config', sConfiguration::getPath());
  }

  /**
   * depends testSetPath
   */
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

  /**
   * depends testSetPath
   */
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
    $this->assertNull($config->badCall());
  }
}
