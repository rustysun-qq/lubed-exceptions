<?php
namespace Lubed\Exceptions;
use Closure;

class ExceptionCapturer {
    //handler
    const EXCEPTION_HANDLER='handleException';
    const ERROR_HANDLER='handleError';
    const SHUTDOWN_HANDLER='handleShutdown';

    protected $is_registered;
    protected $can_throw_exception;
    private $handlerStack=[];
    private $throwable=true;
    private $callback;

    public function pushHandler(ExceptionHandler $handler):self {
        $this->handlerStack[]=$handler;
        return $this;
    }

    public function register(Closure $callback):self {
        if ($this->is_registered) {
            return $this;
        }

        set_error_handler([$this, self::ERROR_HANDLER]);
        set_exception_handler([$this, self::EXCEPTION_HANDLER]);
        register_shutdown_function([$this, self::SHUTDOWN_HANDLER]);
        $this->callback=$callback;
        $this->is_registered=true;
        return $this;
    }

    public function handleError($level, $message, $file=null, $line=null) {
        if ($level & error_reporting()) {
            $options = ['level'=>$level, 'file'=>$file, 'line'=>$line];
            $exception = new SysException($message, $options);

            if ($this->throwable) {
               throw $exception;
            }

            $this->handleException($exception);
            return true;
        }

        return false;
    }

    public function handleException($exception) {
        $code=$exception->getCode();
        $msg=$exception->getMessage();
        $inspector=$this->getInspector($exception);
        $handlerResponse=null;

        foreach (array_reverse($this->handlerStack) as $handler) {
            if (!$handler instanceof ExceptionHandler) {
                continue;
            }

            $handler->setInspector($inspector);
            $handler->setException($exception);
            $handler->setInvoker($this->callback);
            $handlerResponse=$handler->handle();

            if (in_array($handlerResponse, [ExceptionHandler::LAST_HANDLER, ExceptionHandler::QUIT])) {
                break;
            }
        }
    }

    public function handleShutdown() {
        $this->throwable=false;
        $error=error_get_last();
        if ($error && $this->isLevelFatal($error['type'])) {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    private function getInspector($exception) {
        return new Inspector($exception, $this->handlerStack);
    }

    protected function isLevelFatal($level) {
        $errors=E_ERROR;
        $errors|=E_PARSE;
        $errors|=E_CORE_ERROR;
        $errors|=E_CORE_WARNING;
        $errors|=E_COMPILE_ERROR;
        $errors|=E_COMPILE_WARNING;
        return ($level & $errors) > 0;
    }
}
