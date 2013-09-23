<?php
namespace Sutra\Component\Buffer\Tests;

use Sutra\Component\Buffer\Buffer;
use Sutra\Component\Buffer\Exception\EnvironmentException;
use Sutra\Component\Buffer\Exception\ProgrammerException;

class BufferTest extends TestCase
{
    protected $instance;

    public function setUp()
    {
        $this->instance = new Buffer();
        $this->started_buffer = FALSE;
        $this->started_capture = FALSE;
    }

    public function testStartBuffering()
    {
        $level = ob_get_level();
        $this->instance->start();
        $this->started_buffer = TRUE;
        $this->assertEquals($level + 1, ob_get_level());
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage Output buffering has already been started
     */
    public function testStartBufferingTwice()
    {
        $this->instance->start();
        $this->started_buffer = TRUE;
        $this->instance->start();
    }

    public function testCapturing()
    {
        ob_start();
        $this->instance->startCapture();
        echo 'testing capture';
        $this->assertEquals('testing capture', $this->instance->stopCapture());
        $this->assertEquals('', ob_get_clean());
    }

    public function testStartCapturingAfterBuffering()
    {
        $this->instance->start();
        $this->started_buffer = TRUE;
        $this->instance->startCapture();
        $this->started_capture = TRUE;
    }

    public function testIsStarted()
    {
        $this->assertEquals(FALSE, $this->instance->isStarted());
        $this->instance->start();
        $this->started_buffer = TRUE;
        $this->assertEquals(TRUE, $this->instance->isStarted());
    }

    public function testGet()
    {
        $this->instance->start();
        $this->started_buffer = TRUE;
        echo 'testing get';
        $this->assertEquals('testing get', $this->instance->get());
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage The output buffer cannot be retrieved because it has not been started
     */
    public function testGetBeforeStart()
    {
        $this->instance->get();
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage Output capturing is currently active and it must be stopped before the buffer can be retrieved
     */
    public function testGetDuringCapture()
    {
        $this->instance->start();
        $this->started_buffer = TRUE;
        $this->instance->startCapture();
        $this->started_capture = TRUE;
        $this->instance->get();
    }

    public function testErase()
    {
        $this->instance->start();
        $this->started_buffer = TRUE;
        echo 'testing erase';
        $this->instance->erase();
        $this->assertEquals('', $this->instance->get());
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage The output buffer cannot be erased since output buffering has not been started
     */
    public function testEraseBeforeStart()
    {
        $this->instance->erase();
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage Output capturing is currently active and it must be stopped before the buffer can be erased
     */
    public function testEraseDuringCapture()
    {
        $this->instance->start();
        $this->started_buffer = TRUE;
        $this->instance->startCapture();
        $this->started_capture = TRUE;
        $this->instance->erase();
    }

    public function testStopBuffering()
    {
        $level = ob_get_level();
        $this->instance->start();
        $this->instance->stop();
        $this->assertEquals($level, ob_get_level());
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage Output buffering cannot be stopped since it has not been started
     */
    public function testStopBeforeStart()
    {
        $this->instance->stop();
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage Output capturing is currently active and it must be stopped before buffering can be stopped
     */
    public function testStopDuringCapture()
    {
        $this->instance->start();
        $this->started_buffer = TRUE;
        $this->instance->startCapture();
        $this->started_capture = TRUE;
        $this->instance->stop();
    }

    public function tearDown()
    {
        if ($this->started_capture) {
            $this->instance->stopCapture();
            $this->started_capture = FALSE;
        }
        if ($this->started_buffer) {
            ob_clean();
            $this->instance->stop();
            $this->started_buffer = FALSE;
        }
    }
}
