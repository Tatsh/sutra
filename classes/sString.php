<?php
/**
 * Provides object-oriented interface to strings.
 * 
 * @copyright Copyright (c) 2012 Charles S, others
 * @author Charles S [cs] <xxstonerariesxx@gmail.com>
 * @author Andrew Udvare [au] <andrew@bne1.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * 
 * @package Sutra
 * @link https://github.com/tatsh/sutra
 * 
 * @version 1.3
 */
class sString implements ArrayAccess, Countable, IteratorAggregate {
  /**
   * The callback to encode into base64.
   * 
   * @var string
   */
  const ENCODING_BASE64 = 'base64_encode';

  /**
   * The callback to encode into JSON.
   * 
   * @var string
   */
  const ENCODING_JSON = 'fJSON::encoding';

  /**
   * The callback to encode into URI Component.
   * 
   * @var string
   */
  const ENCODING_URL = 'urlencode';

  /**
   * The callback to encode into Raw URI Component.
   * 
   * @var string
   */
  const ENCODING_RAWURL = 'rawurlencode';

  /**
   * The string.
   * 
   * @var string
   */
  private $string;

  /**
   * Constructor.
   * 
   * @param string $string The string to manipulate.
   * @return sString
   */
  public function __construct($string) {
    if(fUTF8::len($string) === 0) {
      throw new fProgrammerException('String argument must be non-zero-length string.');
    }
    $this->string = (string )$string;
  }

  /**
   * Replaces matching parts of the string.
   * 
   * @see fUTF8::replace()
   * 
   * @param mixed The string or array to search for.
   * @param mixed The string or array of replacements.
   * @param boolean $case_sensitive Determines to check for case sensitive
   *   strings
   * @return string The replaced string.
   */
  public function replace($search, $replace,$case_sensitive = TRUE) {
    if($case_sensitive){
        return fUTF8::replace($this->string, $search, $replace);
    } 
    return fUTF8::ireplace($this->string,$search,$replace);
  }
  /**
   * Checks if the offset exists.
   *
   * @internal
   *
   * @throws fProgrammerException If the offset is not an integer.
   *
   * @param integer $offset Offset.
   * @return boolean If the offset exists.
   */
  public function offsetExists($offset) {
    if (!is_numeric($offset) || is_float($offset)) {
      throw new fProgrammerException('Offsets can only be integer. Given: "%s"', $offset);
    }

    $offset = (int)$offset;
    return isset($this->string[$offset]);
  }

  /**
   * Gets the value at a specific offset.
   *
   * @internal
   *
   * @throws fProgrammerException If the offset is not an integer.
   *
   * @param integer $offset Offset.
   * @return mixed The value or NULL.
   */
  public function offsetGet($offset) {
    if (!is_numeric($offset) || is_float($offset)) {
      throw new fProgrammerException('Offsets can only be integer. Given: "%s"', $offset);
    }

    $offset = (int)$offset;
    return isset($this->string[$offset]) ? $this->string[$offset] : NULL;
  }

  /**
   * Sets the value at an offset. The offset is ignored.
   *
   * @internal
   *
   * @param integer $offset Offset to set to. Ignored.
   * @param mixed $value Value to set.
   * @return void
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function offsetSet($offset, $value) {
    if (!is_numeric($offset) || is_float($offset)) {
      throw new fProgrammerException('Offsets can only be integer. Given: "%s"', $offset);
    }
    if(fUTF8::len($value) !== 1){
      throw new fProgrammerException('The value may not be greater than 1');
    }
    $offset = (int)$offset;
    $this->string[$offset] = $value;
  }

  /**
   * Unsets the value at an offset.
   *
   * @internal
   *
   * @throws fProgrammerException If the offset is not an integer.
   *
   * @param integer $offset Offset.
   * @return void
   */
  public function offsetUnset($offset) {
    if (!is_numeric($offset) || is_float($offset)) {
      throw new fProgrammerException('Offsets can only be integer. Given: "%s"', $offset);
    }

    $offset = (int)$offset;
    unset($this->string[$offset]);
  }
  
  /**
   * Get the character within the string specified by numerical index.
   * 
   * @param integer $index Index to use.
   * @return integer The character at index or an empty string.
   */
  public function charAt($index) {
    if($index < 0) {
      return '';
    }
    if($index > ($this->getLength() - 1)) {
      return '';
    }
    return $this->string[$index];
  }

  /**
   * Get the character code at an index.
   * 
   * @param integer $index Index to use.
   * @return mixed If the index is not usable, NULL is returned. Otherwise, the character number (int) is returned.
   */
  public function charCodeAt($index) {
    if($index < 0 || $index > $this->getLength()) {
      return NULL;
    }

    $arr = str_split($this->string);
    return intval(ord($arr[$index]));
  }
  
  /**
   * Get the string double-quoted.
   * 
   * @return string The string, double-quoted.
   */
  public function quote() {
    return '"' . $this->string . '"';
  }
  
  /**
   * Get a substring.
   * 
   * @param integer $begin Where to begin.
   * @param integer $end Where to end, optional. If not passed, end is the strlen() return value of the string.
   * @return string Substring.
   */
  public function slice($begin, $end = NULL) {
    if(is_null($end)) {
      $end = $this->getLength() - 1;
    } 
    else {
      $end = intval($end);
    }
    return $this->substr($begin,$end,$this->string);
  }
  
  /**
   * Explode a string, optionally with a separator.
   * 
   * @param string $separator Separator, optional. If not specified, separator will be '' (empty string).
   * @return array String as array, or parts.
   */
  public function split($separator = NULL) {
    if(is_null($separator)) {
      return str_split($this->string);
    }
    return fUTF8::explode($separator,$this->string);
  }

  /**
   * Replaces the regular expression.
   * 
   * @see preg_replace()
   * 
   * @param string $pattern The regular expression.
   * @param string $replacement The replacement string.
   * @param integer $limit The limit.
   * @return string The string with replacements made.
   */
  public function replaceRegex($pattern, $replacement, $limit = -1) {
    return preg_replace($pattern, $replacement, $this->string, $limit);
  }
  
  /**
   * Converts the string to time.
   * 
   * @see fTime::__construct()
   * @return string The string converted to time.
   */
  public function toTime() {
    return new fTime($this->string);
  }

  /**
   * Converts the string to a timestamp.
   * 
   * @see  sTimestamp::__construct()
   * @return string The string converted to a timestamp.
   */
  public function toTimeStamp() {
    return new sTimeStamp($this->string);
  }

  /**
   * Converts the string to a date.
   * 
   * @see fDate::__construct()
   * @return string The string converted to a date.
   */
  public function toDate() {
    return new fDate($this->string);
  }

  /**
   * Converts the string to a integer.
   * 
   * @return integer The integer from the converted string.
   */
  public function toInt() {
    return (int)$this->string;
  }

  /**
   * Converts the string to a fNumber object.
   * 
   * @see sNumber::__construct()
   * @return integer The integer from the converted string.
   */
  public function toNumber() {
    return new sNumber($this->string);
  }

  /**
   * Converts the string to a array.
   * 
   * @return array The array from the converted string.
   */
  public function toArray() {
    return str_split($this->string);
  }

  /**
   * Converts the string to a float.
   * 
   * @return float The float from the converted string.
   */
  public function toFloat() {
    return (float)$this->string;
  }

  /**
   * Encodes the string to base64.
   * 
   * @see base64_encode()
   * @return string The string encoded to base64.
   */
  public function toBase64() {
    return $this->encode(self::ENCODING_BASE64);
  }

  /**
   * Converts our string to a boolean.
   * 
   * @return boolean The boolean from the converted string.
   */
  public function toBoolean() {
    $bool = $this->toLowerCase();
    if($bool === 'true' || $bool === '1'){
        return TRUE;
    }
    return FALSE;
  }

  /**
   * Encodes the string to JSON data.
   * 
   * @see fJSON::encode()
   * @return string The string encoded to JSON.
   */
  public function toJSON() {
    return $this->encode(self::ENCODING_JSON);
  }

  /**
   * Encodes the string to a uri component.
   * 
   * @see urlencode()
   * @return string The encoded url.
   */
  public function toURIComponent() {
    return $this->encode(self::ENCODING_URL);
  }

  /**
   * Encodes the string to a rawURIcomponent.
   * 
   * @see rawurlencode()
   * @return string The encoded raw url.
   */
  public function toRawURIComponent() {
    return $this->encode(self::ENCODING_RAWURL);
  }

  /**
   * Convert the string to all lowercase.
   * 
   * @see fUTF8::lower()
   * @return string The string lowercased.
   */
  public function toLowerCase() {
    return fUTF8::lower($this->string);
  }

  /**
   * Convert the string to all uppercase.
   * 
   * @see fUTF8::upper()
   * @return string The string uppercased.
   */
  public function toUpperCase() {
    return fUTF8::upper($this->string);
  }

  /**
   * Convert the beginning of each word to uppercase.
   * 
   * @see fUTF8::ucwords()
   * @return string The beginning of each word uppcased.
   */
  public function wordsToUpper() {
    return fUTF8::ucwords($this->string);
  }

  /**
   * Converts the first character to uppercase.
   * 
   * @see fUTF8::ucfirst()
   * @return string The first character uppercased.
   */
  public function firstCharToUpper() {
    return fUTF8::ucfirst($this->string);
  }

  /**
   * Subtracts part of the string.
   * 
   * @see fUTF8::sub()
   * 
   * @param integer $start The starting point to extract from.
   * @param integer $length The length to subtract from the string.
   * @return string The subtracted string.
   */
  public function substr($start, $length = NULL) {
    return fUTF8::sub($this->string, $start, $length);
  }
  
  /**
   * Trims whitespaces or defined characters from the beginning of the string.
   * 
   * @see fUTF8::ltrim()
   * 
   * @param string $charlist The characters to trim.
   * @return The string trimmed.
   */
  public function trimLeft($charlist = NULL) {
    return fUTF8::ltrim($this->string, $charlist);
  }

  /**
   * Trims whitespaces or defined characters from the full string.
   * 
   * @see fUTF8::trim()
   * 
   * @param string $charlist The characters to trim.
   * @return string The string trimmed.
   */
  public function trim($charlist = NULL) {
    return fUTF8::trim($this->string, $charlist);
  }

  /**
   * Trims whitespaces or defined characters from the end of the string.
   * 
   * @see fUTF8::rtrim()
   * 
   * @param string $charlist The characters to trim.
   * @return string The string trimmed.
   */
  public function trimRight($charlist = NULL) {
    return fUTF8::rtrim($this->string, $charlist);
  }

  /**
   * Finds the first position of the search in the string.
   * 
   * @see fUTF8::pos()
   * 
   * @param string $needle The string to search for.
   * @param integer $offset The character position to start searching from.
   * @return integer The character position.
   */
  public function indexOf($needle, $offset = 0) {
    $pos = fUTF8::pos($this->string, $needle, $offset);
    if($pos === false) {
      return -1;
    }
    return $pos;
  }

  /**
   * Finds the last position of the search value in the string.
   * 
   * @see fUTF8::rpos()
   * 
   * @param string $needle The string to search for.
   * @param integer $offset The character position to start searching from.
   * @return integer The character position.
   */
  public function lastIndexOf($needle, $offset = 0) {
    $pos = fUTF8::rpos($this->string, $needle, $offset);
    if($pos === false) {
      return -1;
    }
    return $pos;
  }

  /**
   * Reverses the string.
   * 
   * @see fUTF8::rev()
   * @return string The string reversed.
   */
  public function reverse() {
    return fUTF8::rev($this->string);
  }

  /**
   * Wraps the string to the specified width.
   * 
   * @see fUTF8::wordwrap()
   * 
   * @param integer $width The width to wrap too.
   * @param string $break The break to insert.
   * @param boolean $cut If TRUE we will cut the words to match the width.
   * @return string The string with all lowercase characters to uppercase.
   */
  public function wordWrap($width = 75, $break = '', $cut = FALSE) {
    return fUTF8::wordwrap($this->string, $width, $break, $cut);
  }

  /**
   * Pads the string to the number of characters specified.
   * 
   * @see fUTF8::pad()
   * 
   * @param integer $pad_length The character length to pad.
   * @param string $pad_string The string to pad with our string.
   * @param string $pad_type Types: 'left','right','both'.    
   * @return string The padded string.
   */
  public function pad($pad_length, $pad_string = '', $pad_type = 'right') {
    return fUTF8::pad($this->string, $pad_length, $pad_string, $pad_type);
  }

  /**
   * Removes any non-UTF-8 characters.
   * 
   * @see fUTF8::clean()
   * @return string The cleaned string.
   */
  public function clean() {
    return fUTF8::clean($this->string);
  }

  /**
   * Returns the string.
   * 
   * @internal
   * 
   * @return string The string defined.
   */
  public function __toString() {
    return $this->string;
  }

  /**
   * Gets the length of the string.
   * 
   * @return integer The string length.
   */
  public function getLength() {
    return $this->length;
  }

  /**
   * This is only for getting the 'length' attribute, to be similar to
   *   JavaScript.
   *
   * @internal
   *
   * @param string $key Key to get value of. Only 'length' is accepted.
   * @return mixed The length of the array or NULL if the key is invalid.
   */
  public function __get($name) {
    if($name == 'length') {
      return count($this);
    }
    return null;
  }

  /**
   * So the object can be used with foreach.
   *
   * @internal
   *
   * @return ArrayIterator Iterator object.
   */
  public function getIterator() {
    return new ArrayIterator($this->toArray());
  }

  /**
   * Sets and executes the encoders.
   * 
   * @see base64_encode()
   * @see fJSON::encode()
   * @see urlencode()
   * @see rawurlencode()
   * 
   * @param string $encoder The encoder that will encode the string.
   * @return string The encoded string.
   */
  private function encode($encoder) {
    return fCore::call($encoder, array($this->string));
  }
}

/**
 * Copyright (c) 2012 Charles S <xxstonerariesxx@gmail.com>, others
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */