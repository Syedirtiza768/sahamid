<?php

namespace SAHamid\BI\Exception;

class BIException extends \RuntimeException
{
	private $errorCode;
	private $httpStatus;
	private $details;

	public function __construct($errorCode, $message, $httpStatus = 400, array $details = array())
	{
		parent::__construct($message);
		$this->errorCode = $errorCode;
		$this->httpStatus = (int) $httpStatus;
		$this->details = $details;
	}

	public function getErrorCode()
	{
		return $this->errorCode;
	}

	public function getHttpStatus()
	{
		return $this->httpStatus;
	}

	public function getDetails()
	{
		return $this->details;
	}
}
