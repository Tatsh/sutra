<?php
namespace Sutra\Component\Cryptography;

/**
 * @replaces fCrytopgraphy
 */
class PublicPrivateKey implements PublicPrivateKeyInterface
{
    protected $publicKeyFile;
    protected $privateKeyFile;

    public function __construct($publicKeyFile, $privateKeyFile = null)
    {
        $this->publicKeyFile = $publicKeyFile;
        $this->privateKeyFile = $privateKeyFile;
    }

    public function decrypt($cipherText, $password)
    {
    }

    public function encrypt($plainText)
    {
    }

    public function sign($plainText, $password)
    {
    }

    public function verify($plainText, $signature)
    {
    }
}
