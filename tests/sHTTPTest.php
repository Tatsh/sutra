<?php
require './includes/global.inc';

class sHTTPTest extends PHPUnit_Framework_TestCase {
  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The argument specified, "aaa", is not a valid HTTP URL.
   */
  public function testConstructorBadURL() {
    new sHTTP('aaa');
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The method specified, "bad", is not a valid HTTP method.
   */
  public function testConstructorBadMethod() {
    new sHTTP('http://localhost', 'bad');
  }

  public function testGetHeadersAsString() {
    $headers = array(
      'User-Agent' => 'my user agent',
      'custom-header' => 'custom header',
    );
    $a = new sHTTP('http://localhost');
    $a->setHeaders($headers);
    $str = $a->getHeaders(TRUE);
    $this->assertEquals("User-Agent: my user agent\r\ncustom-header: custom header\r\n", $str);
  }

  public function testSetHeaders() {
    $headers = array(
      'User-Agent' => 'my user agent',
      'custom-header' => 'custom header',
    );
    $a = new sHTTP('http://localhost');
    $a->setHeaders($headers);
    $this->assertInternalType('array', $a->getHeaders());
  }

  public function testSetUserAgent() {
    $agent = 'aaaaaa';
    $a = new sHTTP('http://localhost');
    $a->setUserAgent($agent);
    $headers = $a->getHeaders();
    $this->assertArrayHasKey('User-Agent', $headers);
    $this->assertEquals($agent, $headers['User-Agent']);
  }

  public function testGetData() {
    $a = new sHTTP('http://am.php.net/manual/en/context.http.php');
    $data = $a->getData();
    $this->assertTag(array(
      'tag' => 'title',
      'content' => 'PHP: HTTP context options - Manual',
    ), $data);
  }

  /**
   * @expectedException fUnexpectedException
   * @expectedExceptionMessage The URI, "http://hope-it-doesnt-exist", could not be loaded.
   */
  public function testBadRequest() {
    $url = 'http://hope-it-doesnt-exist';
    $a = new sHTTP($url);
    $a->getData();
  }

  public function testPOST() {
    $a = new sHTTP('http://am.php.net/manual/en/context.http.php', 'POST');

    $this->assertEquals('', $a->getContent());

    $a->setContent('my content');
    $this->assertEquals('my content', $a->getContent());

    $data = $a->getData();
    $this->assertTag(array(
      'tag' => 'title',
      'content' => 'PHP: HTTP context options - Manual',
    ), $data);
  }

  public function testSetProxy() {
    $url = 'http://hope-it-doesnt-exist';
    $a = new sHTTP($url);
    $a->setProxy('tcp://proxy.example.com:5100');
    $this->assertEquals('tcp://proxy.example.com:5100', $a->getProxy());
    $a->removeProxy();
    $this->assertEquals('', $a->getProxy());
  }

  /**
   * @expectedException fUnexpectedException
   * @expectedExceptionMessage The URI, "http://www.google.com", could not be loaded.
   * @todo Need test with working proxy.
   */
  public function testWithProxy() {
    $url = 'http://www.google.com';
    $a = new sHTTP($url);
    $a->setProxy('tcp://proxy.example.com:5100');
    $data = $a->getData();
  }
}
