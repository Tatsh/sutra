<?php
namespace Sutra\Component\DataStructure\Tests;

/**
 * Array-like mock, but fulfills `#isArrayLike()`'s requirements.
 */
class ContainerMock implements \ArrayAccess, \IteratorAggregate
{
    /**
     * Data stored.
     *
     * @var array
     */
    protected $data = array();

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
     * @param mixed $offset Offset.
     *
     * @return boolean Always returns `false`.
     */
    public function offsetExists($offset)
    {
        return false;
    }

    /**
     * Gets the value at a specific offset.
     *
     * @param mixed $offset Offset.
     *
     * @return null Always returns `null`.
     */
    public function offsetGet($offset)
    {
        return null;
    }

    /**
     * Sets the value at an offset.
     *
     * @param mixed $offset Offset to set to.
     * @param mixed $value  Value to set.
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Unsets the value at an offset.
     *
     * @param mixed $offset Offset.
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * Returns string version of object.
     *
     * @return string JSON-encoded array or object.
     */
    public function __toString()
    {
        return json_encode($this->data);
    }
}
