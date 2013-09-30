<?php
namespace Sutra\Component\Buffer\Tests;

use Sutra\Component\Buffer\GzipBuffer;
use Sutra\Component\Buffer\Exception\ProgrammerException;

class GzipBufferTest extends TestCase
{
    protected $instance;
    protected $startedBuffer = false;
    protected $startedCapture = false;

    public function setUp()
    {
        $this->instance = new GzipBuffer();
    }

    public function testStartBuffering()
    {
        $level = ob_get_level();
        $this->instance->start();
        $this->startedBuffer = true;
        $this->assertEquals($level + 1, ob_get_level());
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage Output buffering has already been started
     */
    public function testStartBufferingTwice()
    {
        $this->instance->start();
        $this->startedBuffer = true;
        $this->instance->start();
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage Output capturing is currently active and it must be stopped before the buffering can be started
     */
    public function testStartWhileCapturing()
    {
        $this->instance->startCapture();
        $this->startedCapture = true;
        $this->instance->start();
    }

    public function tearDown()
    {
        if ($this->startedCapture) {
            $this->instance->stopCapture();
            $this->startedCapture = false;
        }
        if ($this->startedBuffer) {
            @ob_clean();
            $this->instance->stop();
            $this->startedBuffer = false;
        }
    }
}
