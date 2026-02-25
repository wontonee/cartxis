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


namespace PhonePe\payments\v2\models\request;

class StandardCheckoutPayRequest implements \JsonSerializable
{
	private string $merchantOrderId;
	private int $amount;
	private $metaInfo;
	private array $paymentFlow;

	/**
	 * @param string $merchantOrderId
	 * @param int $amount
	 * @param $metaInfo
	 * @param array $paymentFlow
	 */
	public function __construct($merchantOrderId, $amount, $metaInfo, $paymentFlow)
	{
		$this->merchantOrderId = $merchantOrderId;
		$this->amount = $amount;
		$this->metaInfo = $metaInfo;
		$this->paymentFlow = $paymentFlow;
	}

	/**
	 * @return string
	 */
	public function getMerchantOrderId()
	{
		return $this->merchantOrderId;
	}

	/**
	 * @return int
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @return mixed
	 */
	public function getMetaInfo()
	{
		return $this->metaInfo;
	}

	/**
	 * @return array
	 */
	public function getPaymentFlow()
	{
		return $this->paymentFlow;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return get_object_vars($this);
	}


}