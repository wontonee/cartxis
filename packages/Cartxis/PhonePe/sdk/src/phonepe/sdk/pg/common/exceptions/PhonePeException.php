<?php

/*
*  Copyright (c) 2025 Original Author(s), PhonePe India Pvt. Ltd.
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*  http://www.apache.org/licenses/LICENSE-2.0
*
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License.
*/


namespace PhonePe\common\exceptions;

use Exception;
use Throwable;

/**
 * PhonePe Generic Exception
 */
class PhonePeException extends Exception
{
	public $httpStatusCode;
	public $code;
	public $message;
	public $data;

	/**
	 * @param $message
	 * @param mixed $httpStatusCode optional
	 * @param mixed $code optional
	 * @param mixed $data optional
	 * @param Throwable|null $previous optional
	 */
	public function __construct( $message = '', $httpStatusCode = 0 , $code = 0,$data = null, ?Throwable $previous = null)
	{
		parent::__construct($message, $httpStatusCode, $previous);
		$this->httpStatusCode = $httpStatusCode;
		$this->message = $message;
		$this->code = $code;
		$this->data = $data;
	}

	public function getHttpStatusCode(): mixed
	{
		return $this->httpStatusCode;
	}

	/**
	 * @return mixed
	 */

	public function getData(): mixed
	{
		return $this->data;
	}

}