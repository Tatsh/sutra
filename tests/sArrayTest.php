<?php
require './includes/global.inc';

class testSpecialContainer2 implements ArrayAccess, IteratorAggregate {
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

class testSpecialContainer3 implements IteratorAggregate {
  private $data = array();
  public function getIterator() {
    return new ArrayIterator($this->data);
  }
  public function __toString() {
    return __CLASS__;
  }
}

class sArrayTest extends PHPUnit_Framework_TestCase {
  const walkCallback = 'sArrayTest::walkCallback';
  const filterCallback1 = 'sArrayTest::filterCallback1';
  const filterCallback2 = 'sArrayTest::filterCallback2';
  const mapCallback = 'sArrayTest::mapCallback';

  public function testGetData() {
    $a = new sArray(1, 2, 3);
    $this->assertEquals(array(1, 2, 3), $a->getData());
  }

  public function testCount() {
    $a = new sArray(1, 2, 3);
    $this->assertEquals(3, $a->count());
    $this->assertEquals(3, count($a));
    $this->assertEquals(3, $a->length);
  }

  public function testArrayAccess() {
    $a = new sArray(1, 2, 3);
    foreach ($a as $key => $value) {
      $this->assertEquals($value, $a[$key]);
    }
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Offsets can only be integer. Given: "1.2"
   */
  public function testOffsetExistsBadArgument() {
    $a = new sArray(1, 2, 3);
    isset($a[1.2]);
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Offsets can only be integer. Given: "1.2"
   */
  public function testOffsetGetException() {
    $a = new sArray(1,2,3);
    $c = $a[1.2];
  }

  public function testOffsetExists() {
    $a = new sArray(1,2,3);
    $this->assertTrue($a->offsetExists(0));
    $this->assertFalse($a->offsetExists(3));
  }

  public function testOffsetSet() {
    $a = new sArray(1,2,3);
    $a[] = 2;
    $this->assertEquals(2, $a[3]);
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Offsets can only be integer. Given: "1.2"
   */
  public function testOffsetUnsetBadArgument() {
    $a = new sArray(1, 2, 3);
    unset($a[1.2]);
  }

  public function testOffsetUnset() {
    $a = new sArray(1, 2, 3);
    unset($a[0]);
  }

  public function testPop() {
    $a = new sArray(1,2,3);
    $this->assertEquals(3, $a->pop());
  }

  public function testPush() {
    $a = new sArray(1,2);
    $a->push(3);
    $this->assertEquals(3, $a->pop());
  }

  public function testFill() {
    $a = new sArray(1,2);
    $a->fill(10, 1);
    for ($i = 0; $i < 10; $i++) {
      $offset = $i + 2;
      $this->assertEquals(1, $a[$offset]);
    }
  }

  public function testShift() {
    $a = new sArray(1,2,3,4);
    $this->assertEquals(1, $a->shift());
    $this->assertEquals(array(2,3,4), $a->getData());
  }

  public function testUnshift() {
    $a = new sArray(2,3,4);
    $this->assertEquals($a, $a->unshift(1));
    $this->assertEquals(array(1,2,3,4), $a->getData());
  }

  public function testMerge() {
    $one = array(1,2,3,4);
    $two = array(45,46,47);
    $three = new sArray(100,1000,10000);

    $a = new sArray;
    $this->assertEquals($a, $a->merge($one));
    $this->assertEquals($one, $a->getData());

    $a = new sArray;
    $a->merge($one, $two);
    $this->assertEquals(array_merge($one, $two), $a->getData());

    $a = new sArray;
    $a->merge($one, $three);
    $this->assertEquals(array_merge($one, $three->getData()), $a->getData());
  }

  public static function walkCallback($a, $b, $user_data = NULL) {
    print $a.'=>'.$b.',';
  }

  public function testWalk() {
    $this->expectOutputString('1,2,3=>0,1,2,3=>1,1,2,3=>2,');
    $a = new sArray(1,2,3);
    $this->assertEquals($a, $a->walk(self::walkCallback));
  }

  public function testWalkRecursive() {
    $this->expectOutputString('0=>1,1=>2,2=>3,3=>Array,0=>a,4=>a,1,0=>a,1=>Array,0=>1,5=>testSpecialContainer2,6=>testSpecialContainer3,');
    $a = new sArray(1,2,3, array('a'), new sArray('a', array(1)), new testSpecialContainer2, new testSpecialContainer3);
    $this->assertEquals($a, $a->walkRecursive(self::walkCallback));
  }

  public function testPrintJSON() {
    $this->expectOutputString('[1,2,3]');
    $a = new sArray(1,2,3);
    $a->printJSON();
  }

  public function testToJSON() {
    $a = new sArray(1,2,3);
    $this->assertEquals('[1,2,3]', $a->toJSON());
  }

  public function testSearch() {
    $a = new sArray(1,2,3);
    $this->assertEquals(0, $a->search(1));
    $this->assertEquals(0, $a->search(TRUE));
    $this->assertFalse($a->search(TRUE, TRUE));
  }

  public function testRand() {
    $a = new sArray(1,2,3);
    $this->assertInternalType('array', $a->rand());
    $this->assertEquals(1, count($a->rand()));
    $this->assertEquals(2, count($a->rand(2)));
  }

  public function testDiff() {
    $a = new sArray(1,2,3);
    $b = new sArray(4,3,5);
    $diff = $a->diff($b);
    $this->assertInstanceOf('sArray', $diff);
    $this->assertEquals(array(1,2), $diff->getData());
  }

  public function testReverse() {
    $a = new sArray(1,2,3);
    $result = array(3,2,1);
    $this->assertEquals($result, $a->reverse()->getData());
  }

  public function testSlice() {
    $a = new sArray(1,2,3);
    $result = array(2,3);
    $this->assertEquals($result, $a->slice(1)->getData());

    $this->assertEquals(array(2), $a->slice(1, 1)->getData());
  }


  public static function filterCallback1() {
    return TRUE;
  }

  public static function filterCallback2($var) {
    if ($var === 1) {
      return FALSE;
    }
    return TRUE;
  }

  public function testFilter() {
    $a = new sArray(1,2,3);
    $b = $a->filter(self::filterCallback1);
    $this->assertNotSame($b, $a);
    $this->assertEquals($a->getData(), $b->getData());

    $b = $a->filter(self::filterCallback2);
    $this->assertEquals(array(2,3), $b->getData());
  }

  public static function mapCallback($n) {
    return $n * 2;
  }

  public function testMap() {
    $a = new sArray(1,2,3);
    $b = $a->map(self::mapCallback);
    $this->assertEquals(array(2,4,6), $b->getData());
  }

  public function testPad() {
    $a = new sArray(1,2,3);
    $b = $a->pad(10, 0);
    $this->assertEquals(array(1,2,3,0,0,0,0,0,0,0), $b->getData());
  }

  public function testUnique() {
    $a = new sArray(1,2,3,1,2,3,1,2,3);
    $b = $a->unique(SORT_NUMERIC);
    $this->assertEquals(array(1,2,3), $b->getData());
  }

  public function testValues() {
    $a = new sArray(1,2,3,1,2,3,1,2,3);
    $this->assertInternalType('array', $a->values());
    $this->assertEquals(array(1,2,3,1,2,3,1,2,3), $a->values());
  }

  public function testFlip() {
    $a = new sArray('a', 'b', 'c');
    $obj = $a->flip();
    $this->assertInstanceOf('sObject', $obj);
    $this->assertEquals(0, $obj['a']);
    $this->assertEquals(1, $obj->b);
    $this->assertEquals(2, $obj->c);
  }

  public function testGetUnknownProperty() {
    $a = new sArray(1, 2, 3);
    $this->assertNull($a->unknown_property);
  }
}
