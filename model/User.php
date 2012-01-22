<?php
/**
 * Manages users.
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
class User extends fActiveRecord {
  /**
   * Array of cached User objects.
   *
   * @var array
   */
  private static $cached_users = array();

  /**
   * Re-implements fActiveRecord::configure().
   *
   * @internal
   *
   * @todo Use sImage to rotate avatar according to EXIF data before any fImage calls.
   *
   * @return void
   */
  protected function configure() {
    fORMFile::configureImageUploadColumn($this, 'avatar', './files/avatars');
    fORMFile::addFImageMethodCall($this, 'avatar', 'cropToRatio', array(1, 1));
    fORMFile::addFImageMethodCall($this, 'avatar', 'resize', array(64, 64));

    // This seems to work best after already storing
    // This means we have to update manually in the callback
//     fORM::registerHookCallback($this, 'post::store()', 'User::rotateAvatarUsingEXIFData');
//     fORM::registerHookCallback($this, 'post::store()', 'User::createResizedAvatars');

    // Encrypt password upon registration
    fORM::registerHookCallback($this, 'post::populate()', 'User::encryptPasswordCallback');

    // Set up date columns
    fORMDate::configureDateCreatedColumn($this, 'date_created');
    fORMDate::configureDateUpdatedColumn($this, 'last_accessed');
    fORMDate::configureTimezoneColumn($this, 'deactivated', 'timezone');
    fORMDate::configureTimezoneColumn($this, 'date_created', 'timezone');
    fORMDate::configureTimezoneColumn($this, 'last_accessed', 'timezone');
  }

  /**
   * Callback to encrypt password using the fCryptography class.
   *
   * @return void
   */
  public static function encryptPasswordCallback($object, &$values, &$old_values, &$related_records, &$cache) {
    if (!fActiveRecord::retrieveOld($old_values, 'user_password', NULL)) {
      $password = fCryptography::hashPassword($values['user_password']);
      fActiveRecord::assign($values, $old_values, 'user_password', $password);
    }
  }

  /**
   * Get a user from the cached User objects in this class.
   *
   * @return mixed User object or NULL if the user ID is invalid.
   */
  public static function getCachedUser($user_id) {
    if (!isset(self::$cached_users[$user_id])) {
      try {
        self::$cached_users[$user_id] = new self($user_id);
      }
      catch (fNotFoundException $e) {
        self::$cached_users[$user_id] = '#NOT_FOUND';
      }
    }

    $user = self::$cached_users[$user_id];
    if ($user == '#NOT_FOUND') {
      $user = NULL;
    }

    return $user;
  }

  /**
   * Get an array representation of the user.
   *
   * @return array Array representation of user object.
   */
  public function toArray() {
    return array(
      'userId' => (int)$this->getUserId(),
      'name' => $this->getName(),
      //'authLevel' => $this->getAuthLevel(),
      'timezone' => $this->getTimezone(),
      'dateRegistered' => $this->getDateCreated()->getFuzzyDifference(),
      'lastAccessed' => $this->getLastAccessed()->getFuzzyDifference(),
    );
  }

  /**
   * Get the state of activation.
   *
   * @return boolean Whether or not the user is activated.
   */
  public function isActivated() {
    return (bool)$this->getDeactivated()->lte(0);
  }

  /**
   * Get the state of verification.
   *
   * @return boolean Whether or not the user is verified.
   */
  public function isVerified() {
    return (bool)$this->getVerified();
  }
}
