<?php
require './includes/global.inc';

class CrudTestTable extends fActiveRecord {}

class sCRUDFormTest extends PHPUnit_Framework_TestCase {
  /**
   * @var fDatabase
   */
  private static $db = NULL;

  public static function setUpBeforeClass() {
    $sql =<<<SQL
    DROP TABLE IF EXISTS crud_test_tables;
    CREATE TABLE crud_test_tables (
      tid INTEGER AUTOINCREMENT PRIMARY KEY,
      column_a TEXT NOT NULL
    );
SQL;

    self::$db = new fDatabase('sqlite', './resources/db.sqlite3');
    self::$db->translatedExecute($sql);
    fORMDatabase::attach(self::$db);
  }

  public static function tearDownAfterClass() {
    self::$db->translatedExecute('DROP TABLE crud_test_tables');
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Request method "delete" is invalid. Must be one of: get,post
   */
  public function testConstructorBadMethod() {
    new sCRUDForm('CrudTestTable', '/', 'delete');
  }
}
