<?php
namespace Sutra\Component\String;

use Sutra\Component\String\Exception\ProgrammerException;

/**
 * Provides an object-oriented interface to strings.
 *
 * @author Andrew Udvare <audvare@gmail.com>
 * @author Charles S <hopelesscode@gmail.com>
 *
 * @replaces sString
 */
class String implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * UTF-8 helper class.
     *
     * @var Utf8HelperInterface
     */
    protected static $helper;

    /**
     * The string.
     *
     * @var string
     */
    protected $string;

    /**
     * Constructor.
     *
     * @param string $string The string to handle.
     *
     * @throws ProgrammerException If string length is zero.
     */
    public function __construct($string)
    {
        static::getUtf8Helper();

        if (strlen($string) === 0) {
            throw new ProgrammerException('String argument must be non-zero-length string');
        }

        $this->string = (string) $string;
    }

    /**
     * Replaces matching parts of the string.
     *
     * @param mixed   $search        The string or array to search for.
     * @param mixed   $replace       The string or array of replacements.
     * @param boolean $caseSensitive Determines to check for case sensitive
     *     strings.
     *
     * @return String The replaced string.
     *
     * @see Utf8HelperInterface#replace()
     *
     * @replaces ::replace
     */
    public function replace($search, $replace, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return new static(static::$helper->replace($this->string, $search, $replace));
        }

        return new static(static::$helper->caseInsensitiveReplace($this->string, $search, $replace));
    }

    /**
     * Checks if the offset exists.
     *
     * @param integer $offset Offset.
     *
     * @return boolean If the offset exists.
     *
     * @throws ProgrammerException If the offset is not an integer.
     */
    public function offsetExists($offset)
    {
        if (!is_numeric($offset) || is_float($offset)) {
            throw new ProgrammerException('Offsets can only be integer. Given: "%s"', $offset);
        }

        $offset = (int) $offset;

        return isset($this->string[$offset]);
    }

    /**
     * Gets the value at a specific offset.
     *
     * @param integer $offset Offset.
     *
     * @return mixed The value or null.
     *
     * @throws ProgrammerException If the offset is not an integer.
     */
    public function offsetGet($offset)
    {
        if (!is_numeric($offset) || is_float($offset)) {
            throw new ProgrammerException('Offsets can only be integer. Given: "%s"', $offset);
        }

        $offset = (int) $offset;

        return isset($this->string[$offset]) ? $this->string[$offset] : null;
    }

    /**
     * Sets the value at an offset. The offset is ignored.
     *
     * @param integer $offset Offset to set to. Ignored.
     * @param string  $value  Character to set.
     *
     * @throws ProgrammerException If offset is not integer or if value length
     *   is not at least 1.
     */
    public function offsetSet($offset, $value)
    {
        if (!is_numeric($offset) || is_float($offset)) {
            throw new ProgrammerException('Offsets can only be integer. Given: "%s"', $offset);
        }
        if (static::$helper->length($value) !== 1) {
            throw new ProgrammerException('The value length may not be greater than 1');
        }
        $offset = (int) $offset;
        $this->string[$offset] = $value;
    }

    /**
     * Unsets the value at an offset.
     *
     * @param integer $offset Offset.
     *
     * @throws ProgrammerException If the offset is not an integer.
     */
    public function offsetUnset($offset)
    {
        if (!is_numeric($offset) || is_float($offset)) {
            throw new ProgrammerException('Offsets can only be integer. Given: "%s"', $offset);
        }

        $arr = static::$helper->split($this->string);
        unset($arr[$offset]);
        $this->string = join('', $arr);
    }

    /**
     * Get the character within the string specified by numerical index.
     *
     * @param integer $index Index to use.
     *
     * @return String|null The character at $index or `null`.
     */
    public function charAt($index)
    {
        if ($index < 0) {
            return;
        }

        if ($index > ($this->getLength() - 1)) {
            return;
        }

        return new static($this->string[$index]);
    }

    /**
     * Get the character code at an index.
     *
     * @param integer $index Index to use.
     *
     * @return mixed If the index is not usable, null is returned. Otherwise, the
     *     character number (int) is returned.
     */
    public function charCodeAt($index)
    {
        if ($index < 0 || $index > $this->length) {
            return;
        }

        return ord($this->string[$index]);
    }

    /**
     * Get the string double-quoted.
     *
     * @return String The string, double-quoted.
     */
    public function quote()
    {
        return new static('"' . $this->string . '"');
    }

    /**
     * Explode a string, optionally with a separator.
     *
     * @param string $separator Separator, optional.
     *
     * @return array String as array.
     */
    public function split($separator = null)
    {
        if ($separator === null) {
            return str_split($this->string);
        }

        return static::$helper->split($this->string, $separator);
    }

    /**
     * Replaces substring using regular expression.
     *
     * @param string  $pattern     The regular expression.
     * @param string  $replacement The replacement string.
     * @param integer $limit       The limit.
     *
     * @return String The string with replacements made.
     *
     * @see preg_replace()
     */
    public function replaceRegex($pattern, $replacement, $limit = -1)
    {
        return new static(preg_replace($pattern, $replacement, $this->string, $limit));
    }

    /**
     * Converts the string to a integer.
     *
     * @return integer The integer from the converted string.
     */
    public function toInteger()
    {
        return (int) $this->string;
    }

    /**
     * Converts the string to a array.
     *
     * @return array The array from the converted string.
     */
    public function toArray()
    {
        return str_split($this->string);
    }

    /**
     * Converts the string to a float.
     *
     * @return float The float from the converted string.
     */
    public function toFloat()
    {
        return (float) $this->string;
    }

    /**
     * Encodes the string to base64.
     *
     * @see base64_encode()
     *
     * @return string The string encoded to base64.
     */
    public function toBase64()
    {
        return new static(base64_encode($this->string));
    }

    /**
     * Converts the string to a boolean.
     *
     * @return boolean The boolean from the converted string.
     */
    public function toBoolean()
    {
        $bool = (string) $this->toLowerCase();

        if ($bool === 'true' || $bool === '1') {
            return true;
        }

        return false;
    }

    /**
     * Encodes the string to JSON data.
     *
     * @param integer $options Encoding options.
     *
     * @return string The string encoded to JSON.
     *
     * @api
     *
     * @see json_encode()
     */
    public function toJson($options = null)
    {
        return new static(json_encode($this->string, $options));
    }

    /**
     * Encodes the string to a URI component.
     *
     * @return string The encoded URI.
     *
     * @see urlencode()
     */
    public function toUriComponent()
    {
        return new static(urlencode($this->string));
    }

    /**
     * Encodes the string to a rawURIcomponent.
     *
     * @see rawurlencode()
     *
     * @return string The encoded raw URI.
     */
    public function toRawUriComponent()
    {
        return new static(rawurlencode($this->string));
    }

    /**
     * Convert the string to all lowercase.
     *
     * @return String The string lowercased.
     */
    public function toLowerCase()
    {
        return new static(static::$helper->lower($this->string));
    }

    /**
     * Convert the string to all uppercase.
     *
     * @return String The string uppercased.
     */
    public function toUpperCase()
    {
        return new static(static::$helper->upper($this->string));
    }

    /**
     * Convert the beginning of each word to uppercase.
     *
     * @return String The beginning of each word uppcased.
     */
    public function wordsToUpper()
    {
        return new static(static::$helper->title($this->string));
    }

    /**
     * Converts the first character to uppercase.
     *
     * @return String The first character uppercased.
     */
    public function firstCharToUpper()
    {
        return new static(static::$helper->firstToUpper($this->string));
    }

    /**
     * Subtracts part of the string.
     *
     * @param integer $start  The starting point to extract from.
     * @param integer $length The length to subtract from the string.
     *
     * @return String The substring (new instance of `String`).
     */
    public function substr($start, $length = null)
    {
        return new static(static::$helper->substr($this->string, $start, $length));
    }

    /**
     * Trims whitespaces or defined characters from the beginning of the string.
     *
     * @param string $charlist The characters to trim.
     *
     * @return String The string trimmed.
     */
    public function trimLeft($charlist = null)
    {
        return new static(static::$helper->trimLeft($this->string, $charlist));
    }

    /**
     * Trims whitespaces or defined characters from the full string.
     *
     * @param string $charList The characters to trim.
     *
     * @return String The string trimmed.
     */
    public function trim($charList = null)
    {
        return new static(static::$helper->trim($this->string, $charList));
    }

    /**
     * Trims whitespaces or defined characters from the end of the string.
     *
     * @param string $charList The characters to trim.
     *
     * @return String The string trimmed.
     */
    public function trimRight($charList = null)
    {
        return new static(static::$helper->trimRight($this->string, $charList));
    }

    /**
     * Finds the first position of the search in the string.
     *
     * @param string  $needle The string to search for.
     * @param integer $offset The character position to start searching from.
     *
     * @return integer The character position, or `false`.
     */
    public function indexOf($needle, $offset = 0)
    {
        return static::$helper->indexOf($this->string, $needle, $offset);
    }

    /**
     * Finds the last position of the search value in the string.
     *
     * @param string  $needle The string to search for.
     * @param integer $offset The character position to start searching from.
     *
     * @return integer The character position or `false`.
     */
    public function lastIndexOf($needle, $offset = 0)
    {
        return static::$helper->lastIndexOf($this->string, $needle, $offset);
    }

    /**
     * Reverses the string.
     *
     * @return String The string reversed.
     */
    public function reverse()
    {
        return new static(static::$helper->reverse($this->string));
    }

    /**
     * Wraps the string to the specified width.
     *
     * @param integer $width The width to wrap too.
     * @param string  $break The break to insert.
     * @param boolean $cut   If true we will cut the words to match the width.
     *
     * @return String The string with all lowercase characters to uppercase.
     */
    public function wordWrap($width = 75, $break = "\n", $cut = false)
    {
        return new static(static::$helper->wordwrap($this->string, $width, $break, $cut));
    }

    /**
     * Pads the string to the number of characters specified.
     *
     * @param integer $padLength The character length to pad.
     * @param string  $padStr    The string to pad with.
     *
     * @return String The padded string.
     */
    public function pad($padLength, $padStr = '')
    {
        return new static(static::$helper->pad($this->string, $padLength, $padStr));
    }

    /**
     * Left pads the string to the number of characters specified.
     *
     * @param integer $padLength The character length to pad.
     * @param string  $padStr    The string to pad with.
     *
     * @return String The padded string.
     */
    public function padLeft($padLength, $padStr = '')
    {
        return new static(static::$helper->padLeft($this->string, $padLength, $padStr));
    }

    /**
     * Left pads the string to the number of characters specified.
     *
     * @param integer $padLength The character length to pad.
     * @param string  $padStr    The string to pad with.
     *
     * @return String The padded string.
     */
    public function padRight($padLength, $padStr = '')
    {
        return new static(static::$helper->padRight($this->string, $padLength, $padStr));
    }

    /**
     * Removes any non-UTF-8 characters.
     *
     * @return String The cleaned string.
     */
    public function clean()
    {
        return new static(static::$helper->clean($this->string));
    }

    /**
     * Returns the scalar string.
     *
     * @return string The string defined.
     */
    public function __toString()
    {
        return $this->string;
    }

    /**
     * Gets the length of the string.
     *
     * @return integer The string length.
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * This is only for getting the 'length' attribute, to be similar to
     *     JavaScript.
     *
     * @param string $name Key to get value of. Only `length` is accepted.
     *
     * @return mixed The length of the array or null if the key is invalid.
     */
    public function __get($name)
    {
        if ($name == 'length') {
            return static::$helper->length($this->string);
        }

        return null;
    }

    /**
     * So the object can be used with foreach.
     *
     * @return \ArrayIterator Iterator object.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->toArray());
    }
    /**
     * Returns the length of the string.
     *
     * @return integer The length of the string.
     */
    public function count()
    {
        return $this->length;
    }

    /**
     * Gets `Utf8HelperInterface` instance.
     *
     * Also sets up instance if it has not yet been instantiated for lazy
     *   loading.
     *
     * @return Utf8HelperInterface Utf8Helper instance.
     */
    public static function getUtf8Helper()
    {
        if (!static::$helper) {
            static::$helper = new Utf8Helper();
        }

        return static::$helper;
    }
}

/**
 * Copyright (c) 2012 Charles S <hopelesscode@gmail.com>, others
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
