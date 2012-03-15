<?php
/**
 * Manages category names.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraModel
 * @link http://www.example.com/
 *
 * @version 1.0
 */
class Category extends fActiveRecord {
  /**
   * Implements fActiveRecord::configure().
   *
   * @internal
   *
   * @return void
   */
  protected function configure() {
    fORMDate::configureDateCreatedColumn($this, 'date_created');
    fORMDate::configureDateUpdatedColumn($this, 'date_updated');
  }

  /**
   * Get all the categories as a simple array with ID as the key.
   *
   * @param boolean $no_cache Do not try to retrieve from cache.
   * @return array Array of categories.
   */
  public static function buildAsArray($no_cache = FALSE) {
    $cache = sCache::getInstance();
    $cached = $cache->get(__CLASS__.'::buildAsArray');

    if (!$cached || $no_cache) {
      $cached = array();
      $records = fRecordSet::build(__CLASS__, array(), array('name' => 'asc'));

      foreach ($records as $record) {
        $cached[$record->getCategoryId()] = __($record->getName());
      }

      $cache->set(__CLASS__.'::buildAsArray', $cached, 7200);
    }

    return $cached;
  }
}
