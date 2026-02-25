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

namespace PhonePe;

use PhonePe\common\configs\Constants;

class Env
{
	const STAGE = "STAGE";
	const UAT = "UAT";
	const PRODUCTION = "PRODUCTION";

	/**
	 * @param string $env
	 * @return string
	 */
	public static function getBaseUrl($env): string
	{
		switch ($env) {
			case Env::PRODUCTION:
				return Constants::BASE_URL_PROD;
			case Env::UAT:
				return Constants::BASE_URL_UAT;
			default: return "Invalid Environment";
		}
	}

	/**
	 * @param string $env
	 * @return string
	 */
	public static function getEventsUrl($env): string
	{
		switch ($env) {
			case Env::PRODUCTION:
				return Constants::BASE_URL_PROD_EVENTS;
			case Env::UAT:
				return Constants::BASE_URL_UAT_EVENTS;
			default:
				return "Invalid Environment";
		}
	}

	public static function getBaseUrlForOAuth($env): string
	{
		switch ($env) {
			case Env::PRODUCTION:
				return Constants::BASE_URL_PROD_FOR_OAUTH;
			case Env::UAT:
				return Constants::BASE_URL_UAT_FOR_OAUTH;
			case Env::STAGE:
				return Constants::BASE_URL_STAGE_FOR_OAUTH;
			default:
				return "Invalid Environment";
		}
	}

    public static function getApiPathForOAuth($env): string
    {
        switch ($env) {
            case Env::PRODUCTION:
                return Constants::OAUTH_ENDPOINT_PROD;
            case Env::UAT:
                return Constants::OAUTH_ENDPOINT_UAT;
            default:
                return "Invalid Environment";
        }
    }
}