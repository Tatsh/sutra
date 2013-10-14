<?php
namespace Sutra\Component\Cryptography;

use Sutra\Component\Cryptography\Exception\EnvironmentException;

/**
 * {@inheritdoc}
 */
class PublicPrivateKey implements PublicPrivateKeyInterface
{
    /**
     * Public key file.
     *
     * @var string
     */
    protected $publicKeyFile;

    /**
     * Private key file.
     *
     * @var string
     */
    protected $privateKeyFile;

    /**
     * Constructor.
     *
     * @param string $publicKeyFile  Path to an X.509 public key certificate.
     * @param string $privateKeyFile Path to a PEM-encoded private key. Note
     *   that encryption will not work without a private key.
     */
    public function __construct($publicKeyFile, $privateKeyFile = null)
    {
        $this->publicKeyFile = $publicKeyFile;
        $this->privateKeyFile = $privateKeyFile;

        $this->validateEnvironment();
        $this->createPublicKeyResource();

        if ($privateKeyFile) {
            $this->createPrivateKeyResource($this->privateKeyFile);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($cipherText, $password)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($plainText)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function sign($plainText, $password)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function verify($plainText, $signature)
    {
    }

    private function createPublicKeyResource()
    {
    }

    private function createPrivateKeyResource()
    {
    }

    private function validateEnvironment()
    {
        if (!extension_loaded('openssl')) {
            throw new EnvironmentException('The PHP openssl extension is required for this class, but it does not appear to be loaded');
        }
    }
}
