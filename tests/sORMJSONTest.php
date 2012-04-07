<?php
require './includes/global.inc';

class TestTable extends fActiveRecord {}
class OtherTable extends fActiveRecord {}

class sORMJSONTest extends PHPUnit_Framework_TestCase {
  /**
   * @var fDatabase
   */
  private static $db = NULL;

  public static function setUpBeforeClass() {
    $sql =<<<SQL
    DROP TABLE IF EXISTS test_tables;
    CREATE TABLE test_tables (
      tid INTEGER AUTOINCREMENT PRIMARY KEY,
      column_a TEXT NOT NULL
    );
    DROP TABLE IF EXISTS other_tables;
    CREATE TABLE other_tables (
      tid INTEGER AUTOINCREMENT PRIMARY KEY
    );
SQL;

    self::$db = new fDatabase('sqlite', './resources/db.sqlite3');
    self::$db->translatedExecute($sql);
  }

  public static function tearDownAfterClass() {
    self::$db->translatedExecute('DROP TABLE test_tables');
    self::$db->translatedExecute('DROP TABLE other_tables');
  }

  /**
   * @expectedException fProgrammerException
   */
  public function testConfigureException() {
    fORMDatabase::attach(self::$db);
    sORMJSON::configureJSONSerializedColumn('TestTable', 'tid');
  }

  public function testGetUnserializedJSONArray() {
    fORMDatabase::attach(self::$db);
    sORMJSON::configureJSONSerializedColumn('TestTable', 'column_a');

    $record = new TestTable;
    $record->setColumnA(array('a' => 1, 'b' => array(1,2,3), 'c' => array('c' => 1)));
    $record->store();
    $id = $record->getTid();

    $record = new TestTable($id);
    $array = $record->getColumnA();
    $this->assertInternalType('array', $array);
    $this->assertInternalType('integer', $array['a']);
    $this->assertInternalType('array', $array['b']);
    $this->assertInternalType('integer', $array['b'][0]);
    $this->assertInternalType('integer', $array['b'][1]);
    $this->assertInternalType('integer', $array['b'][2]);
    $this->assertInternalType('array', $array['c']);
    $this->assertInternalType('integer', $array['c']['c']);
  }

  public function testGetUnserializedJSONObject() {
    sORMJSON::configureJSONSerializedColumn('TestTable', 'column_a', FALSE);

    $record = new TestTable;
    $record->setColumnA(array('a' => 1, 'b' => array(1,2,3), 'c' => array('c' => 1)));
    $record->store();
    $id = $record->getTid();

    $record = new TestTable($id);
    $this->assertInternalType('object', $record->getColumnA());
    $this->assertInternalType('integer', $record->getColumnA()->a);
    $this->assertInternalType('array', $record->getColumnA()->b);
    $this->assertInternalType('integer', $record->getColumnA()->b[0]);
    $this->assertInternalType('integer', $record->getColumnA()->b[1]);
    $this->assertInternalType('integer', $record->getColumnA()->b[2]);
    $this->assertInternalType('object', $record->getColumnA()->c);
    $this->assertInternalType('integer', $record->getColumnA()->c->c);
  }

  public function testNonRegisteredClass() {
    $record = new OtherTable;
    $values = $old_values = $related_records = $cache = array();

    sORMJSON::JSONToValue($record, $values, $old_values, $related_records, $cache);
    $this->assertEquals(array(), $values);

    sORMJSON::valueToJSON($record, $values, $old_values, $related_records, $cache);
    $this->assertEquals(array(), $values);
  }
}
