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

class RefundStatusCheckResponse implements \JsonSerializable
{
	public string $originalMerchantOrderId;
	public $amount;
	public string $state;
	public $paymentDetails;

	/**
	 * @param $httpResponse
	 * @return RefundStatusCheckResponse
	 * @throws \JsonMapper_Exception
	 */
	public static function getInstance($httpResponse): RefundStatusCheckResponse
	{
		$mapper = new JsonMapper();
        $mapper->bEnforceMapType = false;
		$refundStatusCheckResponse = $mapper->map($httpResponse, new RefundStatusCheckResponse());
		return $refundStatusCheckResponse;

	}
	public function getOriginalMerchantOrderId(): string
	{
		return $this->originalMerchantOrderId;
	}

	/**
	 * @return mixed
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @return string
	 */
	public function getState(): string
	{
		return $this->state;
	}

	/**
	 * @return mixed
	 */
	public function getPaymentDetails()
	{
		return $this->paymentDetails;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return get_object_vars($this);
	}
}