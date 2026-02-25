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

class PgRail extends Rail
{
	public string $transactionId;
	public string $authorizationCode;
	public string $serviceTransactionId;

	/**
	 * @param string $transactionId
	 * @param string $authorizationCode
	 * @param string $serviceTransactionId
	 */
	public function __construct(string $transactionId, string $authorizationCode, string $serviceTransactionId)
	{
		$this->type = Rails::PG;
		$this->transactionId = $transactionId;
		$this->authorizationCode = $authorizationCode;
		$this->serviceTransactionId = $serviceTransactionId;
	}

	public function getTransactionId(): string
	{
		return $this->transactionId;
	}

	public function getAuthorizationCode(): string
	{
		return $this->authorizationCode;
	}

	public function getServiceTransactionId(): string
	{
		return $this->serviceTransactionId;
	}
	
}