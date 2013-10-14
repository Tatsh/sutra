<?php
namespace Sutra\Component\Cryptography;

/**
 * Simple cryptography interface.
 *
 * @replaces fCryptopgraphy
 */
interface CryptographyInterface
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
     * @param string  $type   Type of alphabet. One of: base64, alphanumeric,
     *   base56, alpha, base36, hexadecimal, numeric. If not any of these, then
     *   what is passed as string will be the alphabet used.
     *
     * @return string Randomly generated string.
     *
     * @replaces ::randomString
     */
    public function randomString($length, $type = 'alphanumeric');

    /**
     * @param string $s1 String to check.
     * @param string $s2 String to compare against.
     *
     * @return boolean If the strings are equal.
     */
    public function stringsAreEqual($s1, $s2);
}
