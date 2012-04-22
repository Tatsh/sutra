<?php
require './includes/global.inc';

class testSpecialContainer4 implements ArrayAccess, IteratorAggregate {
  private $data = array();
  public function getIterator() {
    return new ArrayIterator($this->data);
  }
  public function offsetExists($offset) {
    return FALSE;
  }
  public function offsetGet($offset) {
    return NULL;
  }
  public function offsetSet($offset, $value) {
    $this->data[$offset] = $value;
  }
  public function offsetUnset($offset) {
    unset($this->data[$offset]);
  }
  public function __toString() {
    return __CLASS__;
  }
}

class testSpecialContainer5 implements IteratorAggregate {
  private $data = array();
  public function getIterator() {
    return new ArrayIterator($this->data);
  }
  public function __toString() {
    return __CLASS__;
  }
}

class sObjectTest extends PHPUnit_Framework_TestCase {
  /**
   * @covers sObject::__construct
   */
  public function testConstructor() {
    new sObject(array('a' => 'b'));
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage All keys must be non-empty strings. Error at key: "{empty_string}"
   */
  public function testConstructorBadKey() {
    new sObject(array('' => 'my value'));
  }

  public function testCheckRequiredKeys() {
    $obj = new sObject(array('a' => 'b'));
    $this->assertFalse($obj->checkRequiredKeys('b', 'c'));
    $this->assertTrue($obj->checkRequiredKeys('a'));
  }

  public function testGetLastMissingKeyAfterCheck() {
    $obj = new sObject(array('a' => 'b'));
    $this->assertFalse($obj->checkRequiredKeys('b', 'c'));
    $this->assertEquals('b', $obj->getLastMissingKey());
  }

  public function testValidateRequiredKeys() {
    $obj = new sObject(array('a' => 'b'));
    $this->assertEquals($obj, $obj->validateRequiredKeys('a'));
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The object is missing a key: "b"
   */
  public function testValidateRequiredKeysException() {
    $obj = new sObject(array('a' => 'b'));
    $obj->validateRequiredKeys('b', 'c');
  }

  public function testConvertKeyCase() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
    $result = new sObject(array('A' => 'b', 'B' => 'c', 'C' => 'd'));
    $new = $obj->convertKeyCase(CASE_UPPER);
    $this->assertEquals($result, $new);

    $new = $obj->convertKeyCase(CASE_LOWER);
    $this->assertEquals($obj, $new);
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Case argument must be one of: "CASE_LOWER, CASE_UPPER"
   */
  public function testConvertKeyCaseBadCase() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
    $obj->convertKeyCase(200938098);
  }

  public function testCount() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
    $this->assertEquals(3, $obj->count());
    $this->assertEquals(3, count($obj));
  }

  public function testDiff() {
    $one = array('a' => 'b', 'b' => 'c', 'c' => 'd');
    $two = array('a' => 'b', 'b' => 'd', 'd' => 'e');
    $three = array('a' => 'b', 'b' => 'c', 'd' => 'e');

    $obj = new sObject($one);
    $diff = $obj->diff($two);
    $this->assertEquals(array('b' => 'c'), $diff->getData());

    $diff = $obj->diff($three);
    $this->assertEquals(array('c' => 'd'), $diff->getData());

    $diff = $obj->diff($two, $three);
    $this->assertEquals(array(), $diff->getData());
  }

  public function testFill() {
    $obj = new sObject;
    $obj->fill(array('a', 'b', 'c'), 1);
    $this->assertEquals(array('a' => 1, 'b' => 1, 'c' => 1), $obj->getData());
  }

  public static function filterCallback1() {
    return TRUE;
  }

  public static function filterCallback2($var) {
    if ($var === 'b') {
      return FALSE;
    }
    return TRUE;
  }

  public function testFilter() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
    $new = $obj->filter('sObjectTest::filterCallback1');
    $this->assertEquals($obj, $new);

    $new = $obj->filter('sObjectTest::filterCallback2');
    $this->assertEquals(array('b' => 'c', 'c' => 'd'), $new->getData());
  }

  public function testGetLastMissingKey() {

  }

  public function testKeys() {
    $keys = array('b', 'd', 'c');
    $obj = new sObject(array('b' => 1, 'd' => 2, 'c' => 3));
    $this->assertEquals($keys, $obj->keys());

    sort($keys, SORT_STRING);
    $this->assertEquals($keys, $obj->keys(TRUE));
  }

  public function testMerge() {
    $begin = array('a' => 'b', 'b' => 'c', 'c' => 'd');
    $merge = new sObject(array('d' => 'e', 'e' => 'f', 'f' => 'g'));
    $merge2 = array('g' => 'h', 'h' => 'i', 'i' => 'j');
    $obj = new sObject($begin);
    $obj->merge($merge, $merge2);

    // Although order should not matter
    $this->assertEquals(array(
      'a' => 'b',
      'b' => 'c',
      'c' => 'd',
      'd' => 'e',
      'e' => 'f',
      'f' => 'g',
      'g' => 'h',
      'h' => 'i',
      'i' => 'j',
    ), $obj->getData());
  }

  public function testPrintJSON() {
    $this->expectOutputString('{"a":"b","b":"c"}');
    $obj = new sObject(array('a' => 'b', 'b' => 'c'));
    $obj->printJSON();
  }

  public function testRand() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c'));
    $ret = $obj->rand();
    $this->assertInternalType('array', $ret);
    $this->assertEquals(1, count($ret));

    $ret = $obj->rand(2);
    $this->assertEquals(2, count($ret));

    $ret = $obj->rand(3); // invalid; warnings are blocked
    $this->assertEquals(1, count($ret));
  }

  public function testSearch() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
    $this->assertFalse($obj->search('e'));
    $this->assertEquals('b', $obj->search('c'));

    $obj['new'] = TRUE;
    $this->assertEquals('new', $obj->search(1));
    $this->assertNotEquals('new', $obj->search(1, TRUE));
    $this->assertEquals('new', $obj->search(TRUE, TRUE));
  }

  public function testToJSON() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c'));
    $this->assertEquals('{"a":"b","b":"c"}', $obj->toJSON());
  }

  public function testToString() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
    $this->assertEquals('sObject', (string)$obj);
  }

  public function testValues() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
    $expected = array('b', 'c', 'd');
    $this->assertEquals($expected, $obj->values());
  }

  public static function walkCallback($key, $value, $user_data = NULL) {
    print $key.'=>'.$value.',';

    if ($user_data) {
      print ':';
    }
  }

  public function testWalk() {
    $contained = new testSpecialContainer4;
    $contained['a'] = 1;

    $this->expectOutputString('sObject=>a,sObject=>b,sObject=>d,sObject=>e,');
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'd' => array(1, array(2, 3)), 'e' => $contained));
    $obj->walk('sObjectTest::walkCallback');
  }

  public function testWalkRecursive() {
    $contained = new testSpecialContainer4;
    $contained['a'] = 1;
    $non_array = new testSpecialContainer5;

    $this->expectOutputString('a=>b,b=>c,d=>Array,0=>1,1=>Array,0=>2,1=>3,e=>sObject,b=>c,f=>testSpecialContainer4,a=>1,g=>testSpecialContainer5,');
    $obj1 = new sObject(array('b' => 'c'));
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'd' => array(1, array(2, 3)), 'e' => $obj1, 'f' => $contained, 'g' => $non_array));
    $obj->walkRecursive('sObjectTest::walkCallback');
  }

  public function testArrayAccess() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'c' => 'd'));

    foreach ($obj as $key => $value) {
      $this->assertEquals($value, $obj[$key]);
      $this->assertEquals($value, $obj->$key);
    }

    $this->assertFalse(isset($obj['d']));
    $obj['d'] = 1;
    $this->assertEquals(1, $obj['d']);
    $this->assertEquals(1, $obj->d);

    unset($obj['d']);
    $this->assertNotEquals(1, $obj->d);
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Key must be a non-empty string.
   */
  public function testArrayAccessException() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
    $obj[''] = 1;
  }

  public function testObjectAccess() {
    $obj = new sObject(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
    $obj->e = 1;
    $this->assertEquals(1, $obj['e']);
  }
}
