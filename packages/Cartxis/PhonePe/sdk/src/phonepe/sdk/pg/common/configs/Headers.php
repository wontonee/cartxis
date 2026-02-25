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


namespace PhonePe\common\configs;

class Headers
{
	const ACCEPT_HEADER = "accept";
	const APPLICATION_JSON = "application/json";

	const SOURCE_PLATFORM_VERSION = "x-source-platform-version";
	const SDK_VERSION = "2.0.0";

	const SOURCE = "x-source";
	const INTEGRATION = "API";

	const SOURCE_PLATFORM = "x-source-platform";
	const SDK_TYPE = "BACKEND_PHP_SDK";

	const SOURCE_VERSION = "x-source-version";
	const API_VERSION = "V2";

	const CONTENT_TYPE = "Content-Type";
	const X_WWW_FORM_URLENCODED = "application/x-www-form-urlencoded";

	const AUTHORIZATION = "Authorization";
}