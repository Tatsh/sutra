<?php
namespace Sutra\Component\Html\Purifier;

/**
 * Creates a schema, with defaults, but adds the
 *   `LinkifyWithTextLengthLimit` options.
 */
class CustomLinkifyConfigurationSchema extends \HTMLPurifier_ConfigSchema implements ConfigurationSchemaInterface
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();

        $instance = static::instance();
        $this->defaults = $instance->defaults;

        $this->add('AutoFormat.LinkifyWithTextLengthLimit.Limit', null, 'mixed', true);
        $this->add('AutoFormat.LinkifyWithTextLengthLimit.Suffix', ' ...', 'string', true);
        $this->add('AutoFormat.LinkifyWithTextLengthLimit.RemoveProtocol', true, 'bool', false);
    }

    /**
     * {@inheritDoc}
     */
    public static function instance($prototype = null)
    {
        $instance = parent::instance($prototype);

        $instance->add('AutoFormat.LinkifyWithTextLengthLimit.Limit', null, 'mixed', true);
        $instance->add('AutoFormat.LinkifyWithTextLengthLimit.Suffix', ' ...', 'string', true);
        $instance->add('AutoFormat.LinkifyWithTextLengthLimit.RemoveProtocol', true, 'bool', false);

        return $instance;
    }
}
