<?php
namespace Lubed\Exceptions;

use Closure;
use Lubed\Supports\Starter;

final class DefaultStarter implements Starter
{
    private array $config;
    private Closure $render;

    public function __construct(?array $config,Closure $render)
    {
        $this->config = NULL===$config?[]:$config;
        $this->render = $render;
    }

    public function start()
    {
        $capturer_class = $this->config['class']??'\\Lubed\Exceptions\\ExceptionCapturer';
        $handler_class = $this->config['handler_class']??'\\Lubed\Exceptions\\ExceptionHandler';

        if (false === class_exists($capturer_class)||false === class_exists($handler_class)) {
            Exceptions::StartFailed('start exception capturer failed',[
                'method'=>__METHOD__
            ]);
        }

        $capturer= new $capturer_class();
        $handler = new $handler_class();
        $capturer->pushHandler($handler);
        $capturer->register($this->render);
    }

}
