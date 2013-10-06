<?php
namespace Sutra\Component\Cryptography;

/**
 * Simple cryptography interface.
 */
interface CryptographyInterface
{
    /**
     * @param string $s1 String to check.
     * @param string $s2 String to compare against.
     */
    public function stringsAreEqual($s1, $s2);
}
