<?php
namespace Sutra\Component\DataStructure\Tests;

class ContainerMock implements \ArrayAccess, \IteratorAggregate
{
    private $data = array();

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function offsetExists($offset)
    {
        return false;
    }

    public function offsetGet($offset)
    {
        return null;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function __toString()
    {
        return json_encode($this->data);
    }
}
