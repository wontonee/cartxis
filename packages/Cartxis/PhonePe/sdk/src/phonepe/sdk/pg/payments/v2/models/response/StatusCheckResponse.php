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
use PhonePe\common\configs\Constants;
use PhonePe\payments\v2\models\response\ResponseComponents\AccountInstrument;
use PhonePe\payments\v2\models\response\ResponseComponents\CreditCardInstrument;
use PhonePe\payments\v2\models\response\ResponseComponents\DebitCardInstrument;
use PhonePe\payments\v2\models\response\ResponseComponents\MetaInfo;
use PhonePe\payments\v2\models\response\ResponseComponents\NetBankingInstrument;
use PhonePe\payments\v2\models\response\ResponseComponents\PaymentDetail;
use PhonePe\payments\v2\models\response\ResponseComponents\PgRail;
use PhonePe\payments\v2\models\response\ResponseComponents\UpiRail;
use PhpParser\Node\Expr\FuncCall;

class StatusCheckResponse implements \JsonSerializable
{
	public string $orderId;
	public string $state;
	public int $amount;
	public int $expireAt;
	public MetaInfo $metaInfo;
	public string $errorCode;
	public string $detailedErrorCode;
	public $paymentDetails;

	public static function getInstance($httpResponse): StatusCheckResponse
	{
		$mapper = new JsonMapper();
        $mapper->bEnforceMapType = false;
		$statusCheckResponse = $mapper->map($httpResponse, new StatusCheckResponse());
		return $statusCheckResponse;

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
	 * @return int
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @return int
	 */
	public function getExpireAt()
	{
		return $this->expireAt;
	}

	/**
	 * @return MetaInfo
	 */
	public function getMetaInfo()
	{
		return $this->metaInfo;
	}

	/**
	 * @return array
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
