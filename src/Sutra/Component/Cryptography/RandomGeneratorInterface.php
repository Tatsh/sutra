<?php
namespace Sutra\Component\Cryptography;

use Symfony\Component\Security\Core\Util\SecureRandomInterface;

/**
 * Random number/string generator.
 *
 * @replaces fCryptopgraphy
 */
interface RandomGeneratorInterface extends SecureRandomInterface
{
    /**
     * Generates a random number.
     *
     * @param integer $min Minimum value.
     * @param integer $max Maximum value.
     *
     * @replaces ::random
     */
    public function random($min = null, $max = null);

    /**
     * Generates a random string.
     *
     * @param integer $length Length of the string to generate.
     * @param string  $type   Type of alphabet.
     *
     * @replaces ::randomString
     */
    public function randomString($length, $type = 'alphanumeric');
}
