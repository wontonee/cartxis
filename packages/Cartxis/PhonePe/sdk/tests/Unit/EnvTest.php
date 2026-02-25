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


namespace Unit;

use PhonePe\common\configs\Constants;
use PhonePe\Env;
use PHPUnit\Framework\TestCase;

class EnvTest extends TestCase
{
	public function testGetBaseUrl(): void
	{
		$this->assertEquals(Constants::BASE_URL_PROD, Env::getBaseUrl(Env::PRODUCTION));
		$this->assertEquals(Constants::BASE_URL_UAT, Env::getBaseUrl(Env::UAT));
		$this->assertEquals('Invalid Environment', Env::getBaseUrl('test'));
	}

	public function testGetEventUrl(): void
	{
		$this->assertEquals(Constants::BASE_URL_PROD_EVENTS, Env::getEventsUrl(Env::PRODUCTION));
		$this->assertEquals(Constants::BASE_URL_UAT_EVENTS, Env::getEventsUrl(Env::UAT));
		$this->assertEquals('Invalid Environment', Env::getEventsUrl('test'));
	}
}