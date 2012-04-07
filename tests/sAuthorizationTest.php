<?php
require './includes/global.inc';

class sAuthorizationTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    sAuthorization::setAuthLevels(array('admin' => 100, 'user' => 50));
  }

  public function testRequireNotLoggedInLoggedIn() {
    $this->expectOutputString('http://'.$_SERVER['SERVER_NAME'].'/');

    sAuthorization::setUserAuthLevel('admin');
    sAuthorization::requireNotLoggedIn();
  }

  public function testRequireNotLoggedIn() {
    $this->assertNull(sAuthorization::requireNotLoggedIn());
  }

  public function testRequireNotLoggedInURLArgument() {
    $this->expectOutputString('http://'.$_SERVER['SERVER_NAME'].'/404');

    sAuthorization::setUserAuthLevel('admin');
    sAuthorization::requireNotLoggedIn('/404');
  }
}
