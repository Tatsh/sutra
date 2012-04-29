<?php
require './includes/global.inc';

class sCRUDFormTest extends PHPUnit_Framework_TestCase {
  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Action URL is invalid. Must be at least 1 character long
   */
  public function testConstructorBadAction() {
    new sCRUDForm('a', '');
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Request method "delete" is invalid. Must be one of: get,post
   */
  public function testConstructorBadMethod() {
    new sCRUDForm('a', '/', 'delete');
  }
}
