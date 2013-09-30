<?php
namespace Sutra\Component\Buffer;

use Sutra\Component\Buffer\Exception\ProgrammerException;

/**
 * Provides a single, simplified interface for output buffering to prevent
 *   nested buffering issues and provide a more logical API.
 *
 * The best way to use this class is in a way that keeps only one instance.
 *   Examples: DI container, core class that holds your app 'globals'.
 */
interface BufferInterface
{
    /**
     * Erases current output buffer stream.
     *
     * @throws ProgrammerException If the buffer cannot be erased.
     */
    public function erase();

    /**
     * Gets current output buffer stream.
     *
     * @return string Output buffer contents.
     *
     * @throws ProgrammerException If the output buffer cannot be retrieved.
     *
     * @see ob_get_contents()
     */
    public function get();

    /**
     * If output buffering has already started.
     *
     * @return boolean If output buffering has already started.
     */
    public function isStarted();

    /**
     * Replaces contents within the output buffer.
     *
     * @param string|array $find    What to find.
     * @param string|array $replace Replacement string or array of replacements
     *   mapping to find array.
     *
     * @throws ProgrammerException If it is not possible to make replacements.
     *
     * @see str_replace()
     */
    public function replace($find, $replace);

    /**
     * Starts output buffering.
     *
     * @throws ProgrammerException If output buffering cannot be started.
     */
    public function start();

    /**
     * Stops output buffering.
     *
     * @throws ProgrammerException If output buffering cannot be stopped.
     */
    public function stop();
}
