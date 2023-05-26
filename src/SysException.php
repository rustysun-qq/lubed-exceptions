<?php
namespace Lubed\Exceptions;

use Lubed\Utils\ErrCode;
use Throwable;

final class SysException extends LubedException {
	public function __construct(string $message,array $options=[],?Throwable $previous=NULL)
	{
		parent::__construct(ErrCode::SYSTEM_EXCEPTION, $message, $options,$previous);
	}
}
