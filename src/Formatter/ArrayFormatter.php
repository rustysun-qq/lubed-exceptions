<?php
namespace Lubed\Exceptions\Formatter;

use Lubed\Exceptions\ExceptionsResult;
use Lubed\Exceptions\Inspector;

class ArrayFormatter implements ExceptionFormatter{
    public static function format(Inspector $inspector,bool $is_stack_trace=FALSE):ExceptionResult {
        $exception = $inspector->getException();
        $response = [
            'type'    => get_class($exception),
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
        ];

        if ($is_stack_trace) {
            $frames = $inspector->getFrames();
            $frameData = [];

            foreach ($frames as $frame) {
                $frameData[] = [
                    'file'     => $frame->getFile(),
                    'line'     => $frame->getLine(),
                    'function' => $frame->getFunction(),
                    'class'    => $frame->getClass(),
                    'args'     => $frame->getArgs(),
                ];
            }

            $response['stack_trace'] = $frameData;
        }

        return (new DefaultExceptionResult($response));
    }
}
