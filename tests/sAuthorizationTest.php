<?php
/**
 * Run with --stderr argument.
 */
require './00-global.php';

class sAuthorizationTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    // HACK
    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER['REQUEST_URI'] = 'http://localhost';

    sDatabase::getInstance();
    sAuthorization::setLoginPage('/login');
  }

  public function testInitialize() {
    sAuthorization::initialize();
  }

  public function testInitializeWithResource() {
    $_SERVER['REQUEST_URI'] = 'http://localhost/file.png';
    sAuthorization::initialize();
  }

  public function testGetGuestUserId() {
    $this->assertInternalType('integer', sAuthorization::getGuestUserId());
  }

  public function testRequireAdministratorPrivileges() {
    // This is because of a redirect to 404 since routes are not established
    $this->expectOutputString('http://localhost/loginhttp://localhost/404');

    $_SERVER['REQUEST_URI'] = '/admin';
    sAuthorization::requireAdministratorPrivileges();
  }

  public function testRequireNotLoggedIn() {
    $this->assertNull(sAuthorization::requireNotLoggedIn());
    $this->assertNull(sAuthorization::requireNotLoggedIn(TRUE));
  }
}
