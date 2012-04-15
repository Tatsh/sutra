<?php
require './includes/global.inc';

class sRequestTest extends PHPUnit_Framework_TestCase {
  public function testCheckCSRFToken() {
    $csrf = fRequest::generateCSRFToken('/');
    $this->assertTrue(sRequest::checkCSRFToken($csrf, '/'));
    $this->assertFalse(sRequest::checkCSRFToken('sss', '/'));
  }

  public function testSavePostValues() {
    $_POST = array(
      'key' => 'value',
      'key1' => 'value2',
    );

    sRequest::savePostValues('aaa');
    $values = sRequest::retrievePostValues('aaa');
    $this->assertInternalType('array', $values);
    $this->assertEquals($_POST, $values);
  }

  public function testSetPostValues() {
    $original = $_POST = array(
      'key' => 'value',
      'key1' => 'value2',
    );
    sRequest::savePostValues('bbb');
    $_POST = array();
    $_SERVER['REQUEST_METHOD'] = 'POST';
    sRequest::setPostValues('bbb');
    $this->assertEquals($original, $_POST);
  }

  public function testDeletePostValues() {
    $_POST = array(
      'key' => 'value',
      'key1' => 'value2',
    );
    sRequest::savePostValues('bbb');
    sRequest::deletePostValues('bbb');
    $this->assertNull(fSession::get(sRequest::LAST_POST_SESSION_KEY_PREFIX.'::bbb'));
  }

  public function testRetrieveNotArrayFromSession() {
    fSession::set(sRequest::LAST_POST_SESSION_KEY_PREFIX.'::bbb', 'a');
    $values = sRequest::retrievePostValues('bbb');
    $this->assertInternalType('array', $values);
    $this->assertEquals(array(), $values);
  }

  public function tearDown() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    fSession::reset();
  }
}
