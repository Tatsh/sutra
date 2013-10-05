<?php
namespace Sutra\Component\DataStructure\Tests;

/**
 * Array-like mock, but does not fulfill `#isArrayLike()`'s requirements.
 */
class ContainerMockInvalid implements \IteratorAggregate
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
     *
     * @codeCoverageIgnore
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
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
