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
   * Implements magic method __callStatic().
   *
   * Methods:
   *   - getNameOfSomething($cast_to = NULL, $default_value = NULL) - returns
   *       the value for name_of_something
   *   - setNameOfSomething($value, $ttl = 3600) - returns void
   *
   * @param string $method Method name invoked.
   * @param array $arguments Arguments passed in an array.
   * @return mixed If calling get, the value of the configuration, otherwise void.
   */
  public static function __callStatic($method, $arguments) {
    self::initialize();

    $subject = fGrammar::underscorize(substr($method, 3));
    if (strlen($method) > 3) {
      if (substr($method, 0, 3) === 'get') {
        $cast_to = isset($arguments[0]) ? $arguments[1] : NULL;
        $default = isset($arguments[1]) ? $arguments[1] : NULL;
        return self::getValue($subject, $cast_to, $default);
      }
      else if (substr($method, 0, 3) === 'set' && isset($arguments[0])) {
        self::setValue($subject, $arguments[0], isset($arguments[1]) ? (int)$arguments[1] : self::DEFAULT_TTL);
      }
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

      default:
        throw new fProgrammerException('Invalid type %s specified for casting.', $to);
    }

    return $value;
  }
}
