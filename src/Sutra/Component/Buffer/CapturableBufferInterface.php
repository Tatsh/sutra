<?php
namespace Sutra\Component\Buffer;

/**
 * {@inheritdoc}
 *
 * Also supports capture of output for functions like `var_dump()`, etc.
 */
interface CapturableBufferInterface extends BufferInterface
{
    /**
     * Starts output capturing.
     *
     * @throws ProgrammerException If output capturing has already started.
     */
    public function startCapture();

    /**
     * Stops output capturing.
     *
     * @return string Captured output.
     *
     * @throws ProgrammerException If output capturing cannot be stopped.
     */
    public function stopCapture();
}
