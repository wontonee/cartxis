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

use PhonePe\common\configs\Rails;

class UpiRail extends Rail
{
	public string $utr;
	public string $upiTransactionId;
	public string $vpa;

	/**
	 * @param string $utr
	 * @param string $upiTransactionId
	 * @param string $vpa
	 */
	public function __construct(string $utr, string $upiTransactionId, string $vpa)
	{
		$this->type = Rails::UPI;
		$this->utr = $utr;
		$this->upiTransactionId = $upiTransactionId;
		$this->vpa = $vpa;
	}

	public function getUtr(): string
	{
		return $this->utr;
	}

	public function getUpiTransactionId(): string
	{
		return $this->upiTransactionId;
	}

	public function getVpa(): string
	{
		return $this->vpa;
	}

}