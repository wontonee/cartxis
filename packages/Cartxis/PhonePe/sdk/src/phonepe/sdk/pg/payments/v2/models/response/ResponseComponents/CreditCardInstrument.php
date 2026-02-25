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

use PhonePe\common\configs\Instruments;

class CreditCardInstrument extends Instrument
{
	public string $bankTransactionId;
	public string $bankId;
	public string $arn;
	public string $brn;


	/**
	 * @param string $bankTransactionId
	 * @param string $bankId
	 * @param string $arn
	 * @param string $brn
	 */
	public function __construct(string $bankTransactionId, string $bankId, string $arn, string $brn)
	{
		$this->type = Instruments::CREDIT_CARD;
		$this->bankTransactionId = $bankTransactionId;
		$this->bankId = $bankId;
		$this->arn = $arn;
		$this->brn = $brn;
	}

	public function getBankTransactionId(): string
	{
		return $this->bankTransactionId;
	}

	public function getBankId(): string
	{
		return $this->bankId;
	}

	public function getArn(): string
	{
		return $this->arn;
	}

	public function getBrn(): string
	{
		return $this->brn;
	}


}