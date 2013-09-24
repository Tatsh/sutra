<?php
namespace Sutra\Component\Cryptography;

use Symfony\Component\Security\Core\Util\SecureRandomInterface;

/**
 * @replaces fCryptopgraphy
 */
interface RandomGeneratorInterface extends SecureRandomInterface
{
    /**
     * @replaces ::random
     */
    public function random($min = null, $max = null);

    /**
     * @replaces ::randomString
     */
    public function randomString($length, $type = 'alphanumeric');
}
