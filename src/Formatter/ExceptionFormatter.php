<?php
namespace Lubed\Exceptions\Formatter;

use Lubed\Exceptions\ExceptionResult;
use Lubed\Exceptions\Inspector;

interface ExceptionFormatter
{
	public static function format(Inspector $inspector,bool $is_stack_trace=FALSE):ExceptionResult;
}