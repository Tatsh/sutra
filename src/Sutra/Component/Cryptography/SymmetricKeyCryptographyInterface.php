<?php
namespace Sutra\Component\Cryptography;

use Sutra\Component\Cryptography\Exception\ValidationException;

/**
 * Simple interface for symmetric key cryptography.
 *
 * Implementers: Take the secret key in a setter or the contructor. Always use
 *   the same prefix or suffix (fingerprint) for the text when encrypting and
 *   decrypting.
 *
 * @replaces fCryptography
 */
interface SymmetricKeyCryptographyInterface
{
    /**
     * Decrypts cipher text encrypted using symmetric key encyption via `#encrypt()`.
     *
     * @param string $cipherText Content to be decrypted.
     * @param string $secretKey  The secret key to use for encryption.
     *
     * @return string Decrypted string.
     *
     * @throws ValidationException If the cipher text appears to be corrupted.
     *
     * @replaces ::symmetricKeyDecrypt
     */
    public function decrypt($cipherText, $secretKey);

    /**
     * Encrypts the passed data using symmetric-key encryption.
     *
     * Since this is symmetric-key cryptography, the same key is used for
     * encryption and decryption.
     *
     * @param string $plainText The content to be encrypted.
     * @param string $secretKey  The secret key to use for encryption.
     *
     * @return string An encrypted and base-64 encoded result containing a
     *   fingerprint and suitable for decryption using `#decrypt()`.
     *
     * @replaces ::symmetricKeyEncrypt
     */
    public function encrypt($plainText, $secretKey);
}
