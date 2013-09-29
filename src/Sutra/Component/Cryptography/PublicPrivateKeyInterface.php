<?php
namespace Sutra\Component\Cryptography;

/**
 * Simple interface for public/private key signing/encryption/decryption.
 *
 * Implementers: A public key (X.509 certificate) is required for encryption
 *   and a private key (PEM) is required for decryption. Recommend that the
 *   constructor should receive these files or their contents as arguments.
 *
 * @todo Better interface/class name.
 *
 * @replaces fCryptography
 */
interface PublicPrivateKeyInterface
{
    /**
     * Decrypts cipher text encrypted using public-key encryption via
     *   `#encrypt()`.
     *
     * @param string $cipherText Content to be decrytped.
     * @param string $password   Password for the private key.
     *
     * @return string Decrypted plain text.
     *
     * @replaces ::publicKeyDecrypt
     */
    public function decrypt($cipherText, $password);

    /**
     * Encrypts the passed data using public key encryption via OpenSSL.
     *
     * @param string $plainText The content to be encrypted.
     *
     * @return Encrypted string.
     *
     * @replaces ::publicKeyEncrypt
     */
    public function encrypt($plainText);

    /**
     * Creates a signature for plain text to allow verification of the creator.
     *
     * @param string $plainText The content to be signed.
     * @param string $password  Password for the private key.
     *
     * @replaces ::publicKeySign
     */
    public function sign($plainText, $password);

    /**
     * Checks a signature for plaintext to verify the creator, intended to work
     *   with `#publicKeySign()`.
     *
     * @param string $plainText The content to check.
     * @param string $signature Base64-encoded signature for the plain text.
     *
     * @return boolean If the public key is the public key of the user who
     *   signed the plain text.
     *
     * @replaces ::publicKeyVerify
     */
    public function verify($plainText, $signature);
}
