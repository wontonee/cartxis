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

class CreditLinePaymentInstrument extends Instrument
{
	public string	$ifsc;
	public string $accountHolderName;

	/**
	 * @param string $ifsc
	 * @param string $accountHolderName
	 */
	public function __construct(string $ifsc, string $accountHolderName)
	{
		$this->ifsc = $ifsc;
		$this->accountHolderName = $accountHolderName;
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