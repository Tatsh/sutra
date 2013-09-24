<?php
namespace Sutra\Component\Cryptography;

/**
 * @replaces fCryptography
 */
interface SymmetricKeyInterface
{
    /**
     * @replaces ::symmetricKeyDecrypt
     */
    public function decrypt($cipherText);

    /**
     * @replaces ::symmetricKeyEncrypt
     */
    public function encrypt($plainText);
}
