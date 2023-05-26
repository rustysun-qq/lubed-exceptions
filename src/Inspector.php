<?php
namespace Lubed\Exceptions;
use Error;

class Inspector {
    private $exception;
    private $handlers;
    private $frames;
    private $previousExceptionInspector;

    public function __construct($exception, $handlers=[]) {
        $this->exception = $exception;
        $this->handlers = $handlers;
    }

    public function getException() {
        return $this->exception;
    }

    public function getExceptionName() {
        return get_class($this->exception);
    }

    public function getExceptionMessage() {
        return $this->exception->getMessage();
    }

    public function getHandlers() {
        return $this->handlers;
    }

    public function hasPreviousException() {
        return $this->previousExceptionInspector || $this->exception->getPrevious();
    }

    public function getPreviousExceptionInspector() {
        if ($this->previousExceptionInspector === NULL) {
            $previousException = $this->exception->getPrevious();

            if ($previousException) {
                $this->previousExceptionInspector = new Inspector($previousException);
            }
        }

        return $this->previousExceptionInspector;
    }

    public function getFrames() {
        if ($this->frames === NULL) {
            $frames = $this->getTrace($this->exception);

            foreach ($frames as $k => $frame) {

                if (empty($frame['file'])) {
                    $file = '[internal]';
                    $line = 0;

                    $next_frame = !empty($frames[$k + 1]) ? $frames[$k + 1] : [];

                    if ($this->isValidNextFrame($next_frame)) {
                        $file = $next_frame['file'];
                        $line = $next_frame['line'];
                    }

                    $frames[$k]['file'] = $file;
                    $frames[$k]['line'] = $line;
                }
            }

            $i = 0;
            foreach ($frames as $k => $frame) {
                if ($frame['file'] == $this->exception->getFile() && $frame['line'] == $this->exception->getLine()) {
                    $i = $k;
                }
            }

            if ($i > 0) {
                array_splice($frames, 0, $i);
            }

            $firstFrame = $this->getFrameFromException($this->exception);
            array_unshift($frames, $firstFrame);
            $this->frames = new FrameCollection($frames);

            if ($previousInspector = $this->getPreviousExceptionInspector()) {
                $outerFrames = $this->frames;
                $newFrames = clone $previousInspector->getFrames();

                if (isset($newFrames[0])) {
                    $newFrames[0]->addComment($previousInspector->getExceptionMessage(), 'Exception message:');
                }

                $newFrames->prependFrames($outerFrames->topDiff($newFrames));
                $this->frames = $newFrames;
            }
        }

        return $this->frames;
    }

    protected function getTrace($e) {
        $traces = $e->getTrace();

        if (!$e instanceof Error) {
            return $traces;
        }

        if (!$this->isLevelFatal(error_reporting())) {
            return $traces;
        }

        //use xdebug extension
        if (!extension_loaded('xdebug') || !xdebug_is_enabled()) {
            return [];
        }

        $stack = array_reverse(xdebug_get_function_stack());
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $traces = array_diff_key($stack, $trace);

        return $traces;
    }

    protected function getFrameFromException($exception) {
        return [
            'file'  => $exception->getFile(),
            'line'  => $exception->getLine(),
            'class' => get_class($exception),
            'args'  => [
                $exception->getMessage(),
            ],
        ];
    }

    protected function getFrameFromError(Error $exception) {
        return [
            'file'  => $exception->getFile(),
            'line'  => $exception->getLine(),
            'class' => 'Error',
            'args'  => [],
        ];
    }

    protected function isValidNextFrame(array $frame) {
        if (empty($frame['file']) || empty($frame['line'])) {
            return FALSE;
        }

        if (empty($frame['function']) || !stristr($frame['function'], 'call_user_func')) {
            return FALSE;
        }

        return TRUE;
    }

    protected function isLevelFatal($level) {
        $errors = E_ERROR;
        $errors |= E_PARSE;
        $errors |= E_CORE_ERROR;
        $errors |= E_CORE_WARNING;
        $errors |= E_COMPILE_ERROR;
        $errors |= E_COMPILE_WARNING;
        return ($level & $errors) > 0;
    }
}
