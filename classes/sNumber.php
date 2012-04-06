<?php
/**
 * Provides getting ordinal numbers as an extension to fNumber.
 *
 * @copyright Copyright (c) 2012 bne1.
 * @author Andrew Udvare [au] <andrew@bne1.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.sutralib.com/
 *
 * @version 1.2
 */
class sNumber extends fNumber {
  /**
   * The locale for the class. Defaults to US English.
   *
   * @var string
   */
  private static $locale = 'en_US';

  /**
   * The fallback to use if the locale has no callback for the locale in use.
   *   Defaults to US English.
   *
   * @var string
   */
  private static $fallback_locale = 'en_US';

  /**
   * Callbacks for other languages.
   *
   * @var array
   */
  private static $callbacks = array(
    'en_AU' => array( // Australia
      'ordinal' => 'sNumber::ordinalNumberPrefixedCallback',
      'ordinalSuffix' => 'sNumber::ordinalSuffix',
    ),
    'en_CA' => array( // Canada
      'ordinal' => 'sNumber::ordinalNumberPrefixedCallback',
      'ordinalSuffix' => 'sNumber::ordinalSuffix',
    ),
    'en_GB' => array( // United Kingdom
      'ordinal' => 'sNumber::ordinalNumberPrefixedCallback',
      'ordinalSuffix' => 'sNumber::ordinalSuffix',
    ),
    'en_IE' => array( // Ireland
      'ordinal' => 'sNumber::ordinalNumberPrefixedCallback',
      'ordinalSuffix' => 'sNumber::ordinalSuffix',
    ),
    'en_IN' => array( // India
      'ordinal' => 'sNumber::ordinalNumberPrefixedCallback',
      'ordinalSuffix' => 'sNumber::ordinalSuffix',
    ),
    'en_MT' => array( // Malta
      'ordinal' => 'sNumber::ordinalNumberPrefixedCallback',
      'ordinalSuffix' => 'sNumber::ordinalSuffix',
    ),
    'en_NZ' => array( // New Zealand
      'ordinal' => 'sNumber::ordinalNumberPrefixedCallback',
      'ordinalSuffix' => 'sNumber::ordinalSuffix',
    ),
    'en_PH' => array( // Philippines
      'ordinal' => 'sNumber::ordinalNumberPrefixedCallback',
      'ordinalSuffix' => 'sNumber::ordinalSuffix',
    ),
    'en_SG' => array( // Singapore
      'ordinal' => 'sNumber::ordinalNumberPrefixedCallback',
      'ordinalSuffix' => 'sNumber::ordinalSuffix',
    ),
    'en_US' => array( // United States
      'ordinal' => 'sNumber::ordinalNumberPrefixedCallback',
      'ordinalSuffix' => 'sNumber::ordinalSuffix',
    ),
    'en_ZA' => array( // South Africa
      'ordinal' => 'sNumber::ordinalNumberPrefixedCallback',
      'ordinalSuffix' => 'sNumber::ordinalSuffix',
    ),
  );

  /**
   * Adds an array of callbacks with the default methods in this class for a
   *   specified locale.
   *
   * @param string $locale Locale name, such as fr_FR.
   * @return void
   */
  private static function addDefaultCallbacks($locale) {
    if (!isset(self::$callbacks[$locale])) {
      self::$callbacks[$locale] = array(
        'oridinal' => __CLASS__.'::ordinal',
        'ordinalSuffix' => __CLASS__.'::ordinalSuffix',
      );
    }
  }

  /**
   * Add a callback that will be used in place of the ones here.
   *
   * For use with different languages and locales.
   *
   * All callbacks must receive one value, an integer, and return a string.
   *
   * @throws fProgrammerException If the method name is invalid.
   *
   * @param string $locale The locale name. Should be a standard locale
   *   name such as en_GB, fr_FR, etc.
   * @param string $method_name Method name in this class to override. One of:
   *   ordinal, ordinalSuffix. The instance methods will also use this
   *   callback.
   * @param string|array $callback Callback to use.
   * @return void
   *
   * @todo Validate the locale against a list of locales.
   */
  public static function addCallback($locale, $method_name, $callback) {
    self::addDefaultCallbacks($locale);

    $valid_methods = array(
      'ordinal',
      'ordinalSuffix',
    );

    if (!in_array($method_name, $valid_methods)) {
      throw new fProgrammerException('Invalid method name "%s" specified. Must be one of: %s.', $method_name, implode(', ', $valid_methods));
    }

    self::$callbacks[$locale][$method_name] = $callback;
  }

  /**
   * Remove a locale's set of callbacks.
   *
   * @param string $locale_name Locale name.
   * @return void
   */
  public static function removeLocale($locale_name) {
    if (isset(self::$callbacks[$locale_name])) {
      unset(self::$callbacks[$locale_name]);
    }
  }

  /**
   * Set the current locale in use for this class. If no callbacks yet exist,
   *   the defaults in this class will be assigned.
   *
   * @param string $locale The language name. Should be a standard locale
   *   name such as en_GB, fr_FR, etc.
   * @return void
   *
   * @todo Validate the locale against a list of locales.
   */
  public static function setLocale($locale) {
    if(!isset(self::$callbacks[$locale])) {
      self::addDefaultCallbacks($locale);
    }
    self::$locale = $locale;
  }

  /**
   * Set the fallback locale if the current locale set does not have a
   *   callback for the method requested. If no callbacks yet exist,
   *   the defaults in this class will be assigned.
   *
   * @param string $locale Locale name.
   * @return void
   *
   * @todo Validate the locale against a list of locales.
   */
  public static function setFallbackLocale($locale) {
    if(!isset(self::$callbacks[$locale])) {
      self::addDefaultCallbacks($locale);
    }
    self::$fallback_locale = $locale;
  }

  /**
   * Get the correct callback based on the locale and fallback locale set in
   *   the class.
   *
   * @param string $fn Method name to check for.
   * @return string Callback name.
   */
  private static function getValidCallback($fn) {
    $locale = self::$locale;
    $fallback = self::$fallback_locale;

    if (!isset(self::$callbacks[self::$locale])) {
      $locale = 'en_US';
    }
    if (!isset(self::$callbacks[self::$fallback_locale])) {
      $fallback = 'en_US';
    }

    if (!isset(self::$callbacks[$locale][$fn])) {
      return self::$callbacks[$fallback][$fn];
    }

    return self::$callbacks[$locale][$fn];
  }

  /**
   * Format a number to be ordinal.
   *
   * @param int $value Number to use.
   * @return string Number with proper English suffix.
   */
  public static function ordinal($value) {
    return fCore::call(self::getValidCallback(__FUNCTION__), array($value));
  }

  /**
   * Callback for English ordinal numbers (where numbers come before the
   *   ordinal keyword).
   *
   * @internal For internal use.
   * @access private
   *
   * @param integer $value
   * @return string The value, formatted.
   */
  public static function ordinalNumberPrefixedCallback($value) {
    $cb = self::getValidCallback('ordinalSuffix');
    return $value.fCore::call($cb, array($value));
  }

  /**
   * Get the correct oridinal suffix for a number.
   *
   * @param integer $value Number to use.
   * @return string Correct suffix.
   */
  public static function ordinalSuffix($value) {
    $cb = self::getValidCallback('ordinalSuffix');
    if ($cb != __CLASS__.'::'.__FUNCTION__) {
      return fCore::call($cb, array($value));
    }

    $suffix = 'th';

    if (!(substr($value, -2, 2) == 11 ||
          substr($value, -2, 2) == 12 ||
          substr($value, -2, 2) == 13)) {
      if (substr($value, -1, 1) == 1) {
        $suffix = 'st';
      }
      else if (substr($value, -1, 1) == 2) {
        $suffix = 'nd';
      }
      else if (substr($value, -1, 1) == 3) {
        $suffix = 'rd';
      }
    }

    return $suffix;
  }

  /**
   * Checks if a number is equal to its int-casted counterpart.
   *
   * @param mixed $value Value to check.
   * @return boolean If the int-casted value is the same.
   */
  public static function isEqualToIntCast($value) {
    if (!is_numeric($value)) {
      return FALSE;
    }

    return $value == intval($value);
  }

  /**
   * Get the correct suffix for the current number.
   *
   * @return string Correct English suffix.
   */
  public function getOrdinalSuffix() {
    return self::ordinalSuffix((int)$this->__toString());
  }

  /**
   * Get the number formatted with the oridinal suffix.
   *
   * @param boolean $remove_zero_fraction If TRUE and all digits after the
   *   decimal place are 0, the decimal place and all zeros are removed.
   * @return string Number with proper English suffix.
   */
  public function formatWithOrdinalSuffix($remove_zero_fraction = FALSE) {
    $formatted = $this->format($remove_zero_fraction);
    return $formatted.$this->getOrdinalSuffix();
  }
}
