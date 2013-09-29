<?php
namespace Sutra\Component\Date\Tests;

class DumbDateMock
{
    public function __toString()
    {
        return DateTest::DATE_TO_TEST;
    }
}
