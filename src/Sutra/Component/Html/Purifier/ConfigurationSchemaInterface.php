<?php
namespace Sutra\Component\Html\Purifier;

/**
 * Interface for HTML Purifier configuration schema, primarily for type
 *   checking but without needing the real class.
 */
interface ConfigurationSchemaInterface
{
    /**
     * Gets global static instance of configuration schema.
     *
     * @return ConfigurationSchemaInterface Global instance.
     *
     * @todo Figure out way this does not need to exist.
     */
    public static function instance();
}
