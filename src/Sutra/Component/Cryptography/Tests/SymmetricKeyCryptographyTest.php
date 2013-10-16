<?php
namespace Sutra\Component\Cryptography\Tests;

use Sutra\Component\Cryptography\Exception\EnvironmentException;
use Sutra\Component\Cryptography\Exception\ValidationException;
use Sutra\Component\Cryptography\SymmetricKeyCryptography;

class SymmetricKeyCryptographyTest extends TestCase
{
    /**
     * @expectedException Sutra\Component\Cryptography\Exception\ValidationException
     * @expectedExceptionMessage The secret key specified does not meet the minimum requirement of being at least 8 characters long
     */
    public function testDecryptBadSecretKey()
    {
        $sym = new SymmetricKeyCryptography();

        $sym->decrypt('text', 'badkey');
    }

    /**
     * @expectedException Sutra\Component\Cryptography\Exception\ProgrammerException
     * @expectedExceptionMessage The cipher text provided does not appear to have been encrypted using fCryptography::symmetricKeyEncrypt() or SymmetricKeyCryptography#encrypt()
     */
    public function testDecryptBadCipherText()
    {
        $sym = new SymmetricKeyCryptography();

        $sym->decrypt('text', 'goodkey12346');
    }

    /**
     * @expectedException Sutra\Component\Cryptography\Exception\ValidationException
     * @expectedExceptionMessage The cipher text provided appears to have been tampered with or corrupted (is the secret key correct?)
     */
    public function testDecryptBadHash()
    {
        $sym = new SymmetricKeyCryptography();

        $encrypted = $sym->encrypt('some text', 'goodkey12346').'addtohash';
        $sym->decrypt($encrypted, 'goodkey12346');
    }

    /**
     * @expectedException Sutra\Component\Cryptography\Exception\ValidationException
     * @expectedExceptionMessage The cipher text provided appears to have been tampered with or corrupted (is the secret key correct?)
     */
    public function testDecryptBadKey()
    {
        $sym = new SymmetricKeyCryptography();

        $encrypted = $sym->encrypt('some text', 'goodkey12346');
        $this->assertNotEquals('some text', $sym->decrypt($encrypted, 'badpassword'));
    }

    public function testDecrypt()
    {
        $sym = new SymmetricKeyCryptography();

        $encrypted = $sym->encrypt('some text', 'goodkey12346');
        $this->assertEquals('some text', $sym->decrypt($encrypted, 'goodkey12346'));
    }

    /**
     * @expectedException Sutra\Component\Cryptography\Exception\ValidationException
     * @expectedExceptionMessage The secret key specified does not meet the minimum requirement of being at least 8 characters long
     */
    public function testEncryptBadSecretKey()
    {
        $sym = new SymmetricKeyCryptography();

        $sym->encrypt('text', 'badkey');
    }
}
