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

use AllowDynamicProperties;

#[AllowDynamicProperties] class PaymentDetail
{
	public string $paymentMode;
	public $timestamp;
	public int $amount;
	public string $transactionId;
	public string $state;
	public string $errorCode;
	public string $detailedErrorCode;
	public Rail $rail;
	public Instrument $instrument;
	public $splitInstruments = array();

	public function getPaymentMode(): string
	{
		return $this->paymentMode;
	}

	/**
	 * @return mixed
	 */
	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function getAmount(): int
	{
		return $this->amount;
	}

	public function getTransactionId(): string
	{
		return $this->transactionId;
	}

	public function getState(): string
	{
		return $this->state;
	}

	public function getErrorCode(): string
	{
		return $this->errorCode;
	}

	public function getDetailedErrorCode(): string
	{
		return $this->detailedErrorCode;
	}

	public function getRail(): Rail
	{
		return $this->rail;
	}

	public function getInstrument(): Instrument
	{
		return $this->instrument;
	}

	public function getSplitInstruments(): array
	{
		return $this->splitInstruments;
	}



}