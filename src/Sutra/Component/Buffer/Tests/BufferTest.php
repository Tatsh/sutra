<?php
namespace Sutra\Component\Buffer\Tests;

use Sutra\Component\Buffer\Buffer;
use Sutra\Component\Buffer\Exception\EnvironmentException;
use Sutra\Component\Buffer\Exception\ProgrammerException;

class BufferTest extends TestCase
{
    protected $instance;
    protected $startedBuffer = false;
    protected $startedCapture = false;

    public function setUp()
    {
        $this->instance = new Buffer();
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
        $this->startedBuffer = true;
        $this->instance->startCapture();
        $this->startedCapture = true;
    }

    public function testIsStarted()
    {
        $this->assertEquals(false, $this->instance->isStarted());
        $this->instance->start();
        $this->startedBuffer = true;
        $this->assertEquals(true, $this->instance->isStarted());
    }

    public function testGet()
    {
        $this->instance->start();
        $this->startedBuffer = true;
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
        $this->startedBuffer = true;
        $this->instance->startCapture();
        $this->startedCapture = true;
        $this->instance->get();
    }

    public function testErase()
    {
        $this->instance->start();
        $this->startedBuffer = true;
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
        $this->startedBuffer = true;
        $this->instance->startCapture();
        $this->startedCapture = true;
        $this->instance->erase();
    }

    public function testStopBuffering()
    {
        $level = ob_get_level();
        $this->instance->start();
        $this->instance->stop();
        $this->assertEquals($level, ob_get_level());
    }

    public function testStopBufferingWithFlush()
    {
        $this->instance->start();
        print('O');
        $this->instance->stop();
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
        $this->startedBuffer = true;
        $this->instance->startCapture();
        $this->startedCapture = true;
        $this->instance->stop();
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage A replacement cannot be made since output buffering has not been started
     */
    public function testReplaceWithoutStarting()
    {
        $this->instance->replace('find', 'replace');
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage Output capturing is currently active and it must be stopped before a replacement can be made
     */
    public function testReplaceDuringCapture()
    {
        $this->instance->start();
        $this->startedBuffer = true;
        $this->instance->startCapture();
        $this->startedCapture = true;
        $this->instance->replace('find', 'replace');
    }

    public function testReplace()
    {
        $this->instance->start();
        $this->startedBuffer = true;
        print('1');
        $this->assertEquals('1', ob_get_contents());
        $this->instance->replace('1', 'replacement string');
        $this->assertEquals('replacement string', ob_get_contents());
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

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage Output capturing has already been started
     */
    public function testStartCaptureWhileCapturing()
    {
        $this->instance->startCapture();
        $this->startedCapture = true;
        $this->instance->startCapture();
    }

    /**
     * @expectedException Sutra\Component\Buffer\Exception\ProgrammerException
     * @expectedExceptionMessage Output capturing cannot be stopped since it has not been started
     */
    public function testStopCaptureWhileNotCapturing()
    {
        $this->instance->stopCapture();
    }

    public function tearDown()
    {
        if ($this->startedCapture) {
            $this->instance->stopCapture();
            $this->startedCapture = false;
        }
        if ($this->startedBuffer) {
            ob_clean();
            $this->instance->stop();
            $this->startedBuffer = false;
        }
    }
}
