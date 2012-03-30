<?php
/**
 * Extends fGrammar.
 *
 * @package Sutra
 */
class sGrammar extends fGrammar {
  /**
   * Cache of strings that have been run through sGrammar::dashize().
   *
   * @var array
   */
  private static $dashize_cache = array();

  /**
   * Exceptions for sGrammar::dashize().
   *
   * @var array
   */
  private static $dashize_rules = array();

  /**
   * Add an exception string for sGrammar::dashize().
   *
   * @param string $original Original string.
   * @param string $returnString The string to return in case this string is passed to
   *   sGrammar::dashize().
   * @return void
   * @see sGrammar::removeDashizeRule()
   */
  public static function addDashizeRule($original, $returnString) {
    if (!strlen($returnString) || !strlen($original)) {
      throw new fProgrammerException(
        "An empty string was passed to %s",
        __CLASS__ . '::dashize()'
        );
    }

    self::$dashize_rules[$original] = $returnString;
  }

  /**
   * Removes a rule used by sGrammar::dashize().
   *
   * @param string $original Original string that would be processed.
   * @return void
   * @see sGrammar::addDashizeRule()
   */
  public static function removeDashizeRule($original) {
    if (!strlen($original)) {
      throw new fProgrammerException(
        "An empty string was passed to %s",
        __CLASS__ . '::removeDashizeRule()'
        );
    }

    if (isset(self::$dashize_rules[$original])) {
      unset(self::$dashize_rules[$original]);
    }
  }

  /**
   * Converts an underscore_notation or camelCase notation to dash-notation.
   *
   * @param string $string String to convert.
   * @return string Converted string.
   * @see sGrammar::addDashizeRule()
   */
  public static function dashize($string) {
    if (!strlen($string)) {
      throw new fProgrammerException(
        "An empty string was passed to %s",
        __CLASS__ . '::dashize()'
      );
    }

    if (isset(self::$dashize_cache[$string])) {
      return self::$dashize_cache[$string];
    }

    $original = $string;
    $string = strtolower($string[0]) . substr($string, 1);

    // Handle custom rules
    if (isset(self::$dashize_rules[$string])) {
      $string = self::$dashize_rules[$string];
    }
    else if (strpos($string, '-') !== FALSE && strtolower($string) == $string) {}
    else if (strpos($string, '_') !== FALSE && strtolower($string) == $string) {
      $string = str_replace('_', '-', $string);
    }
    else if (strpos($string, ' ') !== FALSE) {
      $string = strtolower(preg_replace('#\s+#', '_', $string));
    }
    else {
      do {
        $old_string = $string;
        $string = preg_replace('/([a-zA-Z])([0-9])/', '\1-\2', $string);
        $string = preg_replace('/([a-z0-9A-Z])([A-Z])/', '\1-\2', $string);
      } while ($old_string != $string);

      $string = strtolower($string);
    }

    self::$dashize_cache[$original] = $string;

    return $string;
  }
}
