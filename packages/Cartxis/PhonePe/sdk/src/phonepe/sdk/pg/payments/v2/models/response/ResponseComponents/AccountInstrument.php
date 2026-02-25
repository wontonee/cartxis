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

class AccountInstrument extends Instrument
{
	public string $accountType;
	public string $maskedAccountNumber;
	public string $ifsc;
	public string $accountHolderName;

	/**
	 * @param string $accountType
	 * @param string $maskedAccountNumber
	 * @param string $ifsc
	 * @param string $accountHolderName
	 */
	public function __construct(string $accountType, string $maskedAccountNumber, string $ifsc, string $accountHolderName)
	{
		$this->type = Instruments::ACCOUNT;
		$this->accountType = $accountType;
		$this->maskedAccountNumber = $maskedAccountNumber;
		$this->ifsc = $ifsc;
		$this->accountHolderName = $accountHolderName;
	}

	public function getAccountType(): string
	{
		return $this->accountType;
	}

	public function getMaskedAccountNumber(): string
	{
		return $this->maskedAccountNumber;
	}

	public function getIfsc(): string
	{
		return $this->ifsc;
	}

	public function getAccountHolderName(): string
	{
		return $this->accountHolderName;
	}

}