<?php
namespace Sutra\Component\Html\Purifier;

/**
 * Niceness class to HTML Purifier for type checking but without the real
 *   class.
 */
interface PurifierInterface
{
    /**
     * Filters an HTML snippet/document to be XSS-free and standards-compliant.
     *
     * @param string                 $content String of HTML to purify.
     * @param ConfigurationInterface $config  Config object for this operation,
     *   if omitted, defaults to the config object specified during this
     *   object's construction. The parameter can also be any type
     *   that ` HTMLPurifier_Config::create()` supports.
     *
     * @return string Purified HTML.
     *
     * @throws \BadMethodCallException If any arguments are non-null but of
     *   invalid type.
     */
    public function purify($content, $config = null);

    /**
     * Filters an array of strings of HTML content.
     *
     * @param array                  $contents Array of strings to purify.
     * @param ConfigurationInterface $config   Config object for this
     *   operation, if omitted, defaults to the config object specified during
     *   this object's construction. The parameter can also be any type
     *   that ` HTMLPurifier_Config::create()` supports.
     *
     * @return array Array of purified HTML.
     *
     * @throws \BadMethodCallException If any arguments are non-null but of
     *   invalid type.
     */
    public function purifyArray($contents, $config = null);
}
