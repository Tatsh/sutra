<?php
namespace Sutra\Component\Cryptography;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * {@inheritdoc}
 */
class RandomGenerator implements RandomGeneratorInterface
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     *
     * @replaces ::random
     */
    public function nextBytes($nbBytes)
    {
        $this->seedRandom();
    }

    /**
     * Generates a random string.
     *
     * @param integer $length Length of the string to generate.
     * @param string  $type   Type of alphabet. One of: base64, alphanumeric,
     *   base56, alpha, base36, hexadecimal, numeric. If not any of these, then
     *   what is passed as string will be the alphabet used.
     *
     * @return string Randomly generated string.
     *
     * @throws \UnexpectedValueException If length is invalid.
     */
    public function randomString($length, $type = 'alphanumeric')
    {
        if ($length < 1) {
            throw new \UnexpectedValueException('Length must be greater than or equal to 1');
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
