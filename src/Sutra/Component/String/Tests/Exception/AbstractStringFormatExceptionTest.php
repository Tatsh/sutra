<?php
namespace Sutra\Component\String\Tests\Exception;

use Sutra\Component\String\Tests\TestCase;

class AbstractStringFormatExceptionTest extends TestCase
{
    const ABSTRACT_EXCEPTION_CLASS = 'Sutra\Component\String\Exception\AbstractStringFormatException';

    public function testConstructor()
    {
        $e = $this->getMockForAbstractClass(static::ABSTRACT_EXCEPTION_CLASS, array('regular message'));
        $this->assertEquals('regular message', $e->getMessage());

        $e = $this->getMockForAbstractClass(static::ABSTRACT_EXCEPTION_CLASS, array('regular message %d', 1));
        $this->assertEquals('regular message 1', $e->getMessage());
    }

    public function testSetCode()
    {
        $e = $this->getMockForAbstractClass(static::ABSTRACT_EXCEPTION_CLASS, array('regular message'));
        $e->setCode(2);
        $this->assertEquals(2, $e->getCode());
    }
}
