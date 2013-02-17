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

  /**
   * @covers sTemplate::removeCDN
   */
  public function testRemoveCDN() {
    sTemplate::addCDN('http://myhost');
    sTemplate::addCDN('http://myhost2');
    sTemplate::removeCDN('http://myhost');
    $this->assertEquals(array(1 => 'http://myhost2'), sTemplate::getCDNs());
    sTemplate::removeCDNs();
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The content string is missing in the variables array
   */
  public function testRenderMissingContent() {
    sTemplate::render(array('title' => 'b'));
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The title string is missing in the variables array
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

  public function testSetSiteSlogan() {
    sTemplate::setMode('development');
    sTemplate::setSiteSlogan('Site slogan');
    sTemplate::setActiveTemplate('custom');
    sTemplate::render(array('title' => 'My title', 'content' => 'a'));
    $this->expectOutputRegex('/>\\nSite\sslogan\\n<\/div>/');

    sTemplate::setMode('production');
    sTemplate::render(array('title' => 'My title', 'content' => 'a'));
    $this->expectOutputRegex('/>Site\sslogan<\/div>/');
  }

  public function testSetSiteName() {
    sTemplate::setMode('development');
    sTemplate::setSiteName('my site');
    sTemplate::setActiveTemplate('custom');
    sTemplate::render(array('title' => 'My title', 'content' => 'a'));
    $this->expectOutputRegex('/<title>My\stitle\s\|\smy\ssite<\/title>/');
  }

  public function testEnableQueryStrings() {
    sTemplate::setMode('development');
    sTemplate::enableQueryStrings(TRUE); // default
    sTemplate::setActiveTemplate('custom');
    sTemplate::render(array('title' => 'Test query string enabled', 'content' => ''));
    $this->expectOutputRegex('/<script\ssrc="\/a\.js\?_=\d+/');
  }

  public function testEnableQueryStringsProductionModeOn() {
    sTemplate::setMode('production');
    sTemplate::enableQueryStrings(TRUE); // default
    sTemplate::render(array('title' => 'Test query string enabled, production mode on', 'content' => ''));
    $this->expectOutputRegex('/<script\ssrc="\/a\.min\.js"/');
  }

  public function testEnableQueryStringsProductionModeOff() {
    sTemplate::setMode('development');
    sTemplate::enableQueryStrings(FALSE); // default
    sTemplate::render(array('title' => 'Test query string disabled, production mode off', 'content' => ''));
    $this->expectOutputRegex('/<script\ssrc="\/a\.js"/');
  }

  public function testAddJavaScriptFileURL() {
    sTemplate::setMode('development');
    sTemplate::addJavaScriptFile('https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', 'head');
    sTemplate::render(array('title' => 'Test query string disabled, production mode off', 'content' => ''));
    $this->expectOutputRegex('/<head>\n\s+[^>]+.*<\/title>\n.*<script\ssrc="https/');
  }

  public function testAddCSSFile() {
    sTemplate::setMode('development');
    sTemplate::addCSSFile('test.css');
    sTemplate::addCSSFile('test2.css', 'screen');
    sTemplate::addCSSFile('test3.css', 'only screen and (min-device-width:1024px)', TRUE);
    sTemplate::render(array('title' => 'Test CSS, production mode off', 'content' => ''));
    $this->expectOutputRegex('/<link\shref="\/test\.css"\smedia="all"/');
    $this->expectOutputRegex('/<link\shref="\/test2\.css"\smedia="screen"/');
    $this->expectOutputRegex('/<link\shref="\/test3\.css"\smedia="only\sscreen\sand\s\(min/');
  }

  /**
   * @depends testAddCSSFile
   */
  public function testSetMediaOrder() {
    sTemplate::setMode('development');
    sTemplate::setCSSMediaOrder(array('screen', 'all'));
    sTemplate::render(array('title' => 'Test CSS', 'content' => ''));
    $this->expectOutputRegex('/<link\shref="\/test2\.css[^>]+>[^<]+<link href="\/test\.css"/');
  }

  public static function theCallback1() {
    return array('abc' => 'fghijk');
  }

  public static function theCallback2() {
    return array('abc' => 'abcdef');
  }

  public static function badCallback() {
    return NULL;
  }

  public function testRegisterCallback() {
    sTemplate::registerCallback(__CLASS__.'::theCallback1', 'something');
    sTemplate::registerCallback(__CLASS__.'::theCallback2', 'something2');

    $content = sTemplate::buffer('something');
    $this->assertEquals('fghijk', $content);

    $content = sTemplate::buffer('something2');
    $this->assertEquals('abcdef', $content);
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Callback "sTemplateTest::badCallback" for template "something" did not return an array.
   */
  public function testRegisterBadCallback() {
    sTemplate::registerCallback(__CLASS__.'::badCallback', 'something');
    $content = sTemplate::buffer('something');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testSetActiveTemplateBadFallbackTemplate() {
    sTemplate::setActiveTemplate('./resources', 'non-existant');
  }

  public function testSetCDNs() {
    $cdns = array('http://myhost', 'http://myhost2');
    sTemplate::setCDNs($cdns);
    sTemplate::setMode('development');
    $this->assertEquals('', sTemplate::getACDN());

    sTemplate::setMode('production');
    $this->assertTrue(in_array(sTemplate::getACDN(), $cdns, TRUE));
    sTemplate::setMode('development');
  }

  /**
   * @depends testAddCSSFile
   */
  public function testRender() {
    sTemplate::setMode('production');
    sTemplate::setMinifiedCSSPath('mincss');
    sTemplate::setProductionModeTemplatesPath('template');
    sTemplate::setActiveTemplate('custom');
    sTemplate::render(array('title' => 'General render', 'content' => 'a'));
    $this->expectOutputRegex('/mincss\/css\-only\-screen\-and\-min\-device\-width\-1024px\-\d+/');
  }

  /**
   * @depends testRender
   * @expectedException fUnexpectedException
   * @expectedExceptionMessage Unable to read file "non-existant.css"
   */
  public function testRenderBadCSSFile() {
    sTemplate::getCache()->clear();

    sTemplate::addCSSFile('non-existant.css');
    sTemplate::render(array('title' => 'General render', 'content' => 'a'));
  }
}
