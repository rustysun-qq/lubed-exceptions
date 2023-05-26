<?php
namespace Lubed\Exceptions;

use Error;
use Exception;
use Closure;
use Lubed\Supports\Starter;
use Lubed\Utils\Config;
use Lubed\Http\Streams\InputStream;
use Lubed\Http\Request as HttpRequest;
use Lubed\Http\Uri;
use Lubed\Exceptions\ExceptionResult;

final class DefaultStarter implements Starter
{
    private array $config;
    private Closure $render;

    public function __construct(array $config,Closure $render)
    {
        $this->config = $config;
        $this->render = $render;
    }

    public function start()
    {
        $capturer_class = $this->config['class']??'\\Lubed\Exceptions\\ExceptionCapturer';
        $handler_class = $this->config['handler_class']??'\\Lubed\Exceptions\\ExceptionHandler';

        if (false === class_exists($capturer_class)||false === class_exists($handler_class)) {
            Exceptions::StartFailed('start exception capturer failed',[
                'class'=>__CLASS__,
                'method'=>__METHOD__
            ]);
        }

        $capturer= new $capturer_class();
        $handler = new $handler_class();
        $capturer->pushHandler($handler);
        $capturer->register($this->render);
    }

}
