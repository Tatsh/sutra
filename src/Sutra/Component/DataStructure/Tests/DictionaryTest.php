<?php
namespace Sutra\Component\DataStructure\Tests;

use Sutra\Component\DataStructure\Dictionary;

class DictionaryTest extends TestCase
{
    /**
     * @covers Sutra\Component\DataStructure\Dictionary::__construct
     */
    public function testConstructor() {
        new Dictionary(array('a' => 'b'));
    }

    /**
     * @expectedException Sutra\Component\DataStructure\Exception\ProgrammerException
     * @expectedExceptionMessage All keys must be non-empty strings. Error at key: "{empty_string}"
     */
    public function testConstructorBadKey() {
        new Dictionary(array('' => 'my value'));
    }

    public function testCheckRequiredKeys() {
        $obj = new Dictionary(array('a' => 'b'));
        $this->assertFalse($obj->checkRequiredKeys('b', 'c'));
        $this->assertTrue($obj->checkRequiredKeys('a'));
    }

    public function testGetLastMissingKeyAfterCheck() {
        $obj = new Dictionary(array('a' => 'b'));
        $this->assertFalse($obj->checkRequiredKeys('b', 'c'));
        $this->assertEquals('b', $obj->getLastMissingKey());
    }

    public function testValidateRequiredKeys() {
        $obj = new Dictionary(array('a' => 'b'));
        $this->assertEquals($obj, $obj->validateRequiredKeys('a'));
    }

    /**
     * @expectedException Sutra\Component\DataStructure\Exception\ValidationException
     * @expectedExceptionMessage The object is missing a key: "b"
     */
    public function testValidateRequiredKeysException() {
        $obj = new Dictionary(array('a' => 'b'));
        $obj->validateRequiredKeys('b', 'c');
    }

    public function testConvertKeyCase() {
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
        $result = new Dictionary(array('A' => 'b', 'B' => 'c', 'C' => 'd'));
        $new = $obj->convertKeyCase(CASE_UPPER);
        $this->assertEquals($result, $new);

        $new = $obj->convertKeyCase(CASE_LOWER);
        $this->assertEquals($obj, $new);
    }

    /**
     * @expectedException Sutra\Component\DataStructure\Exception\ProgrammerException
     * @expectedExceptionMessage Case argument must be one of the constants: "CASE_LOWER, CASE_UPPER"
     */
    public function testConvertKeyCaseBadCase() {
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
        $obj->convertKeyCase('aaaaa');
    }

    public function testCount() {
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
        $this->assertEquals(3, $obj->count());
        $this->assertEquals(3, count($obj));
    }

    public function testDiff() {
        $one = array('a' => 'b', 'b' => 'c', 'c' => 'd');
        $two = array('a' => 'b', 'b' => 'd', 'd' => 'e');
        $three = array('a' => 'b', 'b' => 'c', 'd' => 'e');

        $obj = new Dictionary($one);
        $diff = $obj->diff($two);
        $this->assertEquals(array('b' => 'c'), $diff->getData());

        $diff = $obj->diff($three);
        $this->assertEquals(array('c' => 'd'), $diff->getData());

        $diff = $obj->diff($two, $three);
        $this->assertEquals(array(), $diff->getData());
    }

    public function testFill() {
        $obj = new Dictionary;
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
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
        $new = $obj->filter(array(__CLASS__, 'filterCallback1'));
        $this->assertEquals($obj, $new);

        $new = $obj->filter(array(__CLASS__, 'filterCallback2'));
        $this->assertEquals(array('b' => 'c', 'c' => 'd'), $new->getData());
    }

    public function testKeys() {
        $keys = array('b', 'd', 'c');
        $obj = new Dictionary(array('b' => 1, 'd' => 2, 'c' => 3));
        $this->assertEquals($keys, $obj->keys());

        sort($keys, SORT_STRING);
        $this->assertEquals($keys, $obj->keys(TRUE));
    }

    public function testMerge() {
        $begin = array('a' => 'b', 'b' => 'c', 'c' => 'd');
        $merge = new Dictionary(array('d' => 'e', 'e' => 'f', 'f' => 'g'));
        $merge2 = array('g' => 'h', 'h' => 'i', 'i' => 'j');
        $obj = new Dictionary($begin);
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
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c'));
        $obj->printJSON();
    }

    public function testRand() {
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c'));
        $ret = $obj->rand();
        $this->assertInternalType('array', $ret);
        $this->assertEquals(1, count($ret));

        $ret = $obj->rand(2);
        $this->assertEquals(2, count($ret));

        $ret = $obj->rand(3); // invalid; warnings are blocked
        $this->assertEquals(1, count($ret));
    }

    public function testSearch() {
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
        $this->assertFalse($obj->search('e'));
        $this->assertEquals('b', $obj->search('c'));

        $obj['new'] = TRUE;
        $this->assertEquals('new', $obj->search(1));
        $this->assertNotEquals('new', $obj->search(1, TRUE));
        $this->assertEquals('new', $obj->search(TRUE, TRUE));
    }

    public function testToJSON() {
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c'));
        $this->assertEquals('{"a":"b","b":"c"}', $obj->toJSON());
    }

    public function testToString() {
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
        $this->assertEquals('{"a":"b","b":"c","c":"d"}', (string) $obj);
    }

    public function testValues() {
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
        $expected = array('b', 'c', 'd');
        $this->assertEquals($expected, $obj->values());
    }

    public static function walkCallback($value, $key, $user_data = NULL) {
        if (is_array($value)) { // avoid 'Array to string conversion' in PHP 5.4
            foreach ($value as $k => $v) {
                print $k.'=>'.(is_array($v) ? join(',', $v) : $v).',';
            }
        }
        else {
            print $key;
            print '=>'.$value.',';
        }

        if ($user_data) {
            print ':';
        }
    }

    public function testWalk() {
        $contained = new ContainerMock();
        $contained['a'] = 1;

        $this->expectOutputString('a=>b,b=>c,0=>1,1=>2,3,0=>1,0=>2,1=>3,0=>2,1=>3,e=>{"a":1},a=>1,');
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'd' => array(1, array(2, 3)), 'e' => $contained));
        $obj->walkRecursive(array(__CLASS__, 'walkCallback'));
    }

    public function testWalkRecursive() {
        $contained = new ContainerMock();
        $contained['a'] = 1;
        $non_array = new ContainerMockInvalid();

        $this->expectOutputString('a=>b,b=>c,0=>1,1=>2,3,0=>1,0=>2,1=>3,0=>2,1=>3,e=>{"b":"c"},b=>c,f=>{"a":1},a=>1,g=>[],');
        $obj1 = new Dictionary(array('b' => 'c'));
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'd' => array(1, array(2, 3)), 'e' => $obj1, 'f' => $contained, 'g' => $non_array));
        $obj->walkRecursive(array(__CLASS__, 'walkCallback'));
    }

    public function testArrayAccess() {
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'c' => 'd'));

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
     * @expectedException Sutra\Component\DataStructure\Exception\ProgrammerException
     * @expectedExceptionMessage Key must be a non-empty string.
     */
    public function testArrayAccessException() {
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
        $obj[''] = 1;
    }

    public function testObjectAccess() {
        $obj = new Dictionary(array('a' => 'b', 'b' => 'c', 'c' => 'd'));
        $obj->e = 1;
        $this->assertEquals(1, $obj['e']);
    }
}
