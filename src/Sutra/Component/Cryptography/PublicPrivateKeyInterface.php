<?php
namespace Sutra\Component\Cryptography;

/**
 * @replaces fCryptography
 */
interface PublicPrivateKeyInterface
{
    /**
     * @replaces ::publicKeyDecrypt
     */
    public function decrypt($cipherText, $password);

    /**
     * @replaces ::publicKeyEncrypt
     */
    public function encrypt($plainText);

    /**
     * @replaces ::publicKeySign
     */
    public function sign($plainText, $password);

    /**
     * @replaces ::publicKeyVerify
     */
    public function verify($plainText, $signature);
}
