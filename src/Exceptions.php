<?php
namespace Lubed\Exceptions;

final class Exceptions {
	const START_FAILED = 101301;
	const FRAME_READONLY=101302;
	const UNEXPECTED_VALUE=101303;

	public static function unexpectedValue(string $msg)
	{
		throw new RuntimeException(self::UNEXPECTED_VALUE,$msg);
	}

	public static function frameReadOnly(string $msg)
	{
		throw new RuntimeException(self::FRAME_READONLY,$msg);
	}

	public static function startFailed(string $msg,array $options=[])
	{
		$options['sub_error_code']=self::START_FAILED;
		throw new SysException($msg,$options);
	}
}