<?php
namespace Sutra\Component\Buffer;

interface CapturableBufferInterface extends BufferInterface
{
    public function startCapture();
    public function stopCapture();
}
