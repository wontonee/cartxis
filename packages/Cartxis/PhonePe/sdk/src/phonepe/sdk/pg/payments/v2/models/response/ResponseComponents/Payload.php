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


namespace PhonePe\payments\v2\models\response\ResponseComponents;

class Payload
{
	public $merchantId;
	public $merchantOrderId;
	public $orderId;
	public $state;
	public $amount;
	public $expireAt;
	public $paymentDetails;

	/**
	 * @return mixed
	 */
	public function getMerchantId()
	{
		return $this->merchantId;
	}

	/**
	 * @return mixed
	 */
	public function getMerchantOrderId()
	{
		return $this->merchantOrderId;
	}

	/**
	 * @return mixed
	 */
	public function getOrderId()
	{
		return $this->orderId;
	}

	/**
	 * @return mixed
	 */
	public function getState()
	{
		return $this->state;
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
	public function getExpireAt()
	{
		return $this->expireAt;
	}

	/**
	 * @return mixed
	 */
	public function getPaymentDetails()
	{
		return $this->paymentDetails;
	}


}