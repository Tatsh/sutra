<?php
require './includes/global.inc';

class sAuthorizationTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    sAuthorization::setAuthLevels(array('admin' => 100, 'user' => 50));
  }

  public function testRequireNotLoggedInLoggedIn() {
    if (!function_exists('set_exit_overload')) {
      $this->markTestSkipped('Blocking exit functionality is not available.');
    }
    
    $this->expectOutputString('http://'.$_SERVER['SERVER_NAME'].'/');

    sAuthorization::setUserAuthLevel('admin');
    sAuthorization::requireNotLoggedIn();
  }

  public function testRequireNotLoggedIn() {
    if (!function_exists('set_exit_overload')) {
      $this->markTestSkipped('Blocking exit functionality is not available.');
    }
    
    $this->assertNull(sAuthorization::requireNotLoggedIn());
  }

  public function testRequireNotLoggedInURLArgument() {
    if (!function_exists('set_exit_overload')) {
      $this->markTestSkipped('Blocking exit functionality is not available.');
    }
    
    $this->expectOutputString('http://'.$_SERVER['SERVER_NAME'].'/404');

    sAuthorization::setUserAuthLevel('admin');
    sAuthorization::requireNotLoggedIn('/404');
  }

  public function tearDown() {
    fSession::reset();
  }
}
