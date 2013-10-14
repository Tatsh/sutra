<?php
namespace Sutra\Component\Cryptography\Tests;

use Sutra\Component\Cryptography\Cryptography;

class CryptographyTest extends TestCase
{
    protected static $instance;

    public static function setUpBeforeClass()
    {
        // Not supporting Windows here yet
        if (!file_exists('/dev/urandom')) {
            return;
        }

        static::$instance = new Cryptography('/dev/urandom');
    }

    public function testRandom()
    {
        if (!static::$instance) {
            $this->markTestSkipped();

            return;
        }

        $this->assertInternalType('integer', static::$instance->random());

        $this->assertLessThanOrEqual(10, static::$instance->random(1, 10));
    }

    public static function randomStringProvider()
    {
        return array(
            array(10, 'alphanumeric', '/[a-zA-Z0-9]+/'),
            array(20, 'base64', '/[a-zA-Z0-9\+\/]/'),
            array(30, 'base56', '/[a-zA-Z23456789]/'),
            array(40, 'alpha', '/[a-zA-Z]/'),
            array(40, 'base36', '/[A-Z0-9]/'),
            array(40, 'hexadecimal', '/[abcdef0-9]/'),
            array(40, 'numeric', '/[0-9]/'),
            array(40, 'acdef', '/[acdef]/'),
        );
    }

    /**
     * @dataProvider randomStringProvider
     */
    public function testRandomString($length, $alphabet, $regex)
    {
        if (!static::$instance) {
            $this->markTestSkipped();

            return;
        }

        $ret = static::$instance->randomString($length, $alphabet);
        $this->assertRegExp($regex, $ret);
        $this->assertEquals($length, strlen($ret));
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Length must be greater than or equal to 1
     */
    public function testRandomStringException()
    {
        if (!static::$instance) {
            $this->markTestSkipped();

            return;
        }

        static::$instance->randomString(0);
    }

    public function testStringsAreEqual()
    {
        if (!static::$instance) {
            $this->markTestSkipped();

            return;
        }

        $this->assertFalse(static::$instance->stringsAreEqual('a', 'string'));
        $this->assertTrue(static::$instance->stringsAreEqual('string', 'string'));
    }

    // Just for coverage
    public function testConstructor()
    {
        if (!static::$instance) {
            $this->markTestSkipped();

            return;
        }

        $ret = new Cryptography('/dev/urandom');
        $this->assertInstanceOf('Sutra\Component\Cryptography\Cryptography', $ret);
    }
}
