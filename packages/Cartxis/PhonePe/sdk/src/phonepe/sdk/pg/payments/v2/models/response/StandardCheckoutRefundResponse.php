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

class StandardCheckoutRefundResponse implements \JsonSerializable
{
	private string $refundId;
	private $amount;
	private $state;

	/**
	 * @param string $refundId
	 * @param $amount
	 * @param $state
	 */
	public function __construct(string $refundId, $amount, $state)
	{
		$this->refundId = $refundId;
		$this->amount = $amount;
		$this->state = $state;
	}

	public function getRefundId(): string
	{
		return $this->refundId;
	}

	/**
	 * @return mixed
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @return mixed
	 */
	public function getState()
	{
		return $this->state;
	}


	public function jsonSerialize(): array
	{
		return get_object_vars($this);
	}
}