<?php
namespace Sutra\Component\Cryptography;

use Sutra\Component\Cryptography\Exception\EnvironmentException;
use Sutra\Component\Cryptography\Exception\ProgrammerException;
use Sutra\Component\Cryptography\Exception\ValidationException;

/**
 * {@inheritDdoc}
 */
class SymmetricKeyCryptography implements SymmetricKeyCryptographyInterface
{
    /**
     * Validates environment.
     */
    public function __construct()
    {
        $this->verifyEnvironment();
    }

    /**
     * {@inheritDdoc}
     */
    public function decrypt($cipherText, $secretKey)
    {
        if (strlen($secretKey) < 8) {
            throw new ValidationException('The secret key specified does not meet the minimum requirement of being at least 8 characters long');
        }

        $elements = explode('#', $cipherText);

        if (sizeof($elements) != 4 || $elements[0] != 'fCryptography::symmetric') {
            throw new ProgrammerException('The cipher text provided does not appear to have been encrypted using fCryptography::symmetricKeyEncrypt() or %s#encrypt()', substr(strrchr(__CLASS__, '\\'), 1));
        }

        $iv = base64_decode($elements[1]);
        $cipherText = base64_decode($elements[2]);
        $providedHmac = $elements[3];

        $hmac = hash_hmac('sha1', $iv.'#'.$cipherText, $secretKey);

        if ($hmac !== $providedHmac) {
            throw new ValidationException('The cipher text provided appears to have been tampered with or corrupted (is the secret key correct?)');
        }

        $module = mcrypt_module_open('rijndael-192', '', 'cfb', '');
        $key = substr(sha1($secretKey), 0, mcrypt_enc_get_key_size($module));
        mcrypt_generic_init($module, $key, $iv);

        $plainText = @mdecrypt_generic($module, $cipherText);

        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);

        return $plainText;
    }

    /**
     * {@inheritDdoc}
     */
    public function encrypt($plainText, $secretKey)
    {
        if (strlen($secretKey) < 8) {
            throw new ValidationException('The secret key specified does not meet the minimum requirement of being at least 8 characters long');
        }

        $module = mcrypt_module_open('rijndael-192', '', 'cfb', '');
        $key = substr(sha1($secretKey), 0, mcrypt_enc_get_key_size($module));

        srand();

        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($module), MCRYPT_RAND);

        mcrypt_generic_init($module, $key, $iv);

        $cipherText = @mcrypt_generic($module, $plainText);

        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);

        $hmac = hash_hmac('sha1', $iv.'#'.$cipherText, $secretKey);

        $encodedIv = base64_encode($iv);
        $encodedCipherText = base64_encode($cipherText);

        return 'fCryptography::symmetric#'.$encodedIv.'#'.$encodedCipherText.'#'.$hmac;
    }

    /**
     * Validates mcrypt installation.
     *
     * @codeCoverageIgnore
     */
    private function verifyEnvironment()
    {
        if (!function_exists('mcrypt_module_open')) {
            throw new EnvironmentException('The cipher used, %1$s (also known as %2$s), requires libmcrypt version 2.4.x or newer. The version installed does not appear to meet this requirement.', 'AES-192', 'rijndael-192');
        }

        if (!in_array('rijndael-192', mcrypt_list_algorithms())) {
            throw new EnvironmentException('The cipher used, %1$s (also known as %2$s), does not appear to be supported by the installed version of libmcrypt', 'AES-192', 'rijndael-192');
        }
    }
}
