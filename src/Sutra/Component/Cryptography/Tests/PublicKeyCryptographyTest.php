<?php
namespace Sutra\Component\Cryptography\Tests;

use Symfony\Component\Process\Process;
use Sutra\Component\Cryptography\PublicKeyCryptography;

class PublicKeyCryptographyTest extends TestCase
{
    protected $publicKeyFile = './_pubkey.pem';
    protected $privateKeyFile = './_privkey.pem';
    protected static $skip = false;

    protected static function cleanUp()
    {
        foreach (array('./_privkey.pem', '_pubkey.pem') as $filename) {
            if (is_file($filename)) {
                unlink($filename);
            }
        }
    }

    public static function setUpBeforeClass()
    {
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
     * @expectedExceptionMessage The cipher text provided does not appear to have been encrypted using fCryptography::publicKeyEncrypt() or PublicKeyCryptography#decrypt()
     */
    public function testDecryptInvalid()
    {
        if (static::$skip) {
            $this->markTestSkipped();

            return;
        }

        $crypto = new PublicKeyCryptography($this->publicKeyFile, $this->privateKeyFile);
        $crypto->decrypt('fCryptography::invalid#');
    }

    /**
     * @expectedException Sutra\Component\Cryptography\Exception\ValidationException
     * @expectedExceptionMessage The cipher text provided appears to have been tampered with or corrupted
     */
    public function testDecryptBadHash()
    {
        $crypto = new PublicKeyCryptography($this->publicKeyFile, $this->privateKeyFile);
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

        $crypto = new PublicKeyCryptography($this->publicKeyFile, $this->privateKeyFile);
        $encrypted = $crypto->encrypt('my plain text');
        $this->assertEquals('my plain text', $crypto->decrypt($encrypted));
    }

    public static function tearDownAfterClass()
    {
        static::cleanUp();
    }
}
