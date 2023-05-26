<?php
namespace Lubed\Exceptions;

use Exception;
use Throwable;

class LubedException extends Exception
{
    private $options;

    public function __construct(
        int $code = 0,
        string $message = "",
        array $options = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->options=$options;
    }

    public function getData()
    {
        return $this->options['data']??NULL;
    }

    public function getClass()
    {
        return $this->options['class']??NULL;
    }

    public function getMethod()
    {
        return $this->options['method']??NULL;
    }

    public function getOptions():array
    {
        return $this->options;
    }
}
