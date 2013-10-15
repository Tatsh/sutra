<?php
namespace Sutra\Component\Cryptography;

use Sutra\Component\Cryptography\Exception\EnvironmentException;
use Sutra\Component\Cryptography\Exception\ProgrammerException;
use Sutra\Component\Cryptography\Exception\ValidationException;

/**
 * {@inheritdoc}
 */
class PublicKeyCryptography implements PublicKeyCryptographyInterface
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
     * Private key resource.
     *
     * @var resource
     */
    protected $privateKeyResource;

    /**
     * Public key resource.
     *
     * @var resource
     */
    protected $publicKeyResource;

    /**
     * Private key pass-pharse.
     *
     * @var string
     */
    protected $password;

    /**
     * Constructor.
     *
     * @param string $publicKeyFile  Path to an X.509 public key
     *   certificate.
     * @param string $privateKeyFile Path to a PEM-encoded
     *   private key.
     * @param string $password       Password for the private key.
     */
    public function __construct($publicKeyFile, $privateKeyFile, $password = null)
    {
        $this->publicKeyFile = $publicKeyFile;
        $this->privateKeyFile = $privateKeyFile;
        $this->password = $password;

        $this->validateEnvironment();
        $this->createPublicKeyResource();
        $this->createPrivateKeyResource();
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($cipherText)
    {
        $elements = explode('#', $cipherText);

        if (sizeof($elements) != 4 || $elements[0] != 'fCryptography::public') {
            throw new ProgrammerException('The cipher text provided does not appear to have been encrypted using fCryptography::publicKeyEncrypt() or %s#%s()', substr(strrchr(__CLASS__, '\\'), 1), __FUNCTION__);
        }

        $encryptedKey = base64_decode($elements[1]);
        $cipherText = base64_decode($elements[2]);
        $providedHmac = $elements[3];

        $plainText = '';
        $result = openssl_open($cipherText, $plainText, $encryptedKey, $this->privateKeyResource);

        if ($result === false) {
            throw new EnvironmentException('Unknown error occurred whiel decrypting the cipher text provided');
        }

        $hmac = hash_hmac('sha1', $encryptedKey.$cipherText, $plainText);

        // By verifying the HMAC we ensure the integrity of the data
        if ($hmac !== $providedHmac) {
            throw new ValidationException('The cipher text provided appears to have been tampered with or corrupted');
        }

        return $plainText;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($plainText)
    {
        $cipherText = '';
        $encryptedKeys = array();
        $resource = array($this->publicKeyResource);
        $result = openssl_seal($plainText, $cipherText, $encryptedKeys, $resource);

        if ($result === false) {
            throw new EnvironmentException('There was an unknown error encrypting the plain text provided');
        }

        $hmac = hash_hmac('sha1', $encryptedKeys[0].$cipherText, $plainText);

        return 'fCryptography::public#'.base64_encode($encryptedKeys[0]).'#'.base64_encode($cipherText).'#'.$hmac;
    }

    /**
     * {@inheritdoc}
     */
    public function sign($plainText)
    {
        $signature = null;
        $result = openssl_sign($plainText, $signature, $this->privateKeyResource);

        if (!$result) {
            throw new EnvironmentException('There was an unknown error signing the plain text');
        }

        return base64_encode($signature);
    }

    /**
     * {@inheritdoc}
     */
    public function verify($plainText, $signature)
    {
        $result = openssl_verify($plainText, base64_decode($signature), $this->publicKeyResource);

        if ($result === -1) {
            throw new EnvironmentException('There was an unknown error verifying the plain text and signature against the public key specified');
        }

        return $result === 1 ? true : false;
    }

    /**
     * Frees OpenSSL key resources.
     */
    public function __destruct()
    {
        if ($this->privateKeyResource) {
            openssl_free_key($this->privateKeyResource);
        }
        if ($this->publicKeyResource) {
            openssl_free_key($this->publicKeyResource);
        }
    }

    /**
     * Creates a public key resource.
     */
    private function createPublicKeyResource()
    {
        if (!file_exists($this->publicKeyFile)) {
            throw new ProgrammerException('The path to the X.509 certificate specified, %s, is not valid', $this->publicKeyFile);
        }

        if (!is_readable($this->publicKeyFile)) {
            throw new EnvironmentException('The X.509 certificate specified, %s, can not be read', $this->publicKeyFile);
        }

        $publicKeyContents = file_get_contents($this->publicKeyFile);
        $this->publicKeyResource = openssl_pkey_get_public($publicKeyContents);

        if ($this->publicKeyResource === false) {
            throw new ProgrammerException('The public key certificate specified, %s, does not appear to be a valid certificate', $this->publicKeyFile);
        }
    }

    /**
     * Creates a private key resource.
     */
    private function createPrivateKeyResource()
    {
        if (!file_exists($this->privateKeyFile)) {
            throw new ProgrammerException('The path to the PEM-encoded private key specified, %s, is not valid', $this->privateKeyFile);
        }

        if (!is_readable($this->privateKeyFile)) {
            throw new EnvironmentException('The PEM-encoded private key specified, %s, is not readable', $this->privateKeyFile);
        }

        $privateKeyContents = file_get_contents($this->privateKeyFile);
        $this->privateKeyResource = openssl_pkey_get_private($privateKeyContents, $this->password);

        if ($this->privateKeyResource === false) {
            throw new ValidationException('The private key file specified, %s, does not appear to be a valid private key or the password provided is incorrect', $this->password);
        }
    }

    /**
     * Validates the environment.
     *
     * @codeCoverageIgnore
     */
    private function validateEnvironment()
    {
        if (!extension_loaded('openssl')) {
            throw new EnvironmentException('The PHP openssl extension is required for this class, but it does not appear to be loaded');
        }
    }
}
