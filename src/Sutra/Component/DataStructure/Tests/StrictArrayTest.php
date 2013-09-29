<?php
namespace Sutra\Component\DataStructure\Tests;

use Sutra\Component\DataStructure\Dictionary;
use Sutra\Component\DataStructure\StrictArray;

class StrictArrayTest extends TestCase
{
    const walkCallback = 'Sutra\Component\DataStructure\Tests\StrictArrayTest::walkCallback';
    const filterCallback1 = 'Sutra\Component\DataStructure\Tests\StrictArrayTest::filterCallback1';
    const filterCallback2 = 'Sutra\Component\DataStructure\Tests\StrictArrayTest::filterCallback2';
    const mapCallback = 'Sutra\Component\DataStructure\Tests\StrictArrayTest::mapCallback';
    const walkCallbackRef = 'Sutra\Component\DataStructure\Tests\StrictArrayTest::walkCallbackRef';

    public function testGetData() {
        $a = new StrictArray(1, 2, 3);
        $this->assertEquals(array(1, 2, 3), $a->getData());
    }

    public function testCount() {
        $a = new StrictArray(1, 2, 3);
        $this->assertEquals(3, $a->count());
        $this->assertEquals(3, count($a));
        $this->assertEquals(3, $a->length);
    }

    public function testArrayAccess() {
        $a = new StrictArray(1, 2, 3);
        foreach ($a as $key => $value) {
            $this->assertEquals($value, $a[$key]);
        }
    }

    public function testArraySetting() {
        $a = new StrictArray(1, 2, 3);
        $a[0] = 2;
        $this->assertEquals(array(2,2,3), $a->getData());
    }

    /**
     * @expectedException Sutra\Component\DataStructure\Exception\ProgrammerException
     * @expectedExceptionMessage Offsets can only be integer. Given: "1.2"
     */
    public function testOffsetExistsBadArgument() {
        $a = new StrictArray(1, 2, 3);
        isset($a[1.2]);
    }

    /**
     * @expectedException Sutra\Component\DataStructure\Exception\ProgrammerException
     * @expectedExceptionMessage Offsets can only be integer. Given: "1.2"
     */
    public function testOffsetGetException() {
        $a = new StrictArray(1,2,3);
        $c = $a[1.2];
    }

    public function testOffsetExists() {
        $a = new StrictArray(1,2,3);
        $this->assertTrue($a->offsetExists(0));
        $this->assertFalse($a->offsetExists(3));
    }

    public function testOffsetSet() {
        $a = new StrictArray(1,2,3);
        $a[] = 2;
        $this->assertEquals(2, $a[3]);
    }

    /**
     * @expectedException Sutra\Component\DataStructure\Exception\ProgrammerException
     * @expectedExceptionMessage Offsets can only be integer. Given: "1.2"
     */
    public function testOffsetUnsetBadArgument() {
        $a = new StrictArray(1, 2, 3);
        unset($a[1.2]);
    }

    public function testOffsetUnset() {
        $a = new StrictArray(1, 2, 3);
        unset($a[0]);
    }

    public function testPop() {
        $a = new StrictArray(1,2,3);
        $this->assertEquals(3, $a->pop());
    }

    public function testPush() {
        $a = new StrictArray(1,2);
        $a->push(3);
        $this->assertEquals(3, $a->pop());
    }

    public function testFill() {
        $a = new StrictArray(1,2);
        $a->fill(10, 1);
        for ($i = 0; $i < 10; $i++) {
            $offset = $i + 2;
            $this->assertEquals(1, $a[$offset]);
        }
    }

    public function testShift() {
        $a = new StrictArray(1,2,3,4);
        $this->assertEquals(1, $a->shift());
        $this->assertEquals(array(2,3,4), $a->getData());
    }

    public function testUnshift() {
        $a = new StrictArray(2,3,4);
        $this->assertEquals($a, $a->unshift(1));
        $this->assertEquals(array(1,2,3,4), $a->getData());
    }

    public function testMerge() {
        $one = array(1,2,3,4);
        $two = array(45,46,47);
        $three = new StrictArray(100,1000,10000);

        $a = new StrictArray;
        $this->assertEquals($a, $a->merge($one));
        $this->assertEquals($one, $a->getData());

        $a = new StrictArray;
        $a->merge($one, $two);
        $this->assertEquals(array_merge($one, $two), $a->getData());

        $a = new StrictArray;
        $a->merge($one, $three);
        $this->assertEquals(array_merge($one, $three->getData()), $a->getData());
    }

    public static function walkCallback($a, $b, $user_data = NULL) {
        if (is_array($a)) {
            print join(',', $a);
        }
        else {
            print $a;
        }
        print '=>';
        print $b.',';
    }

    public function testWalk() {
        $this->expectOutputString('1=>0,2=>1,3=>2,');
        $a = new StrictArray(1,2,3);
        $this->assertEquals($a, $a->walk(self::walkCallback));
    }

    public static function walkCallbackRef(&$value, $key, $user_data = NULL) {
        $value += 3;
    }

    public function testWalkModify() {
        $a = new StrictArray(1,2,3);
        $b = array(4,5,6);
        $this->assertEquals($b, $a->walk(self::walkCallbackRef)->getData());
    }

    public function testWalkRecursive() {
        $this->expectOutputString('1=>0,2=>1,3=>2,a=>3,a=>0,a,1=>4,a=>0,1=>1,1=>0,[]=>5,[]=>6,');
        $a = new StrictArray(1,2,3, array('a'), new StrictArray('a', array(1)), new ContainerMock(), new ContainerMockInvalid());
        $this->assertEquals($a, $a->walkRecursive(self::walkCallback));
    }

    public function testPrintJSON() {
        $this->expectOutputString('[1,2,3]');
        $a = new StrictArray(1,2,3);
        $a->printJSON();
    }

    public function testToJSON() {
        $a = new StrictArray(1,2,3);
        $this->assertEquals('[1,2,3]', $a->toJSON());
    }

    public function testSearch() {
        $a = new StrictArray(1,2,3);
        $this->assertEquals(0, $a->search(1));
        $this->assertEquals(0, $a->search(TRUE));
        $this->assertFalse($a->search(TRUE, TRUE));
    }

    public function testRand() {
        $a = new StrictArray(1,2,3);
        $this->assertInternalType('array', $a->rand());
        $this->assertEquals(1, count($a->rand()));
        $this->assertEquals(2, count($a->rand(2)));
    }

    public function testDiff() {
        $a = new StrictArray(1,2,3);
        $b = new StrictArray(4,3,5);
        $diff = $a->diff($b);
        $this->assertInstanceOf('Sutra\Component\DataStructure\StrictArray', $diff);
        $this->assertEquals(array(1,2), $diff->getData());
    }

    public function testReverse() {
        $a = new StrictArray(1,2,3);
        $result = array(3,2,1);
        $this->assertEquals($result, $a->reverse()->getData());
    }

    public function testSlice() {
        $a = new StrictArray(1,2,3);
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
        $a = new StrictArray(1,2,3);
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
        $a = new StrictArray(1,2,3);
        $b = $a->map(self::mapCallback);
        $this->assertEquals(array(2,4,6), $b->getData());
    }

    public function testPad() {
        $a = new StrictArray(1,2,3);
        $b = $a->pad(10, 0);
        $this->assertEquals(array(1,2,3,0,0,0,0,0,0,0), $b->getData());
    }

    public function testUnique() {
        $a = new StrictArray(1,2,3,1,2,3,1,2,3);
        $b = $a->unique(SORT_NUMERIC);
        $this->assertEquals(array(1,2,3), $b->getData());
    }

    public function testValues() {
        $a = new StrictArray(1,2,3,1,2,3,1,2,3);
        $this->assertInternalType('array', $a->values());
        $this->assertEquals(array(1,2,3,1,2,3,1,2,3), $a->values());
    }

    public function testFlip() {
        $a = new StrictArray('a', 'b', 'c');
        $obj = $a->flip();
        $this->assertInstanceOf('Sutra\Component\DataStructure\Dictionary', $obj);
        $this->assertEquals(0, $obj['a']);
        $this->assertEquals(1, $obj->b);
        $this->assertEquals(2, $obj->c);
    }

    public function testGetUnknownProperty() {
        $a = new StrictArray(1, 2, 3);
        $this->assertNull($a->unknown_property);
    }

    public function testSort() {
        $a = new StrictArray(10,2,3,1);
        $a->sort(SORT_NUMERIC);
        $this->assertEquals(array(1,2,3,10), $a->getData());
    }

    public function testReverseSort() {
        $a = new StrictArray(10,2,3,1);
        $a->reverseSort(SORT_NUMERIC);
        $this->assertEquals(array(10,3,2,1), $a->getData());
    }
}
