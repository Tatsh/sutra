<?php
namespace Sutra\Component\Buffer;

interface BufferInterface
{
    public function erase();
    public function get();
    public function isStarted();
    public function replace($find, $replace);
    public function start($gzip = false);
    public function stop();

}
