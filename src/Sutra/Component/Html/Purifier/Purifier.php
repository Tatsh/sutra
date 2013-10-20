<?php
namespace Sutra\Component\Html\Purifier;

/**
 * Extends HTML Purifier but does type checking.
 */
class Purifier extends \HTMLPurifier implements PurifierInterface
{
    /**
     * {@inheritDoc}
     */
    public function purify($content, $config = null)
    {
        if ($config && !($config instanceof ConfigurationInterface)) {
            throw new \BadMethodCallException(sprintf('Argument 2 must be an instance of %s\ConfigurationInterface or null', __NAMESPACE__));
        }

        return parent::purify($content, $config = null);
    }

    /**
     * {@inheritDoc}
     */
    public function purifyArray($contents, $config = null)
    {
        if (!is_array($contents) && !($contents instanceof \Traversable)) {
            throw new \BadMethodCallException('Argument 1 must be an array of strings');
        }
        if ($config && !($config instanceof ConfigurationInterface)) {
            throw new \BadMethodCallException(sprintf('Argument 2 must be an instance of %s\ConfigurationInterface or null', __NAMESPACE__));
        }

        return parent::purifyArray($contents, $config);
    }
}
