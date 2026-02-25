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

use Mockery\Exception;
use PhonePe\common\exceptions\PhonePeException;
use PhonePe\common\tokenHandler\TokenService;
use PhonePe\common\configs;
use PhonePe\common\utils\HttpRequest;
use PhonePe\Env;


class EventPublisher
{
	private bool $shouldPublishEvents;
	private TokenService $tokenService;
	private $env;
	private $httpClient;


	public function setShouldPublishEvents(bool $shouldPublishEvents){
		$this->shouldPublishEvents = $shouldPublishEvents;
	}

	public function __construct(TokenService $tokenService, $httpClient, $env ,$shouldPublishEvents)
	{
		$this->tokenService = $tokenService;
		$this->httpClient = $httpClient;
		$this->env = $env;
		$this->shouldPublishEvents = $shouldPublishEvents;
	}

	public function sendEvent($event){
		if($this->shouldPublishEvents){
			$events = array($event);
			$payload = json_encode(
				array(
					'events' => $events,
					'source' => configs\Constants::BACKEND_SDK,
					'clientVersion' => configs\Constants::EVENTS_CLIENT_VERSION
			)
			);

			$path = configs\Constants::EVENTS_ENDPOINT;

			$request = HttpRequest::buildPostRequest(
				$payload,
				$path,
				$this->getHostUrl(),
				$this->getHeaders(),
			);

			try{
				$httpResponseObj = $this->httpClient::postRequest($request->getUrl(), $request->getPayload(), $request->getHeaders());
				$httpResponse = json_decode($httpResponseObj->getResponse());
				return $httpResponse;
			}catch (PhonePeException $phonePeException) {
				throw $phonePeException;
			}

		}
	}

	public function getHostUrl(){
		return Env::getEventsUrl($this->env);
	}

	public function getHeaders(): array
	{
		$auth_headers = $this->tokenService->getAuthHeaders();
		$headers = array();
		$headers[configs\Headers::ACCEPT_HEADER] = configs\Headers::APPLICATION_JSON;
		$headers[configs\Headers::SOURCE] = configs\Headers::INTEGRATION;
		$headers[configs\Headers::SOURCE_VERSION] = configs\Headers::API_VERSION;
		$headers[configs\Headers::SOURCE_PLATFORM_VERSION] = configs\Headers::SDK_VERSION;
		$headers[configs\Headers::SOURCE_PLATFORM] = configs\Headers::SDK_TYPE;
		$headers[configs\Headers::CONTENT_TYPE] = configs\Headers::APPLICATION_JSON;
		$headers[configs\Headers::AUTHORIZATION] = $auth_headers;
		return $headers;
	}
}