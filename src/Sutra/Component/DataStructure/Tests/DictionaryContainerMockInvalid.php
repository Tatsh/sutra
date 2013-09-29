<?php
namespace Sutra\Component\DataStructure\Tests;

class DictionaryContainerMockInvalid implements \IteratorAggregate
{
    private $data = array();

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function __toString()
    {
        return json_encode($this->data);
    }
}
