<?php
namespace Lubed\Exceptions;

class DefaultExceptionResult implements ExceptionResult
{
	public function __construct($result) {
		$this->result=$result;
	}

	public function getResult() {
		return $this->result;
	}
}