<?php
namespace Sutra\Component\Html\Tests\Purifier;

use Sutra\Component\Html\Purifier\CustomLinkifyConfigurationSchema;
use Sutra\Component\Html\Tests\TestCase;

class CustomLinkifyConfigurationSchemaTest extends TestCase
{
    public function testConstructor()
    {
        $instance = new CustomLinkifyConfigurationSchema();
        $defaults = $instance->defaults;

        $this->assertNull($defaults['AutoFormat.LinkifyWithTextLengthLimit.Limit']);
        $this->assertEquals(' ...', $defaults['AutoFormat.LinkifyWithTextLengthLimit.Suffix']);
        $this->assertTrue($defaults['AutoFormat.LinkifyWithTextLengthLimit.RemoveProtocol']);
    }

    public function testInstance()
    {
        $instance = CustomLinkifyConfigurationSchema::instance();
        $defaults = $instance->defaults;

        $this->assertNull($defaults['AutoFormat.LinkifyWithTextLengthLimit.Limit']);
        $this->assertEquals(' ...', $defaults['AutoFormat.LinkifyWithTextLengthLimit.Suffix']);
        $this->assertTrue($defaults['AutoFormat.LinkifyWithTextLengthLimit.RemoveProtocol']);
    }
}
