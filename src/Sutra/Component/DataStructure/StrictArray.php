<?php
namespace Sutra\Component\DataStructure;

use Sutra\Component\DataStructure\Exception\ProgrammerException;
use Sutra\Component\DataStructure\Exception\ValidationException;

// For documentation
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * An object-oriented interface to numerically indexed arrays.
 *
 * @author Andrew Udvare <audvare@gmail.com>
 */
class StrictArray implements \Countable, \ArrayAccess, \IteratorAggregate
{
    /**
     * The data.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Constructor. Accepts multiple arguments or only a single array argument.
     *
     * @param mixed $arg First item of the array.
     */
    public function __construct($arg = null)
    {
        if (is_array($arg)) {
            $this->data = array_values($arg);

            return;
        }

        $this->data = func_get_args();
    }

    /**
     * Gets the data as a regular array.
     *
     * @return array Array of data.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the size of array.
     *
     * @return integer The size of the array.
     */
    public function count()
    {
        return sizeof($this->data);
    }

    /**
     * So the object can be used with foreach.
     *
     * @return ArrayIterator Iterator object.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
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

        return isset($this->data[$offset]);
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

        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * Sets the value at an offset.
     *
     * @param integer $offset Offset to set to.
     * @param mixed   $value  Value to set.
     */
    public function offsetSet($offset, $value)
    {
        if (isset($this->data[$offset])) {
            $this->data[$offset] = $value;
        }
        else {
            $this->data[] = $value;
        }
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

        $offset = (int) $offset;
        unset($this->data[$offset]);
    }

    /**
     * This is only for getting the 'length' attribute, to be similar to
     *     JavaScript.
     *
     * @param string $key Key to get value of. Only 'length' is accepted.
     *
     * @return mixed The length of the array or null if the key is invalid.
     */
    public function __get($key)
    {
        if ($key == 'length') {
            return count($this);
        }

        return null;
    }

    // Mutators
    /**
     * Pops the last element of the array and returns its value.
     *
     * @return mixed The value at the last index.
     */
    public function pop()
    {
        return array_pop($this->data);
    }

    /**
     * Pushes the value into the last position of the array.
     *
     * @param mixed $var Variable to push.
     *
     * @return StrictArray The object to allow method chaining.
     */
    public function push($var)
    {
        $this->data[] = $var;

        return $this;
    }

    /**
     * Fills the array $num times with $value.
     *
     * @param integer $num   Number of times to fill.
     * @param mixed   $value Value to fill with.
     *
     * @return StrictArray The object to allow method chaining.
     */
    public function fill($num, $value)
    {
        for ($i = 0; $i < $num; $i++) {
            $this->data[] = $value;
        }

        return $this;
    }

    /**
     * Shifts the first value off the array and returns it.
     *
     * @return mixed The value at the first index of the array.
     */
    public function shift()
    {
        return array_shift($this->data);
    }

    /**
     * Puts a new element at the beginning of the array.
     *
     * @param mixed $var Variable to unshift.
     *
     * @return StrictArray The object to allow method chaining.
     */
    public function unshift($var)
    {
        array_unshift($this->data, $var);

        return $this;
    }

    /**
     * Merges this array with another array or `StrictArray` object. Accepts
     *   multiple arguments.
     *
     * @param array|StrictArray $array1 Array to shift with.
     *
     * @return StrictArray The object to allow method chaining.
     */
    public function merge($array1)
    {
        $args = func_get_args();

        foreach ($args as $key => $arg) {
            if ($arg instanceof static) {
                $args[$key] = $arg->getData();
            }
        }

        array_unshift($args, $this->data);
        $this->data = array_values(call_user_func_array('array_merge', $args));

        return $this;
    }

    /**
     * Applies a user-defined function to each element of this object.
     *
     * @param callable $func     Callback function. The callback takes two
     *     parameters: the value of the key, and the key second. If the value must
     *     be changed, then it should be specified as a reference.
     * @param mixed    $userData If specified, this will be passed to the
     *   callback as the third parameter.
     *
     * @return StrictArray The object to allow method chaining.
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function walk($func, $userData = null)
    {
        foreach ($this->data as $key => $value) {
            call_user_func_array($func, array(&$value, $key, $userData));
            $this->data[$key] = $value;
        }
        $this->data = array_values($this->data);

        return $this;
    }

    /**
     * Applies a user-defined function to each element of this object. This
     *     method will recurse into deeper arrays. Any key sets will be ignored.
     *
     * @param callable $func     Callback function. The callback takes two
     *     parameters: the value of the key, and the key second. If the value must
     *     be changed, then it should be specified as a reference.
     * @param mixed    $userData If specified, this will be passed to the callback
     *     as the third parameter.
     *
     * @return StrictArray The object to allow method chaining.
     */
    public function walkRecursive($func, $userData = null)
    {
        foreach ($this->data as $key => $value) {
            call_user_func_array($func, array(&$value, $key, $userData));
            static::walkRecursiveCallback($this, $value, $func, $userData);
        }

        return $this;
    }

    /**
     * Sorts the elements of the array.
     *
     * @param integer $flags Flags for sorting.
     *
     * @return StrictArray The object to allow method chaining.
     *
     * @see sort()
     */
    public function sort($flags = SORT_STRING)
    {
        sort($this->data, $flags);

        return $this;
    }

    /**
     * Sorts the elements of the array reversed.
     *
     * @param integer $flags Flags for sorting.
     *
     * @return StrictArray The object to allow method chaining.
     *
     * @see rsort()
     */
    public function reverseSort($flags = SORT_STRING)
    {
        rsort($this->data, $flags);

        return $this;
    }

    // Non-mutators
    /**
     * Recursively converts an array to an array of strings.
     *
     * @param array|StrictArray|object $values Array or array-like object.
     *
     * @return array Array of strings.
     */
    private static function convertToStrings($values)
    {
        foreach ($values as $key => $value) {
            if (static::isArrayLike($value)) {
                $values[$key] = implode(',', static::convertToStrings($value));
            }
            else {
                $values[$key] = (string) $value;
            }
        }

        return $values;
    }

    /**
     * Implements __toString(). Like JavaScript, returns the elements separated
     *     by a comma. Internal arrays and array-like objects are also handled, but
     *     are not separated by any symbol (like JavaScript).
     *
     * @return string
     */
    public function __toString()
    {
        $arr = static::convertToStrings($this->data);

        return implode(',', $arr);
    }

    /**
     * Prints the JSON-encoded array.
     */
    public function printJSON()
    {
        print($this->jsonEncode($this->data));
    }

    /**
     * Returns the JSON-encoded array.
     *
     * @return string JSON string.
     */
    public function toJSON()
    {
        return $this->jsonEncode($this->data);
    }

    /**
     * Searches the array for a given value and returns the corresponding key if
     *     successful. Can return boolean false.
     *
     * @param mixed   $needle Value to search for.
     * @param boolean $strict If the value should be identical.
     *
     * @return boolean|string If the key is found, a string will be returned.
     *     Otherwise boolean false will be returned.
     */
    public function search($needle, $strict = false)
    {
        return array_search($needle, $this->data, $strict);
    }

    /**
     * Picks one or more random keys.
     *
     * @param integer $numReq Number of items to get.
     *
     * @return array Array of numeric keys.
     */
    public function rand($numReq = 1)
    {
        $ret = @array_rand($this->data, $numReq);
        if (!is_array($ret)) {
            $ret = array($ret);
        }

        return $ret;
    }

    /**
     * Compares this object's data with other arrays. Accepts multiple
     *   arguments.
     *
     * @param array|StrictArray|object $array1 Array or array-like object.
     *
     * @return sArray Array containing all the entries from $array1 that are
     *     not present in any of the other arrays.
     */
    public function diff($array1)
    {
        $args = func_get_args();

        foreach ($args as $key => $arg) {
            if ($arg instanceof static) {
                $args[$key] = $arg->getData();
            }
        }

        array_unshift($args, $this->data);

        return new static(call_user_func_array('array_diff', $args));
    }

    /**
     * Returns a copy of this array reversed.
     *
     * @return sArray The array, reversed.
     */
    public function reverse()
    {
        return new static(array_reverse($this->data));
    }

    /**
     * Extract a slice of the array.
     *
     * @param integer $offset If offset is non-negative, the sequence will start
     *     at that offset in the array. If offset is negative, the sequence will
     *     start that far from the end of the array.
     * @param integer $length If length is given and is positive, then the
     *     sequence will have up to that many elements in it. If the array is
     *     shorter than the length, then only the available array elements will be
     *     present. If length is given and is negative then the sequence will stop
     *     that many elements from the end of the array. If it is omitted, then the
     *     sequence will have everything from offset up until the end of the array.
     *
     * @return StrictArray Array slice.
     */
    public function slice($offset, $length = null)
    {
        return new static(array_slice($this->data, $offset, $length));
    }

    /**
     * Calls a callback on each item in the object. If the callback returns true,
     *     then the value will be returned in the resulting sArray instance of this
     *     method.
     *
     * @param callable $cb Callback to call on each key.
     *
     * @return sArray New filtered sArray.
     *
     * @see array_filter()
     */
    public function filter($cb)
    {
        return new static(array_filter($this->data, $cb));
    }

    /**
     * Applies the callback to the elements of this array.
     *
     * @param callable $cb Callback to use.
     *
     * @return StrictArray New instance.
     *
     * @see array_map()
     */
    public function map($cb)
    {
        return new static(array_map($cb, $this->data));
    }

    /**
     * Pad array to the specified length with a value.
     *
     * @param integer $padSize  New size of the array.
     * @param mixed   $padValue Value to pad if the array size is less than
     *   `$padSize`.
     *
     * @return StrictArray Returns a copy of the input padded to size specified by
     *     `$padSize` with value `$padValue`.
     *
     * @see array_pad()
     */
    public function pad($padSize, $padValue)
    {
        return new static(array_pad($this->data, $padSize, $padValue));
    }

    /**
     * Removes duplicate values from the array.
     *
     * @param integer $sort_flags One of the `SORT_*` constants.
     *
     * @return StrictArray Copy of this array with duplicate values removed.
     *
     * @see array_unique
     */
    public function unique($sort_flags = SORT_STRING)
    {
        return new static(array_unique($this->data, $sort_flags));
    }

    /**
     * Returns the values of this array. Alias to getData().
     *
     * @return array Array of data.
     */
    public function values()
    {
        return $this->data;
    }

    /**
     * Returns an sObject instance with the indexes of this array being the
     *     values and the values becoming the keys.
     *
     * @return Dictionary Dictionary instance.
     */
    public function flip()
    {
        return new Dictionary(array_flip($this->data));
    }

    /**
     * Checks if value is an array or is array-like (implementing the correct
     *     interfaces).
     *
     * To be array-like, a class must implement both `ArrayAccess` and
     *     `IteratorAggregate`.
     *
     * @param mixed $value Value to check.
     *
     * @return boolean If the value is an array or is array-like.
     */
    private static function isArrayLike($value)
    {
        if (is_array($value)) {
            return true;
        }

        if ($value instanceof static) {
            return true;
        }

        if (is_object($value)) {
            $reflection = new \ReflectionClass($value);
            if ($reflection->implementsInterface('IteratorAggregate') &&
                $reflection->implementsInterface('ArrayAccess')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Callback used with walkRecursive.
     *
     * @param StrictArray $instance Object instance.
     * @param mixed       &$array   Mixed variable, checked if is array-like.
     * @param callable    $func     Function to call on each item.
     * @param mixed       $userData User data to add as third argument to callback.
     */
    private static function walkRecursiveCallback(StrictArray $instance, &$array, $func, $userData = null)
    {
        if (!static::isArrayLike($array)) {
            return;
        }

        foreach ($array as $key => $value) {
            call_user_func_array($func, array(&$value, $key, $userData));
            if (static::isArrayLike($value)) {
                static::walkRecursiveCallback($instance, $value, $func, $userData);
            }
        }
    }

    /**
     * JSON encodes according to RFC 4627.
     *
     * @param mixed $data Data to encode.
     *
     * @return string The data in JSON format.
     *
     * @see JsonResponse#setData()
     */
    private function jsonEncode($data)
    {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }
}

/**
 * Copyright (c) 2013 Andrew Udvare <audvare@gmail.com>
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
