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


namespace PhonePe\payments\v2\models\response;

use JsonMapper;
use PhonePe\payments\v2\models\response\ResponseComponents\Payload;

class CallbackResponse implements \JsonSerializable
{
	public $type;
	public Payload $payload;

	public function jsonSerialize(): array
	{
		return get_object_vars($this);
	}

	public static function getInstance($body): CallbackResponse
	{
		$obj = json_decode($body);
		$mapper = new JsonMapper();
		$callbackResponse = $mapper->map($obj, new CallbackResponse());
		return $callbackResponse;

	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return Payload
	 */
	public function getPayload(): Payload
	{
		return $this->payload;
	}


}