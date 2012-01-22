<?php
/**
 * All messages sent from /contact are also stored in the database.
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
class ContactMailMessage extends fActiveRecord {
  /**
   * Re-implements fActiveRecord::configure().
   *
   * @internal
   *
   * @return void
   */
  protected function configure() {
    fORMDate::configureDateCreatedColumn($this, 'date_created');
  }
}
