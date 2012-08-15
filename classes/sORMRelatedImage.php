<?php
/**
 * Provides image to active record relationship functionality.
 *
 * @copyright Copyright (c) 2012 bne1.
 * @author Andrew Udvare [au] <andrew@bne1.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.sutralib.com/
 *
 * @version 1.3
 */
class sORMRelatedImage {
  /**
   * Configured tables.
   *
   * @var array
   */
  private static $configured_classes = array();
  
  /**
   * Configures a related image column on another table.
   *
   * If the related table primary key is not passed, this assumes that $column
   *   name specified is the same name on the related table.
   *
   * @throws fProgrammerException If $related_table_pk is unspecified and the
   *   related table primary key is not passed and the primary key names are
   *   not the same.
   * 
   * @param string|fActiveRecord $class The name of the class, or instance.
   * @param string $column The column for the image class.
   * @param string $related_table Related table name with the values.
   * @return void
   *
   * @todo
   */
  public static function configure($class, $column, $related_table, $related_table_pk = NULL) {
    $class = fORM::getClass($class);

    if (isset(self::$configured_classes[$class])) {
      return;
    }
    
    $table = fORM::tablize($class);
    $schema = fORMSchema::retrieve($class);
    $table_info = $schema->getColumnInfo($table);
    $table_keys = $schema->getKeys($table);
    $related_table_info = $schema->getColumnInfo($related_table);
    $related_table_keys = $schema->getKeys($related_table);
    $related_table_pk = $related_table_pk === NULL ? $table_keys['primary'][0] : $related_table_pk;

    if (!array_key_exists($related_table_pk, $related_table_keys)) {
      throw new fProgrammerException('Related table primary key was unspecified and key "%s" does not exist in the table "%s"', $related_table_pk, $table);
    }
    
    self::$configured_classes[$class] = TRUE;
  }
}
