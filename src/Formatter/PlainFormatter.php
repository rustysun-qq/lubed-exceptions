<?php
namespace Lubed\Exceptions\Formatter;

use Lubed\Exceptions\DefaultExceptionResult;
use Lubed\Exceptions\ExceptionResult;
use Lubed\Exceptions\Inspector;

class PlainFormatter implements ExceptionFormatter
{
    public static function format(Inspector $inspector,bool $is_stack_trace=FALSE):ExceptionResult {
        $message = $inspector->getException()->getMessage();
        $frames = $inspector->getFrames();
        $plain = $inspector->getExceptionName();
        $plain .= ' thrown with message "';
        $plain .= $message;
        $plain .= '"' . "\n\n";

        if($is_stack_trace){
            $plain .= "Stacktrace:\n";
            foreach ($frames as $i => $frame) {
                $plain .= "#" . (count($frames) - $i - 1) . " ";
                $plain .= $frame->getClass() ?: '';
                $plain .= $frame->getClass() && $frame->getFunction() ? ":" : "";
                $plain .= $frame->getFunction() ?: '';
                $plain .= ' in ';
                $plain .= ($frame->getFile() ?: '<#unknown>');
                $plain .= ':';
                $plain .= (int) $frame->getLine() . "\n";
            }
        }

        return (new DefaultExceptionResult($plain));
    }
}
