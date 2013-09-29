<?php
namespace Sutra\Component\Date\Tests;

/**
 * Mock with bare minimum to be an object to pass to `Date` or similar classes.
 */
class DumbDateMock
{
    /**
     * Returns same date as in test.
     *
     * @return string Date string.
     */
    public function __toString()
    {
        return DateTest::DATE_TO_TEST;
    }
}
