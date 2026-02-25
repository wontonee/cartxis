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


namespace PhonePe\payments\v2\models\request\builders;

use PhonePe\payments\v2\models\request\StandardCheckoutRefundRequest;

class StandardCheckoutRefundRequestBuilder
{
	private string $merchantRefundId;
	private string $originalMerchantOrderId;
	private $amount;

	public  function  merchantRefundId($merchantRefundId): StandardCheckoutRefundRequestBuilder
	{
		$this->merchantRefundId = $merchantRefundId;
		return $this;
	}

	public function originalMerchantOrderId($originalMerchantOrderId): StandardCheckoutRefundRequestBuilder
	{
		$this->originalMerchantOrderId =$originalMerchantOrderId;
		return $this;
	}

	public function amount($amount): StandardCheckoutRefundRequestBuilder
	{
		$this->amount = $amount;
		return $this;
	}

	public static function builder(): StandardCheckoutRefundRequestBuilder
	{
		return new StandardCheckoutRefundRequestBuilder();
	}

	public function build(): StandardCheckoutRefundRequest
	{
		return new StandardCheckoutRefundRequest(
			$this->merchantRefundId,
			$this->originalMerchantOrderId,
			$this->amount
		);
	}
}