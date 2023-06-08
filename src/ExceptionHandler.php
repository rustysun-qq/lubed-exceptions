<?php
namespace Lubed\Exceptions;

use Lubed\Exceptions\Formatter\PlainFormatter;
use Closure;

class ExceptionHandler {
    const DONE = 0x01;
    const LAST_HANDLER = 0x02;
    const QUIT = 0x03;

    private $inspector;
    private $exception;
    private $invoker;

    public function __construct() {
    }

    public function handle() {
        $inspector = $this->getInspector();
        $frames = $inspector->getFrames();
        $exception = $inspector->getException();
        //NOT BIZ EXCEPTION
        if (!$exception instanceof BizException) {
            $options=method_exists($exception,'getOptions')?$exception->getOptions():[
                'file'=>rtrim(basename($exception->getFile()),'.php'),
                'line'=>$exception->getLine()
            ];
            $vars = [
                "title"           => 'Whoops! There was an error.',
                "name"            => explode("\\", $inspector->getExceptionName()),
                "message"         => $inspector->getException()->getMessage(),
                "code"            => $exception->getCode(),
                "plain_exception" => PlainFormatter::format($inspector),
                "frames"          => $frames,
                "has_frames"      => !!count($frames),
                "handler"         => $this,
                "handlers"        => $inspector->getHandlers(),
                'class' => get_class($exception),
                'options' => $options
            ];
            $result = new DefaultExceptionResult($vars);
            if($this->invoker){
                ($this->invoker)($result);
            }
        }

        return self::QUIT;
    }

    public function setInspector(Inspector $inspector) {
        $this->inspector = $inspector;
    }

    protected function getInspector() {
        return $this->inspector;
    }

    public function setInvoker(Closure $invoker){
        return $this->invoker = $invoker;
    }

    public function setException($exception) {
        $this->exception = $exception;
    }

    protected function getException() {
        return $this->exception;
    }
}
