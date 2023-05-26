<?php
namespace Lubed\Exceptions;

final class ExceptionDescription{
	private $err_code;
	private $err_msg;
	private $file;
	private $line;
	private $data;
	private $class;
	private $method;

	public function __construct(int $err_code,string $err_msg='',array $options=[])
	{
		$this->err_code=$err_code;
		$this->err_msg=$err_msg;
		$this->data=$options['data']??NULL;
		$this->file = $options['file']??'';
		$this->line = $options['line']??'';
		$this->class = $options['class']??'';
		$this->method = $options['method']??'';
	}

	public function getErrCode()
	{
		return $this->err_code;
	}

	public function getErrMessage()
	{
		return $this->err_msg;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getFile()
	{
		return $this->file;
	}

	public function getLine()
	{
		return $this->line;
	}

	public function getClass()
	{
		return $this->class;
	}

	public function getMethod()
	{
		return $this->method;
	}
}
