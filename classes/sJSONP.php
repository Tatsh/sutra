<?php
/**
 * Extension to Flourish fJSON class to provide methods for JSONP support.
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
class sJSONP extends fJSON {
  /**
   * JavaScript reserved words, as per the ECMA 3 standard.
   *
   * @var array
   */
  private static $reserved_words = array(
    'break',
    'do',
    'instanceof',
    'typeof',
    'case',
    'else',
    'new',
    'var',
    'catch',
    'finally',
    'return',
    'void',
    'continue',
    'for',
    'switch',
    'while',
    'debugger',
    'function',
    'this',
    'with',
    'default',
    'if',
    'throw',
    'delete',
    'in',
    'try',
    'class',
    'enum',
    'extends',
    'super',
    'const',
    'export',
    'import',
    'implements',
    'let',
    'private',
    'public',
    'yield',
    'interface',
    'package',
    'protected',
    'static',
    'null',
    'true',
    'false',
  );

  /**
   * The JavaScript identifier syntax regular expression.
   *
   * @var string
   * @credit http://www.geekality.net/2010/06/27/php-how-to-easily-provide-json-and-jsonp/
   */
  private static $identifier_syntax = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

  /**
   * Overrides encode method to wrap around the callback received.
   *
   * @throws fValidationException If the callback is invalid.
   *
   * @param mixed $data Data to encode.
   * @param string $callback Optional. If not passed, will be retrieved from GET
   *   paramater 'callback'. If no such parameter is found, 'fn' will be used.
   * @return string Encoded JSONP data.
   */
  public static function encode($data, $callback = NULL) {
    if (is_null($callback)) {
      $callback = fRequest::get('callback', 'string', 'fn');
    }

    if (!self::isValidCallback($callback)) {
      throw new fValidationException('Invalid callback "%s" passed.', $callback);
    }

    return $callback.'('.parent::encode($data).');';
  }

  /**
   * Validate a callback name is not a reserved word in JavaScript and does not have
   *   invalid characters.
   *
   * @param string $subject The callback name.
   * @return boolean TRUE if the callback can be used, FALSE otherwise.
   */
  private static function isValidCallback($subject) {
    return preg_match(self::$identifier_syntax, $subject) && !in_array(fUTF8::lower($subject), self::$reserved_words);
  }

  // @codeCoverageIgnoreStart
  /**
   * Overrides sendHeader to send a text/javascript response instead.
   *
   * @return void
   */
  public static function sendHeader() {
    header('Content-Type: text/javascript; charset=utf-8');
  }
  // @codeCoverageIgnoreEnd

  // @codeCoverageIgnoreStart
  /**
   * Force use as a static class.
   *
   * @return sJSONP
   */
  private function __construct() {}
  // @codeCoverageIgnoreEnd
}
