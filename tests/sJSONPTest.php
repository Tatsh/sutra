<?php
require './includes/global.inc';

class sJSONPTest extends PHPUnit_Framework_TestCase {
  /**
   * @expectedException fValidationException
   */
  public function testEncodeBadCallbackReservedWord() {
    sJSONP::encode(array('data' => 'a'), 'super');
  }

  public function testEncode() {
    $code = sJSONP::encode(array('data' => 'a'), 'goodCallback');
    $this->assertEquals('goodCallback({"data":"a"});', $code);

    $code = sJSONP::encode(1, 'goodCallback');
    $this->assertEquals('goodCallback(1);', $code);

    $code = sJSONP::encode(2.1, 'goodCallback');
    $this->assertEquals('goodCallback(2.1);', $code);

    $code = sJSONP::encode(true, 'goodCallback');
    $this->assertEquals('goodCallback(true);', $code);

    $b = new stdClass;
    $b->data = 'a';
    $code = sJSONP::encode($b, 'goodCallback');
    $this->assertEquals('goodCallback({"data":"a"});', $code);

    // No callback specified
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $code = sJSONP::encode($b);
    $this->assertStringStartsWith('fn(', $code);
  }
}
