<?php
namespace Sutra\Component\DataStructure;

use Sutra\Component\DataStructure\Exception\ProgrammerException;
use Sutra\Component\DataStructure\Exception\ValidationException;

// For documentation
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides an object-oriented interface to associative arrays. This class is
 *     not concerned with the order of the keys.
 *
 * @author Andrew Udvare <andrew@bne1.com>

 * @todo Create sOrderedObject class.
 */
class Dictionary implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * The actual 'array' of data this object manages.
     *
     * @var array
     */
    private $data = array();

    /**
     * The last missing key, checked by checkRequiredKeys().
     *
     * @var string
     */
    private $lastMissingKey = null;

    /**
     * Constructor.
     *
     * @param array $data Data to use. Keys should all be non-empty strings.
     *
     * @throws ProgrammerException If any keys are false-like. This
     *     includes 0, false, null, '', and others but not strings like '0'.
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            if (!$key) {
                throw new ProgrammerException('All keys must be non-empty strings. Error at key: "%s"', $key ? $key : '{empty_string}');
            }
        }

        $this->data = $data;
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
     * Whether an offset exists.
     *
     * @param mixed $offset Offset value.
     *
     * @return boolean If the offset exists.
     *
     * @throws ProgrammerException If the offset is invalid.
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Gets the item at an offset.
     *
     * @param mixed $offset Offset value.
     *
     * @return mixed The value at the offset.
     *
     * @throws ProgrammerException If the offset is invalid.
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * Sets the item at an offset.
     *
     * @param mixed $offset Offset to set.
     * @param mixed $value  Value to set.
     *
     * @throws ProgrammerException If the offset is invalid.
     */
    public function offsetSet($offset, $value)
    {
        if (!$offset) {
            throw new ProgrammerException('Key must be a non-empty string. Given: "%s" (%s)', $offset, gettype($offset));
        }

        $this->data[$offset] = $value;
    }

    /**
     * Returns the amount of items in the object.
     *
     * @return integer The amount of items.
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Unsets the item at a specified offset.
     *
     * @param mixed $offset Offset to unset.
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * Gets the ArrayIterator instance for use with foreach.
     *
     * @return ArrayIterator Iterator for use with foreach.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Gets the keys to this object.
     *
     * @param boolean $sorted If the keys should be sorted.
     *
     * @return array Array of keys.
     */
    public function keys($sorted = false)
    {
        if ($sorted) {
            $keys = array_keys($this->data);
            sort($keys, SORT_STRING);

            return $keys;
        }

        return array_keys($this->data);
    }

    /**
     * Returns string representation of object.
     *
     * @return string String representation of object.
     */
    public function __toString()
    {
        return $this->jsonEncode($this->data);
    }

    /**
     * Utilized for reading data for inaccessible properties.
     *
     * @param string $key Key to get the value of.
     *
     * @return mixed The value or null.
     */
    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Utilized for setting data for inaccessible properties.
     *
     * @param string $key   Key to set the value of.
     * @param mixed  $value Value to set.
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Prints the data in JSON format. Does not send a JSON header.
     */
    public function printJSON()
    {
        print($this->jsonEncode($this->data));
    }

    /**
     * Returns the data in JSON format.
     *
     * @return string The data in JSON format.
     */
    public function toJSON()
    {
        return $this->jsonEncode($this->data);
    }

    /**
     * Applies a user-defined function to each element of this object.
     *
     * @param string $func     Callback function. The callback takes two parameters:
     *     the Dictionary parameter first (can be a reference) and the key second.
     * @param mixed  $userData If specified, this will be passed to the callback
     *     as the third parameter.
     *
     * @return Dictionary The object to allow method chaining.
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function walk($func, $userData = null)
    {
        foreach ($this->data as $key => $value) {
            call_user_func_array($func, array($this, $key, $userData));
        }

        return $this;
    }

    /**
     * Applies a user-defined function to each element of this object. This
     *     method will recurse into deeper arrays.
     *
     * @param string $func     Callback function. The callback takes two parameters:
     *     the Dictionary parameter first (can be a reference) and the key second.
     * @param mixed  $userData If specified, this will be passed to the callback
     *     as the third parameter.
     *
     * @return Dictionary The object to allow method chaining.
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
     * Gets the values of the object as normal, numerically-index array.
     *
     * @return array Array of values.
     */
    public function values()
    {
        return array_values($this->data);
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
     * @return array Array of keys.
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
     * Merges the values of the arguments with the values of this object.
     *
     * @param Dictionary|array $arrayLike Array or Dictionary instance.
     *
     * @return Dictionary The object to allow method chaining.
     */
    public function merge($arrayLike)
    {
        $args = func_get_args();

        foreach ($args as $key => $arg) {
            if ($arg instanceof static) {
                $args[$key] = $arg->getData();
            }
        }

        array_unshift($args, $this->data);

        $this->data = call_user_func_array('array_merge', $args);

        return $this;
    }

    /**
     * Checks that the object has the required keys specified. The first missing
     *     key will be retrievable by using #getLastMissingKey().
     *
     * @param string $key Key to check.
     *
     * @return boolean If all required keys are present.
     *
     * @throws ValidationException If any key is missing.
     */
    public function checkRequiredKeys($key)
    {
        $keys = func_get_args();
        $ret = true;

        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->data)) {
                $this->lastMissingKey = $key;
                $ret = false;
                break;
            }
        }

        return $ret;
    }

    /**
     * Gets the last missing key. null is the default value.
     *
     * @return string String of the last missing key, or null.
     */
    public function getLastMissingKey()
    {
        return $this->lastMissingKey;
    }

    /**
     * Validates that the object has the required keys specified. Accepts
     *   multiple arguments.
     *
     * If the first argument is an array, that will be considered the set of
     *     keys.
     *
     * @param string|array $key Key to check, or array of keys.
     *
     * @return Dictionary The object to allow method chaining.
     *
     * @throws ValidationException If any key is missing.
     */
    public function validateRequiredKeys($key)
    {
        $cb = array($this, 'checkRequiredKeys');
        $args = array();

        if (is_array($key)) {
            $args = $key;
        }
        else {
            $args = func_get_args();
        }

        if (!call_user_func_array($cb, $args)) {
            throw new ValidationException('The object is missing a key: "%s"', $this->lastMissingKey);
        }

        return $this;
    }

    /**
     * Calls a callback on each item in the object. If the callback returns true,
     *     then the value will be returned in the resulting Dictionary instance of this
     *     method.
     *
     * @param callable $cb Callback to call on each key.
     *
     * @return Dictionary New filtered Dictionary.
     *
     * @see array_filter()
     */
    public function filter($cb)
    {
        return new static(array_filter($this->data, $cb));
    }

    /**
     * Fills the object with keys (replacing old ones if present) with the same
     *     value.
     *
     * @param array $keys  Keys to add.
     * @param mixed $value Value to set.
     *
     * @return Dictionary The object to allow method chaining.
     */
    public function fill(array $keys, $value)
    {
        $this->data = array_merge($this->data, array_fill_keys($keys, $value));

        return $this;
    }

    /**
     * Compares this object's data with associative arrays.
     *
     * @param mixed $array1 Array or array-like object. Takes multiple
     *   arguments.
     *
     * @return Dictionary Object containing all the entries from $array1 that are
     *     not present in any of the other arrays.
     */
    public function diff($array1)
    {
        $args = func_get_args();
        array_unshift($args, $this->data);

        return new static(call_user_func_array('array_diff', $args));
    }

    /**
     * Returns a new Dictionary instance with keys changed to the case specified.
     *
     * @param integer $case One of: CASE_LOWER, CASE_UPPER.
     *
     * @return Dictionary New Dictionary instance with keys changed.
     */
    public function convertKeyCase($case = CASE_LOWER)
    {
        if ($case !== CASE_LOWER && $case !== CASE_UPPER) {
            throw new ProgrammerException('Case argument must be one of the constants: "%s"', implode(', ', array('CASE_LOWER', 'CASE_UPPER')));
        }

        return new static(array_change_key_case($this->data, $case));
    }

    /**
     * Checks if value is an array or is array-like (implementing the correct
     *     interfaces).
     *
     * To be array-like, a class must implement both ArrayAccess and
     *     IteratorAggregate. Optionally, it can implement the Countable interface.
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
     * @param Dictionary $instance   Object instance.
     * @param mixed      &$arrayLike Mixed variable, checked if is array-like.
     * @param callable   $func       Function to call on each item.
     * @param mixed      $userData   User data to add as third argument to callback.
     */
    private static function walkRecursiveCallback(Dictionary $instance, &$arrayLike, $func, $userData = null)
    {
        if (!static::isArrayLike($arrayLike)) {
            return;
        }

        foreach ($arrayLike as $key => $value) {
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
