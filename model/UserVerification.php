<?php
/**
 * Manages user verifications, which users must verify by their contact method
 *   (usually e-mail) before an account can be accessed.
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
class UserVerification extends fActiveRecord {
  /**
   * Re-implements fActiveRecord::configure().
   *
   * @return void
   */
  protected function configure() {
    fORMDate::configureDateCreatedColumn($this, 'date_issued');
    fORMDate::configureTimezoneColumn($this, 'date_used', 'timezone');
    fORMDate::configureTimezoneColumn($this, 'date_issued', 'timezone');
  }
}
