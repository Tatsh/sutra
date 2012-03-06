<?php
class sCacheTest extends PHPUnit_Framework_TestCase {
  public function testGetSiteUniqueKey() {
    $cwd = getcwd();
    $key = sCache::getSiteUniqueKey('key');

    $this->assertStringStartsWith('sCache', $key);
    $this->assertStringStartsWith('sCache::'.$cwd.'::', $key);
    $this->assertEquals('sCache::'.$cwd.'::key', $key);

    $key = sCache::getSiteUniqueKey('key2', 'myClass');
    $this->assertStringStartsWith('myClass', $key);
    $this->assertStringStartsWith('myClass::'.$cwd.'::', $key);
    $this->assertEquals('myClass::'.$cwd.'::key2', $key);
  }
}
