<?php
require './includes/global.inc';

/**
 * Just for code coverage purposes.
 */
class sResponseTest extends PHPUnit_Framework_TestCase {
  public function testSendNotModifiedHeader() {
    $age = strtotime('-2 weeks');
    $etag = 'aaaa';
    sResponse::sendNotModifiedHeader($age, $etag);
  }

  public function testSendNotModifiedHeaderNoneMatch() {
    $age_timestamp = new fTimestamp('-2 weeks');
    $etag = 'aaaa';
    $_SERVER['HTTP_IF_NONE_MATCH'] = $etag;
    sResponse::sendNotModifiedHeader($age_timestamp, $etag);
    unset($_SERVER['HTTP_IF_NONE_MATCH']);
  }

  public function testSendNotModifiedHeaderModifiedSince() {
    $age_timestamp = strtotime('-2 weeks');
    $gmdate = gmdate('D, d M Y H:i:s', $age_timestamp).' GMT';
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $gmdate;
    sResponse::sendNotModifiedHeader($age_timestamp, 'a');
    unset($_SERVER['HTTP_IF_MODIFIED_SINCE']);
  }

  public static function testSendForbiddenHeader() {
    sResponse::sendForbiddenHeader();
  }
}
