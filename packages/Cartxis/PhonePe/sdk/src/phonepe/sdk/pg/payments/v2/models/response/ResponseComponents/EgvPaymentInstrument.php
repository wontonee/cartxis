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

class EgvPaymentInstrument extends Instrument
{
	public string $cardNumber;
	public string $programId;

	/**
	 * @param string $cardNumber
	 * @param string $programId
	 */
	public function __construct(string $cardNumber, string $programId)
	{
		$this->type = Instruments::EGV;
		$this->cardNumber = $cardNumber;
		$this->programId = $programId;
	}

	public function getCardNumber(): string
	{
		return $this->cardNumber;
	}

	public function getProgramId(): string
	{
		return $this->programId;
	}


}