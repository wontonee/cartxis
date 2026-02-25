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

use PhonePe\payments\v2\models\response\PaymentInstrument\Pa;

class StandardCheckoutPayResponse implements \JsonSerializable
{

	private string $orderId;
	private string $state;
	private string $redirectUrl;
	private int $expiresAt;

	/**
	 * @param string $orderId
	 * @param string $state
	 * @param string $redirectUrl
	 * @param int $expiresAt
	 */
	public function __construct($orderId, $state, $redirectUrl, $expiresAt){
		$this->orderId = $orderId;
		$this->state = $state;
		$this->redirectUrl = $redirectUrl;
		$this->expiresAt = $expiresAt;
	}

	/**
	 * @return string
	 */
	public function getOrderId()
	{
		return $this->orderId;
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @return string
	 */
	public function getRedirectUrl()
	{
		return $this->redirectUrl;
	}

	/**
	 * @return int
	 */
	public function getExpireAt()
	{
		return $this->expiresAt;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return get_object_vars($this);
	}
}