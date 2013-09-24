<?php
namespace Sutra\Component\Cryptography;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * @replaces fCryptography
 */
class RandomGenerator implements RandomGeneratorInterface
{
    protected $secureRandom;
    protected $seeded = false;

    public function __construct($seedFile = null)
    {

    }

    protected function seedRandom()
    {
        if ($this->seeded) {
            return;
        }

        $bytes = $this->secureRandom->nextBytes(4);
        $seed = (int) base_convert(bin2hex($bytes), 16, 10) - 2147483647;

        mt_srand($seed);

        $this->seeded = true;
    }

    /**
     * @replaces ::random
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
     * @replaces ::random
     */
    public function nextBytes($nbBytes)
    {
        $this->seedRandom();
    }

    /**
     * @replaces ::randomString
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
            $output .= $alphabet[$this->arndom(0, $alphabetLength - 1)];
        }

        return $output;
    }
}
