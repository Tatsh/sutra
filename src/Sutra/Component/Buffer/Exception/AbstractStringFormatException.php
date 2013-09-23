<?php
namespace Sutra\Component\Buffer\Exception;

/**
 * Exception that handles arguments like `sprintf()`.
 *
 * @see sprintf()
 */
abstract class AbstractStringFormatException extends \Exception
{
    protected $previous;

    public function __construct($message, $code = 0, $previous = null)
    {
        $args = func_get_args();
        $format = array_shift($args);

        if (count($args) === 0) {
            $this->message = $format;

            return;
        }

        $this->message = sprintf($format, $args);
    }

    public function setCode($code)
    {
        $this->code = (int) $code;
    }

    public function setPrevious(\Exception $exception)
    {
        $this->previous = $exception;
    }
}
