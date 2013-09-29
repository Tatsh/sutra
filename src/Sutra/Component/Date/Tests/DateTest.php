<?php
namespace Sutra\Component\Date\Tests;

use Sutra\Component\Date\Date;

class DateTest extends TestCase
{
    const DATE_TO_TEST = '2013-05-12';

    /**
     * @expectedException Sutra\Component\Date\Exception\ValidationException
     * @expectedExceptionMessage The date given "red" was not able to be parsed
     */
    public function testConstructorBadTimestamp()
    {
        $date = new Date('red');
    }

    public function testToString()
    {
        $date = new Date(static::DATE_TO_TEST);
        $str = (string) $date;
        $this->assertEquals(static::DATE_TO_TEST, $str);

        $date = new Date(new \DateTime(static::DATE_TO_TEST));
        $str = (string) $date;
        $this->assertEquals(static::DATE_TO_TEST, $str);

        $date = new Date(new DumbDateMock());
        $str = (string) $date;
        $this->assertEquals(static::DATE_TO_TEST, $str);
    }

    public function testAdjust()
    {
        $date = new Date(static::DATE_TO_TEST);
        $newDate = $date->adjust('+1 week');
        $this->assertEquals('2013-05-19', (string) $newDate);

        // Make sure original date is not changed
        $this->assertEquals(static::DATE_TO_TEST, (string) $date);
    }

    /**
     * @expectedException Sutra\Component\Date\Exception\ValidationException
     * @expectedExceptionMessage The adjustment specified, "-500 years", does not appear to be a valid relative date measurement
     */
    public function testAdjustBadTimestamp()
    {
        $date = new Date(static::DATE_TO_TEST);
        $date->adjust('-500 years');
    }

    public function testEquals()
    {
        $date = new Date(static::DATE_TO_TEST);
        $val = $date->equals();
        $this->assertFalse($val);

        $val = $date->equals($date);
        $this->assertTrue($val);
    }

    /**
     * @expectedException Sutra\Component\Date\Exception\ProgrammerException
     * @expectedExceptionMessage The formatting string, "AB", contains one of the following non-date formatting characters: a, A, B, c, e, g, G, h, H, i, I, O, P, r, s, T, u, U, Z
     */
    public function testFormatWithTimeCharacter()
    {
        $date = new Date(static::DATE_TO_TEST);
        $date->format('AB');
    }

    public function testFormat()
    {
        $date = new Date(static::DATE_TO_TEST);
        $val = $date->format('m-d-Y');
        $this->assertEquals('05-12-2013', (string) $val);
    }

    public static function getFuzzyDifferenceProvider()
    {
        $date = new Date();

        return array(
            array(null, false, 'today'),
            array(false, null, 'today'), // alternative signature
            array($date->adjust('-1 day'), false, '1 day after'),
            array($date->adjust('-1 day'), true, '1 day'),
            array($date->adjust('-2 days'), false, '2 days after'),
            array($date->adjust('-2 days'), true, '2 days'),
            array($date->adjust('-14 days'), false, '2 weeks after'),
            array($date->adjust('-14 days'), true, '2 weeks'),
            array($date->adjust('-1 month'), false, '1 month after'),
            array($date->adjust('-2 months'), false, '2 months after'),
            array($date->adjust('-2 months'), true, '2 months'),
            array($date->adjust('+1 year'), false, '1 year before'),
            array($date->adjust('+10 years'), false, '10 years before'),
            array(clone $date, false, 'same day'),
        );
    }

    /**
     * NOTE Time-sensitive.
     */
    public function testGetFuzzyDifferenceAgo()
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $date = new Date(sprintf('%d-%d-01', $currentYear, $currentMonth - 2));

        $this->assertRegExp('/[23] months ago/', $date->getFuzzyDifference());
    }

    /**
     * NOTE Time-sensitive.
     */
    public function testGetFuzzyDifferenceFromNow()
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $date = new Date(sprintf('%d-%d-01', $currentYear, $currentMonth + 3));

        $this->assertRegExp('/[23] months from now/', $date->getFuzzyDifference());
    }

    /**
     * @dataProvider getFuzzyDifferenceProvider
     */
    public function testGetFuzzyDifference($otherDate, $simple, $expected)
    {
        $date = new Date();
        $val = $date->getFuzzyDifference($otherDate, $simple);
        $this->assertEquals($expected, $val);
    }

    public function testGreaterThan()
    {
        $date = new Date(static::DATE_TO_TEST);
        $this->assertFalse($date->greaterThan());
        $this->assertTrue($date->greaterThan('2012-05-12'));
        $this->assertFalse($date->greaterThan(static::DATE_TO_TEST));
    }

    public function testGreaterThanOrEqualTo()
    {
        $date = new Date(static::DATE_TO_TEST);
        $this->assertFalse($date->greaterThanOrEqualTo());
        $this->assertTrue($date->greaterThanOrEqualTo(static::DATE_TO_TEST));
        $this->assertTrue($date->greaterThanOrEqualTo('2012-05-12'));
        $this->assertFalse($date->greaterThanOrEqualTo('2099-05-12'));
    }

    public function testLessThan()
    {
        $date = new Date(static::DATE_TO_TEST);
        $this->assertTrue($date->lessThan());
        $this->assertFalse($date->lessThan('2012-05-12'));
        $this->assertTrue($date->lessThan('2099-05-12'));
    }

    public function testLessThanOrEqualTo()
    {
        $date = new Date(static::DATE_TO_TEST);
        $this->assertTrue($date->lessThanOrEqualTo());
        $this->assertFalse($date->lessThanOrEqualTo('2012-05-12'));
        $this->assertTrue($date->lessThanOrEqualTo(static::DATE_TO_TEST));
        $this->assertTrue($date->lessThanOrEqualTo('2099-05-12'));
    }

    public function testModify()
    {
        $date = new Date(static::DATE_TO_TEST);

        $newDate = $date->modify('Y-m-01');
        $this->assertNotEquals($date, $newDate);
        $this->assertEquals('2013-05-01', (string) $newDate);

        $newDate = $date->modify('Y-m-t');
        $this->assertEquals('2013-05-31', (string) $newDate);
    }
}
