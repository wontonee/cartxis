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


namespace PhonePe\common\eventHandler;

class Event
{
	public string $merchantOrderId;
	public string $eventName;
	public string $eventTime;
	public array $data;

	public function __construct()
	{
	}

	public function setMerchantOrderId(string $merchantOrderId): void
	{
		$this->merchantOrderId = $merchantOrderId;
	}

	public function setEventName(string $eventName): void
	{
		$this->eventName = $eventName;
	}

	public function setEventTime(string $eventTime): void
	{
		$this->eventTime = $eventTime;
	}

	public function setData(array $data): void
	{
		$this->data = $data;
	}



}