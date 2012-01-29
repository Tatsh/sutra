<?php
/**
 * Manages Sutra-specific database handling. This does not extend off
 *   fDatabase. Instead, it manages an instance of fDatabase.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.example.com/
 *
 * @version 1.0
 */
class sDatabase {
  /**
   * The type of database.
   *
   * @var string
   */
  protected $type;

  /**
   * Database name.
   *
   * @var string
   */
  protected $name;

  /**
   * The user name to access the database.
   *
   * @var string
   */
  protected $user;

  /**
   * The database host.
   *
   * @var string
   */
  protected $host;

  /**
   * The database port.
   *
   * @var int
   */
  protected $port;

  /**
   * The database time-out time.
   *
   * @var int
   */
  protected $timeout;

  /**
   * Whether or not schema caching is enabled.
   *
   * @var bool
   */
  protected $schema_caching_enabled;

  /**
   * The fDatabase instance.
   *
   * @var fDatabase
   */
  protected $dbh;

  /**
   * The sDatabase instance.
   *
   * @var sDatabase
   */
  protected static $instance;

  /**
   * Constructor. Private to prevent external instantiation.
   *
   * @throws fEnvironmentException If the configuration file cannot be read; if
   *   connection details are invalid.
   *
   * @return sDatabase
   */
  public function __construct() {
    $file = sConfiguration::getPath().DIRECTORY_SEPARATOR.'database.ini';

    if (is_readable($file)) {
      $ini = parse_ini_file($file);
      $this->user = NULL;
      $password = NULL;
      $this->host = NULL;
      $this->port = NULL;
      $this->timeout = NULL;

      // type and database are required
      if ($ini['type'] === '' || $ini['name'] === '') {
        throw new fEnvironmentException('Type and name must be specified in database configuration file.');
      }

      $this->type = strtolower($ini['type']);
      $this->name = $ini['name'];

      if (isset($ini['user'])) {
        $this->user = $ini['user'];
      }

      if (isset($ini['password'])) {
        $password = $ini['password'];
      }

      if (isset($ini['host'])) {
        $this->host = $ini['host'];
      }

      if (isset($ini['port'])) {
        if (is_numeric($ini['port'])) {
          $this->port = (int)$ini['port'];
        }
        else {
          throw new fEnvironmentException('Only numbers are allowed for port.');
        }
      }

      if (isset($ini['timeout'])) {
        if (is_numeric($ini['timeout']) && strpos($ini['timeout'], '.') === FALSE) {
          $this->timeout = (int)$ini['timeout'];
        }
        else {
          throw new fEnvironmentException('Timeout must be specified in seconds.');
        }
      }

      if ($this->type == 'sqlite') {
        $this->dbh = new fDatabase($this->type, $this->name);
      }
      else {
        $this->dbh = new fDatabase($this->type, $this->name, $this->user, $password, $this->host, $this->port, $this->timeout);
      }

      if (isset($ini['schema_caching']) && (int)$ini['schema_caching'] == 1) {
        $this->schema_caching_enabled = TRUE;
        $this->dbh->enableCaching(sCache::getInstance());
      }

      // Connect the ORM
      fORMDatabase::attach($this->dbh);
      if ($this->schema_caching_enabled) {
        fORM::enableSchemaCaching(sCache::getInstance());
      }
    }
    else {
      throw new fEnvironmentException('Database configuration file could not be read.');
    }
  }

  /**
   * Get existing fDatabase object in any state.
   *
   * @return fDatabase
   */
  protected function getfDatabase() {
    return $this->dbh;
  }

  /**
   * Mainly because this is a singleton class that manages the connection via fDatabase.
   *
   * @return fDatabase Contrary to name, this returns the fDatabase instance
   *   rather than the sDatabase instance.
   */
  public static function getInstance() {
    if (!self::$instance) {
      self::$instance = new self;
    }

    return self::$instance->getfDatabase();
  }
}
