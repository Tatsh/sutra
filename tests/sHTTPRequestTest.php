<?php
require './includes/global.inc';

class sHTTPRequestTest extends PHPUnit_Framework_TestCase {
  const JSON_SOURCE_URI = 'https://gdata.youtube.com/feeds/api/users/google/uploads?alt=json';

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The argument specified, "aaa", is not a valid HTTP URL.
   */
  public function testConstructorBadURL() {
    new sHTTPRequest('aaa');
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The method specified, "bad", is not a valid HTTP method.
   */
  public function testConstructorBadMethod() {
    new sHTTPRequest('http://localhost', 'bad');
  }

  public function testGetHeadersAsString() {
    $headers = array(
      'User-Agent' => 'my user agent',
      'custom-header' => 'custom header',
    );
    $a = new sHTTPRequest('http://localhost');
    $a->setHeaders($headers);
    $str = $a->getHeaders(TRUE);
    $this->assertEquals("User-Agent: my user agent\r\ncustom-header: custom header\r\n", $str);
  }

  public function testSetHeaders() {
    $headers = array(
      'User-Agent' => 'my user agent',
      'custom-header' => 'custom header',
    );
    $a = new sHTTPRequest('http://localhost');
    $a->setHeaders($headers);
    $this->assertInternalType('array', $a->getHeaders());
  }

  public function testSetHeader() {
    $a = new sHTTPRequest('http://localhost');
    $a->setHeader('X-Requested-With', 'blah');
    $headers = $a->getHeaders();
    $this->assertArrayHasKey('X-Requested-With', $headers);
  }

  public function testSetUserAgent() {
    $agent = 'aaaaaa';
    $a = new sHTTPRequest('http://localhost');
    $a->setUserAgent($agent);
    $headers = $a->getHeaders();
    $this->assertArrayHasKey('User-Agent', $headers);
    $this->assertEquals($agent, $headers['User-Agent']);
  }

  public function testGetData() {
    $a = new sHTTPRequest('http://am.php.net/manual/en/context.http.php');
    $data = $a->getData();
    $this->assertTag(array(
      'tag' => 'title',
      'content' => 'PHP: HTTP context options - Manual',
    ), $data);

    $a = new sHTTPRequest('http://am.php.net/manual/en/context.http.php');
    $a->send();
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
    $a = new sHTTPRequest($url);
    $a->getData();
  }

  public function testPOST() {
    $a = new sHTTPRequest('http://am.php.net/manual/en/context.http.php', 'POST');

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
    $a = new sHTTPRequest($url);
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
    $a = new sHTTPRequest($url);
    $a->setProxy('tcp://proxy.example.com:5100');
    $data = $a->getData();
  }

  public function testGetJSON() {
    if (!function_exists('runkit_function_copy')) {
      $this->markTestSkipped('Runkit not available. Skipping test.');
    }

    $data = sHTTPRequest::getJSON(self::JSON_SOURCE_URI);
    $this->assertInternalType('object', $data);

    $data = sHTTPRequest::getJSON(self::JSON_SOURCE_URI, TRUE);
    $this->assertInternalType('array', $data);

    $code =<<<'PHP'
    if ($class_name == 'fJSON') {
      return FALSE;
    }
    return ce_original($class_name);
PHP;
    runkit_function_copy('class_exists', 'ce_original');
    runkit_function_redefine('class_exists', '$class_name', $code);
    $data = sHTTPRequest::getJSON(self::JSON_SOURCE_URI);
    $this->assertInternalType('object', $data);
    $data = sHTTPRequest::getJSON(self::JSON_SOURCE_URI, TRUE);
    $this->assertInternalType('array', $data);
    runkit_function_remove('class_exists');
    runkit_function_rename('ce_original', 'class_exists');
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage You need to have the fJSON class or the json_decode() function available.
   * @depends testGetJSON
   */
  public function testGetJSONException() {
    if (!function_exists('runkit_function_copy')) {
      $this->markTestSkipped('Runkit not available. Skipping test.');
    }

    $class_exists =<<<'PHP'
    if ($class_name == 'fJSON') {
      return FALSE;
    }
    return ce_original($class_name);
PHP;
    $function_exists =<<<'PHP'
    if ($func_name == 'json_decode') {
      return FALSE;
    }
    return fe_original($func_name);
PHP;
    runkit_function_copy('class_exists', 'ce_original');
    runkit_function_copy('function_exists', 'fe_original');
    runkit_function_redefine('class_exists', '$class_name', $class_exists);
    runkit_function_redefine('function_exists', '$func_name', $function_exists);
    sHTTPRequest::getJSON(self::JSON_SOURCE_URI);
    runkit_function_remove('class_exists');
    runkit_function_remove('function_exists');
    runkit_function_rename('ce_original', 'class_exists');
    runkit_function_rename('fe_original', 'function_exists');
  }
}
