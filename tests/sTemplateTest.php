<?php
require './includes/global.inc';

class sTemplateTest extends PHPUnit_Framework_TestCase {
  /**
   * @var sCache
   */
  private static $cache = NULL;

  public static function setUpBeforeClass() {
    self::$cache = new sCache('apc');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testGetCacheNoCache() {
    sTemplate::getCache();
  }

  /**
   * @depends testGetCacheNoCache
   */
  public function testSetCache() {
    sTemplate::setCache(self::$cache);
    $this->assertEquals(self::$cache, sTemplate::getCache());
  }

  /**
   * @expectedException fValidationException
   */
  public function testBadMinifiedCSSPath() {
    sTemplate::setMinifiedCSSPath('non-existant');
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The directory specified, "non-writable-directory", does exist but is not writable.
   */
  public function testSetMinifedCSSPathNonWritable() {
    $dir_name = 'non-writable-directory';
    @mkdir($dir_name, 0500);
    sTemplate::setMinifiedCSSPath($dir_name);
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testAddJavaScriptFileBadWhere() {
    sTemplate::addJavaScriptFile('aaa.js', 'a');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testAddJavaScriptFileBadWhereMinified() {
    sTemplate::addMinifiedJavaScriptFile('aaa.js', 'a');
  }

  public function testAddJavaScriptFile() {
    sTemplate::setMode('development');
    $this->assertEquals('development', sTemplate::getMode());

    sTemplate::addJavaScriptFile('a.js');
    $this->assertEquals(array('head' => array(), 'body' => array('a.js')), sTemplate::getJavaScriptFiles());

    sTemplate::addMinifiedJavaScriptFile('a.min.js');
    $this->assertEquals(array('head' => array(), 'body' => array('a.js')), sTemplate::getJavaScriptFiles());

    sTemplate::setMode('production');
    $this->assertEquals(array('a.min.js'), sTemplate::getJavaScriptFiles('body'));

    sTemplate::setMode('development');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testSetModeInvalid() {
    sTemplate::setMode('a');
  }

  public function testSetMode() {
    sTemplate::setMode('development');
    $this->assertEquals('development', sTemplate::getMode());

    sTemplate::setMode('production');
    $this->assertEquals('production', sTemplate::getMode());
  }

  /**
   * @expectedException fValidationException
   */
  public function testBadTemplatePath() {
    sTemplate::setTemplatesPath('non-existant');
  }

  /**
   * @expectedException fValidationException
   */
  public function testBadProductionTemplatePath() {
    sTemplate::setProductionModeTemplatesPath('non-existant');
  }

  public function testTemplateExists() {
    $this->assertFalse(sTemplate::templateExists('non-existant'));
  }

  /**
   * @expectedException fValidationException
   */
  public function testBadTemplatePath2() {
    sTemplate::setTemplatesPath('default', 'non-existant-also');
  }

  public function setUp() {
    sTemplate::setTemplatesPath('./template');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testSetActiveTemplateBadTemplate() {
    sTemplate::setActiveTemplate('non-existant');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testBufferBadFilename() {
    sTemplate::buffer('non-existant');
  }

  public function testAddCDN() {
    sTemplate::addCDN('http://myhost');
    $this->assertEquals(array('http://myhost'), sTemplate::getCDNs());
  }

  /**
   * @depends testAddCDN
   */
  public function testRemoveCDNs() {
    sTemplate::removeCDNs();
    $this->assertEquals(array(), sTemplate::getCDNs());
  }

  public function testRemoveCDN() {
    sTemplate::addCDN('http://myhost');
    sTemplate::addCDN('http://myhost2');
    sTemplate::removeCDN('http://myhost');
    $this->assertEquals(array(1 => 'http://myhost2'), sTemplate::getCDNs());
    sTemplate::removeCDNs();
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The content string is missing in the variables array.
   */
  public function testRenderMissingContent() {
    sTemplate::render(array('title' => 'b'));
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The title string is missing in the variables array.
   */
  public function testRenderMissingTitle() {
    sTemplate::render(array('content' => 'a'));
  }

  public function testAddBodyClassJustArray() {
    sTemplate::addBodyClass('special');
    $this->assertEquals(array('special'), sTemplate::getBodyClasses());
  }

  /**
   * @expectedException fUnexpectedException
   * @expectedExceptionMessage Could not find a valid page template for this URL.
   */
  public function testRenderNoPageTemplate() {
    sTemplate::render(array('title' => 'a', 'content' => 'a'));
  }
}
