<?php
namespace Sutra\Component\Cryptography;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Util\StringUtils;

/**
 * {@inheritdoc}
 */
class Cryptography implements CryptographyInterface
{
    /**
     * SecureRandomInterface instance.
     *
     * @var SecureRandom
     */
    protected $secureRandom;

    /**
     * If `mt_rand()` has been seeded.
     *
     * @var boolean
     */
    protected $seeded = false;

    /**
     * Constructor.
     *
     * Be aware that a guessable seed will severely compromise the PRNG
     *   algorithm that is employed.
     *
     * @param string          $seedFile Seed file (random data).
     * @param LoggerInterface $logger   Logger instance.
     */
    public function __construct($seedFile = null, LoggerInterface $logger = null)
    {
        $this->secureRandom = new SecureRandom($seedFile, $logger);
    }

    /**
     * {@inheritDoc}
     */
    public function random($min = null, $max = null)
    {
        $this->seedRandom();

        if ($min !== null || $max !== null) {
            return mt_rand($min, $max);
        }

        return mt_rand();
    }

    /**
     * {@inheritDoc}
     *
     * @throws \UnexpectedValueException If length is invalid.
     */
    public function randomString($length, $type = 'alphanumeric')
    {
        if ($length < 1) {
            throw new \BadMethodCallException('Length must be greater than or equal to 1');
        }

        switch ($type) {
            case 'base64':
                $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+/';
                break;

            case 'alphanumeric':
                $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;

            case 'base56':
                $alphabet = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                break;

            case 'alpha':
                $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;

            case 'base36':
                $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;

            case 'hexadecimal':
                $alphabet = 'abcdef0123456789';
                break;

            case 'numeric':
                $alphabet = '0123456789';
                break;

            default:
                $alphabet = $type;
                break;
        }

        $alphabetLength = strlen($alphabet);
        $output = '';

        for ($i = 0; $i < $length; $i++) {
            $output .= $alphabet[$this->random(0, $alphabetLength - 1)];
        }

        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function stringsAreEqual($s1, $s2)
    {
        return StringUtils::equals($s1, $s2);
    }

    /**
     * Seeds `mt_rand()` function.
     *
     * @see mt_srand()
     * @see mt_rand()
     */
    private function seedRandom()
    {
        if ($this->seeded) {
            return;
        }

        $bytes = $this->secureRandom->nextBytes(4);
        $seed = (int) base_convert(bin2hex($bytes), 16, 10) - 2147483647;

        mt_srand($seed);

        $this->seeded = true;
    }
}
