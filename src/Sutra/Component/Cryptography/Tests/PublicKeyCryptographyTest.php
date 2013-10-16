<?php
namespace Sutra\Component\Cryptography\Tests;

use Symfony\Component\Process\Process;
use Sutra\Component\Cryptography\Exception\EnvironmentException;
use Sutra\Component\Cryptography\Exception\ValidationException;
use Sutra\Component\Cryptography\PublicKeyCryptography;

class PublicKeyCryptographyTest extends TestCase
{
    protected static $invalidKey;
    protected static $publicKeyFile = './_pubkey.pem';
    protected static $privateKeyFile = './_privkey.pem';
    protected static $skip = false;

    protected static function cleanUp()
    {
        $files = array(
            static::$publicKeyFile,
            static::$privateKeyFile,
            static::$invalidKey,
        );

        foreach ($files as $filename) {
            if (!is_file($filename)) {
                continue;
            }

            chmod($filename, 0640);
            unlink($filename);
        }
    }

    public static function setUpBeforeClass()
    {
        static::$invalidKey = __DIR__.'/.____random_file____';
        static::cleanUp();

        $proc = new Process('openssl genrsa -out ./_privkey.pem 2048');
        $proc->run();

        if (!$proc->isSuccessful()) {
            static::$skip = true;

            return;
        }

        $proc = new Process('openssl rsa -in ./_privkey.pem -pubout -out _pubkey.pem');
        $proc->run();

        if (!$proc->isSuccessful()) {
            static::$skip = true;

            return;
        }
    }

    /**
     * @expectedException Sutra\Component\Cryptography\Exception\ProgrammerException
     * @expectedExceptionMessage The path to the X.509 certificate specified, random_file, is not valid
     */
    public function testConstructorExceptionPublic1()
    {
        new PublicKeyCryptography('random_file', 'random_file2');
    }

    public function testConstructorExceptionPublic2()
    {
        touch(static::$invalidKey);
        chmod(static::$invalidKey, 0300);

        $raised = false;

        try {
            new PublicKeyCryptography(static::$invalidKey, 'random_file2');
        }
        catch (EnvironmentException $e) {
            $this->assertEquals(sprintf('The X.509 certificate specified, %s, is not readable', static::$invalidKey), $e->getMessage());

            return;
        }

        $this->fail('EnvironmentException not raised');
    }

    public function testConstructorExceptionPublic3()
    {
        touch(static::$invalidKey);
        chmod(static::$invalidKey, 0640);

        try {
            new PublicKeyCryptography(static::$invalidKey, 'random_file2');
        }
        catch (ValidationException $e) {
            $this->assertEquals(sprintf('The certificate specified, %s, does not appear to be a valid certificate', static::$invalidKey), $e->getMessage());

            return;
        }

        $this->fail('ValidationException not raised');
    }

    /**
     * @expectedException Sutra\Component\Cryptography\Exception\ProgrammerException
     * @expectedExceptionMessage The path to the PEM-encoded private key specified, random_file2, is not valid
     */
    public function testContructorExceptionPrivate1()
    {
        new PublicKeyCryptography(static::$publicKeyFile, 'random_file2');
    }

    public function testContructorExceptionPrivate2()
    {
        touch(static::$invalidKey);
        chmod(static::$invalidKey, 0300);

        $raised = false;

        try {
            new PublicKeyCryptography(static::$publicKeyFile, static::$invalidKey);
        }
        catch (EnvironmentException $e) {
            $this->assertEquals(sprintf('The PEM-encoded private key specified, %s, is not readable', static::$invalidKey), $e->getMessage());

            return;
        }

        $this->fail('EnvironmentException not raised');
    }

    public function testConstructorExceptionPrivate3()
    {
        touch(static::$invalidKey);
        chmod(static::$invalidKey, 0640);

        try {
            new PublicKeyCryptography(static::$publicKeyFile, static::$invalidKey);
        }
        catch (ValidationException $e) {
            $this->assertEquals(sprintf('The private key file specified, %s, does not appear to be a valid private key or the password provided is incorrect', static::$invalidKey), $e->getMessage());

            return;
        }

        $this->fail('ValidationException not raised');
    }

    /**
     * @expectedException Sutra\Component\Cryptography\Exception\ProgrammerException
     * @expectedExceptionMessage The cipher text provided does not appear to have been encrypted using fCryptography::publicKeyEncrypt() or PublicKeyCryptography#decrypt()
     */
    public function testDecryptInvalid()
    {
        if (static::$skip) {
            $this->markTestSkipped();

            return;
        }

        $crypto = new PublicKeyCryptography(static::$publicKeyFile, static::$privateKeyFile);
        $crypto->decrypt('fCryptography::invalid#');
    }

    /**
     * @expectedException Sutra\Component\Cryptography\Exception\ValidationException
     * @expectedExceptionMessage The cipher text provided appears to have been tampered with or corrupted
     */
    public function testDecryptBadHash()
    {
        $crypto = new PublicKeyCryptography(static::$publicKeyFile, static::$privateKeyFile);
        $encrypted = $crypto->encrypt('some string');
        $encrypted .= 'ejoaigjeogj';
        $crypto->decrypt($encrypted);
    }

    public function testEncrypt()
    {
        if (static::$skip) {
            $this->markTestSkipped();

            return;
        }

        $crypto = new PublicKeyCryptography(static::$publicKeyFile, static::$privateKeyFile);
        $encrypted = $crypto->encrypt('my plain text');
        $this->assertEquals('my plain text', $crypto->decrypt($encrypted));
    }

    public function testSignAndVerify()
    {
        if (static::$skip) {
            $this->markTestSkipped();

            return;
        }

        $crypto = new PublicKeyCryptography(static::$publicKeyFile, static::$privateKeyFile);
        $signature = $crypto->sign('my plain text');
        $otherSig = $crypto->sign('other plain text');
        $this->assertTrue($crypto->verify('my plain text', $signature));
        $this->assertFalse($crypto->verify('other plain text', $signature));
        $this->assertFalse($crypto->verify('my plain text', $otherSig));
    }

    public static function tearDownAfterClass()
    {
        static::cleanUp();
    }
}
