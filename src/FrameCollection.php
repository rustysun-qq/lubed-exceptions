<?php
namespace Lubed\Exceptions;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use UnexpectedValueException;
use Lubed\Utils\ErrCode;

class FrameCollection implements ArrayAccess,IteratorAggregate,Countable
{
    private $frames;

    public function __construct(array $frames) {
        $this->init($frames);
    }

    public function filter($callable):self {
        $this->frames = array_filter($this->frames, $callable);
        return $this;
    }

    public function map($callable):self {
        $this->frames = array_map(function ($frame) use ($callable) {
            $frame = call_user_func($callable, $frame);

            if (!$frame instanceof Frame) {
                Excptions::unexpectedValue(sprintf("%s:Callable to %s must return a Frame object",__CLASS__,__METHOD__));
            }

            return $frame;
        }, $this->frames);

        return $this;
    }

    public function getArray():array {
        return $this->frames;
    }

    public function getIterator():Traversable {
        return new ArrayIterator($this->frames);
    }

    public function offsetExists($offset):bool {
        return isset($this->frames[$offset]);
    }

    public function offsetGet($offset):mixed {
        return $this->frames[$offset];
    }

    public function offsetSet($offset, $value):void {
        Excptions::frameReadOnly(sprintf('%s is read only',__CLASS__));
    }

    public function offsetUnset($offset):void {
        Excptions::frameReadOnly(sprintf('%s is read only',__CLASS__));
    }

    public function count():int {
        return count($this->frames);
    }

    public function serialize():string {
        return serialize($this->frames);
    }

    public function unserialize($serializedFrames):void {
        $this->frames = unserialize($serializedFrames);
    }

    public function prependFrames(array $frames):void {
        $this->frames = array_merge($frames, $this->frames);
    }

    public function topDiff(FrameCollection $parentFrames):array {
        $diff = $this->frames;
        $parentFrames = $parentFrames->getArray();
        $p = count($parentFrames) - 1;

        for ($i = count($diff) - 1; $i >= 0 && $p >= 0; $i--) {
            $tailFrame = $diff[$i];

            if ($tailFrame->equals($parentFrames[$p])) {
                unset($diff[$i]);
            }

            $p--;
        }

        return $diff;
    }

    private function init(array $frames)
    {
        $this->frames = array_map(function ($frame) {
            return new Frame($frame);
        }, $frames);
    }
}
