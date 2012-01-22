<?php
/**
 * Manages site variables that are stored in the database table
 *   site_variables.
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
class SiteVariable extends fActiveRecord {
  /**
   * The default amount of time to cache values retrieved.
   *
   * @var integer
   */
  const DEFAULT_TTL = 3600;

  /**
   * fCache instance.
   *
   * @var fCache
   */
  protected static $variable_cache = NULL;

  /**
   * The current working directory.
   *
   * @var string
   */
  private static $cwd = '';

  /**
   * Whether or not the class is initialised.
   *
   * @var boolean
   */
  private static $initialized = FALSE;

  /**
   * Initialise the class.
   *
   * @return void
   */
  private static function initialize() {
    if (!self::$initialized) {
      self::$cwd = getcwd();
      self::$variable_cache = sCache::getInstance();
      self::$initialized = TRUE;
    }
  }

  /**
   * Get a value. The value retrieved will be cached for one hour.
   *   This cannot be named get because there exists a get method in
   *   fActiveRecord.
   *
   * If upon database lookup the key is not found, a record will be created
   *   with the default value.
   *
   * @param string $key Key name.
   * @param string $cast_to One of: 'int', 'integer', 'float', 'string', 'object',
    *  'array', 'unset', 'null'.
   * @param mixed $default Default value to return if not found.
   * @param integer $ttl Time to live in cache. Default is 3600 seconds.
   * @return mixed The value retrieved, unserialised.
   */
  public static function getValue($key, $cast_to = NULL, $default = NULL, $ttl = NULL) {
    self::initialize();

    $cache = self::$variable_cache;
    $cache_key = __CLASS__.'::'.self::$cwd.'::'.$key;
    $unserialized_value = NULL;
    $needs_caching = FALSE;

    if (is_null($value = $cache->get($cache_key))) {
      try {
        $var = new self($key);
        $value = $var->getValueString();
        $unserialized_value = unserialize($value);
        $needs_caching = TRUE;
      }
      catch (fNotFoundException $e) {
        $var = new self;
        $var->setName($key);
        $var->setValueString(serialize($default));
        $var->store();
        $unserialized_value = $default;
        $needs_caching = TRUE;
      }
    }
    else {
      $unserialized_value = $value;
    }

    if ($needs_caching) {
      $cache->set($cache_key, $unserialized_value, is_null($ttl) ? self::DEFAULT_TTL : (int)$ttl);
    }
    return !is_null($cast_to) ? self::cast($cast_to, $unserialized_value) : $unserialized_value;
  }

  /**
   * Set a value. Cannot be named set because there exists a set method in
   *   fActiveRecord.
   *
   * If you store a value with the string '############NO_VALUE', then this key
   *   ALWAYS be database queried for (undesirable).
   *
   * @param string $key The key name.
   * @param mixed $value The value to store.
   * @param integer $ttl Time to live in cache. Default is 3600 seconds (1 hour).
   * @return SiteVariable The object to allow for method chaining.
   */
  public static function setValue($key, $value, $ttl = NULL) {
    self::initialize();
    $stored_value = serialize($value);

    try {
      $record = new self($key);
      $record->setValueString($stored_value);
      $record->store();
    }
    catch (fNotFoundException $e) {
      $record = new self;
      $record->setName($key);
      $record->setValueString($stored_value);
      $record->store();
    }

    $cache_key = __CLASS__.'::'.self::$cwd.'::'.$key;
    self::$variable_cache->set($cache_key, $value, is_null($ttl) ? self::DEFAULT_TTL : (int)$ttl);

    return $this;
  }

  /**
   * Cast a value.
   *
   * If the type of $value is object, and the cast is to float, the object
   *   will be returned.
   *
   * @param string $to One of: 'int', 'integer', 'float', 'string', 'object',
   *   'array', 'unset', 'null'.
   * @param $value The value to cast.
   * @return mixed The value, casted.
   */
  private static function cast($to, $value) {
    self::initialize();

    $to = strtolower($to);
    if (!$to) {
      return $value;
    }

    // Do not cast objects to float as this might result in a warning
    if (is_object($value) && $to == 'float') {
      return $value;
    }

    switch ($to) {
      case 'bool':
      case 'boolean':
        $value = (bool)$value;
        break;

      case 'int':
      case 'integer':
        $value = (int)$value;
        break;

      case 'double':
      case 'float':
        $value = (float)$value;
        break;

      case 'string':
        $value = (string)$value;
        break;

      case 'object':
        $value = (object)$value;
        break;

      case 'array':
        $value = (array)$value;
        break;

      case 'unset':
      case 'null':
        $value = NULL;
        break;
    }

    return $value;
  }
}
