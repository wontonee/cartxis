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

class Constants
{
	# URLs and Endpoints
	const BASE_URL_PROD = "https://api.phonepe.com/apis/pg";
	const BASE_URL_UAT = "https://api-preprod.phonepe.com/apis/pg-sandbox";
	const BASE_URL_PROD_FOR_OAUTH = "https://api.phonepe.com/apis";
	const BASE_URL_STAGE_FOR_OAUTH = "https://api-testing.phonepe.com/apis";
	const BASE_URL_UAT_FOR_OAUTH = "https://api-preprod.phonepe.com/apis/pg-sandbox";
	const BASE_URL_PROD_EVENTS = "https://api.phonepe.com/apis/pg-ingestion";
	const BASE_URL_UAT_EVENTS = "https://api-preprod.phonepe.com/apis/pg-sandbox";
    const OAUTH_ENDPOINT_PROD = "/identity-manager/v1/oauth/token";
    const OAUTH_ENDPOINT_UAT = "/v1/oauth/token";
	const ORDER_STATUS_ENDPOINT = "/checkout/v2/order";
	const REFUND_ENDPOINT = "/payments/v2/refund";

	# OAuth
	const CLIENT_CREDENTIALS = "client_credentials";
	const O_BEARER = "O-Bearer";

	# Other
	const BACKEND_SDK = "BACKEND_SDK";
	const EVENTS_CLIENT_VERSION = "PHP_SDK:2.0.0";

	const EVENTS_ENDPOINT = "/client/v1/backend/events/batch";

}