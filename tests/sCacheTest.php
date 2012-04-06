<?php
require './00-global.php';

class sCacheTest extends PHPUnit_Framework_TestCase {
  /**
   * @var string
   */
  private static $prefix = '';
  /**
   * @var fCache
   */
  private static $fcache = NULL;
  /**
   * @var sCache
   */
  private static $scache = NULL;
  /**
   * @var boolean
   */
  private static $skipping = FALSE;

  public static function setUpBeforeClass() {
    try {
      self::$prefix = 'sCache::'.getcwd().'::';

      self::$fcache = new fCache('apc');
      self::$scache = new sCache('apc');
      self::$fcache->set('a', 'b');

      if (self::$fcache->get('a', FALSE) === FALSE) {
        throw new fProgrammerException;
      }
    }
    catch (fProgrammerException $e) {
      self::$skipping = TRUE;
      return;
    }
    catch (fEnvironmentException $e) {
      self::$skipping = TRUE;
      return;
    }
  }

  public static function tearDownAfterClass() {
    self::$fcache->clear();
    self::$fcache->__destruct();
  }

  public function setUp() {
    if (self::$skipping) {
      $this->markTestSkipped('The APC extension is unvailable or not functioning.');
    }

    self::$scache->set('key1', 'value');
  }

  public function testAdd() {
    $ret = self::$scache->add('key1', 'value');
    $this->assertFalse($ret);

    $ret = self::$fcache->add(self::$prefix.'key1', 'value');
    $this->assertFalse($ret);
  }

  public function testGet() {
    $ret = self::$scache->get('key1');
    $ret2 = self::$fcache->get(self::$prefix.'key1');

    $this->assertNotNull($ret);
    $this->assertEquals('value', $ret);
    $this->assertEquals('value', $ret2);
  }

  public function testSet() {
    $ret = self::$scache->set('key1', 'value2');
    $this->assertTrue($ret);

    self::$fcache->set(self::$prefix.'key1', 'value3');
    $ret = self::$scache->get('key1');
    $this->assertEquals('value3', $ret);
  }

  public function testDelete() {
    self::$scache->set('key1', 'value');
    self::$scache->set('key2', 'value');

    $ret = self::$fcache->delete(self::$prefix.'key1');
    $this->assertTrue($ret);

    $ret = self::$fcache->delete(self::$prefix.'key2');
    $this->assertTrue($ret);

    $ret = self::$scache->delete('key1');
    $this->assertFalse($ret);

    $ret = self::$scache->delete('key2');
    $this->assertFalse($ret);
  }
}
