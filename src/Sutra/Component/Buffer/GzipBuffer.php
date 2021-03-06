<?php
namespace Sutra\Component\Buffer;

use Sutra\Component\Buffer\Exception\ProgrammerException;

/**
 * {@inheritdoc}
 */
class GzipBuffer extends Buffer
{
    /**
     * {@inheritDoc}
     */
    public function start()
    {
        if ($this->started) {
            throw new ProgrammerException('Output buffering has already been started');
        }

        if ($this->capturing) {
            throw new ProgrammerException('Output capturing is currently active and it must be stopped before the buffering can be started');
        }

        ob_start('ob_gzhandler');
        $this->started = true;
    }
}
