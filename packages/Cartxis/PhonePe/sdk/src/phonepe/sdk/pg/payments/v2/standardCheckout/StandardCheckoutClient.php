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


namespace PhonePe\payments\v2\standardCheckout;

use PhonePe\common\configs\Constants;
use PhonePe\common\configs\MerchantConfig;
use PhonePe\common\eventHandler\EventPublisher;
use PhonePe\common\exceptions\PhonePeException;
use PhonePe\common\tokenHandler\TokenService;
use PhonePe\common\utils\HttpRequest;
use PhonePe\common\utils\CurlHttpClient;
use PhonePe\Env;
use PhonePe\payments\v2\models\request\StandardCheckoutPayRequest;
use PhonePe\payments\v2\models\request\StandardCheckoutRefundRequest;
use PhonePe\payments\v2\models\response\CallbackResponse;
use PhonePe\payments\v2\models\response\RefundStatusCheckResponse;
use PhonePe\payments\v2\models\response\StandardCheckoutPayResponse;
use PhonePe\payments\v2\models\response\StandardCheckoutRefundResponse;
use PhonePe\payments\v2\models\response\StatusCheckResponse;
use PhonePe\common\configs;

class StandardCheckoutClient
{

	private static $instances = array();
	private MerchantConfig $merchantConfig;
	private TokenService $tokenService;
	private $httpClient;
	private string $hostUrl;
	private EventPublisher $eventPublisher;


	/**
	 * @param string $clientId
	 * @param int $clientVersion
	 * @param string $clientSecret
	 * @param string $env
	 * @param boolean $shouldPublishEvents
	 * @param Object $httpClient
	 */
	private function __construct($clientId, $clientVersion, $clientSecret, $env, $shouldPublishEvents, $httpClient)
	{
		$this->merchantConfig = new MerchantConfig($clientId, $clientVersion, $clientSecret);
		$this->tokenService =  new TokenService($this->merchantConfig, $env, $httpClient);
		$this->httpClient = $httpClient;
		$this->hostUrl = Env::getBaseUrl($env);
		$this->eventPublisher = new EventPublisher($this->tokenService, $this->httpClient, $env, $shouldPublishEvents);
	}

	/**
	 * @param string $clientId
	 * @param int $clientVersion
	 * @param string $clientSecret
	 * @param string $env
	 * @param boolean $shouldPublishEvents optional
	 * @param Object $httpClient optional
	 * @return StandardCheckoutClient
	 */
	public static function getInstance($clientId,
										 $clientVersion,
										 $clientSecret,
										 $env,
										 $shouldPublishEvents = false,
										 $httpClient = new CurlHttpClient()): StandardCheckoutClient
	{
		$httpClientName = get_class($httpClient);
		$args = array(
			$clientId,
			$clientVersion,
			$clientSecret,
			$shouldPublishEvents,
			$httpClientName
		);
		$hash_object = hash('md5', implode(',', $args));
		if (isset(self::$instances[$hash_object])===false) {
			self::$instances[$hash_object] = new self($clientId, $clientVersion, $clientSecret, $env, $shouldPublishEvents, $httpClient);
		}

		return self::$instances[$hash_object];
	}


	/**
	 * @return MerchantConfig
	 */
	public function getMerchantConfig(): MerchantConfig
	{
		return $this->merchantConfig;
	}

	/**
	 * @param StandardCheckoutPayRequest $standardCheckoutPayRequest
	 * @return StandardCheckoutPayResponse
	 */
	public function pay(StandardCheckoutPayRequest $standardCheckoutPayRequest){
		$payload = json_encode($standardCheckoutPayRequest);
		$path = StandardCheckoutConstants::STANDARD_CHECKOUT_PAY_API;

		$request = HttpRequest::buildPostRequest(
			$payload,
			$path,
			$this->getHostUrl(),
			$this->getHeaders(),
		);

		try {
			$httpResponseObj = $this->httpClient::postRequest($request->getUrl(), $request->getPayload(), $request->getHeaders());
			$httpResponse = json_decode($httpResponseObj->getResponse());
			return new StandardCheckoutPayResponse($httpResponse->orderId, $httpResponse->state, $httpResponse->redirectUrl, $httpResponse->expireAt);
		} catch (PhonePeException $phonePeException) {
			if($phonePeException->getHttpStatusCode() == 401){
				$this->tokenService->refreshToken();
			}
			throw $phonePeException;
		}
	}

	public function sendEvent($event)
	{
		try {
			return $this->eventPublisher->sendEvent($event);
		}
		catch (PhonePeException $phonePeException) {
				if($phonePeException->getHttpStatusCode() == 401){
					$this->tokenService->refreshToken();
				}
				throw $phonePeException;
			}
	}

	/**
	 * @param $merchantOrderId
	 * @param bool $withDetails
	 * @return StandardCheckoutPayResponse
	 */

	public function getOrderStatus($merchantOrderId, bool $withDetails = false): StatusCheckResponse{
		$withDetails ? $details = "true" : $details = "false";
		$path = Constants::ORDER_STATUS_ENDPOINT . "/" . $merchantOrderId . "/status?details=" . $details;
		$request = HttpRequest::buildGetRequest(
			$path,
			$this->getHostUrl(),
			$this->getHeaders()
		);
		try {
			$httpResponseObj = $this->httpClient::getRequest($request->getUrl(), $request->getHeaders());
			$httpResponse = json_decode($httpResponseObj->getResponse());
			$statusCheckResponse = StatusCheckResponse::getInstance($httpResponse);
			return $statusCheckResponse;

		} catch (PhonePeException $phonePeException) {
			if($phonePeException->getHttpStatusCode() == 401){
				$this->tokenService->refreshToken();
			}
			throw $phonePeException;
		}
	}

	/**
	 * @param $xPhonepeKeySignature
	 * @param $username
	 * @param $password
	 * @return mixed
	 */
	public function verifyCallbackResponse($headers, $body, $username, $password) {
		try{
			$authorization = $headers['authorization'];
			$string_to_be_hashed = $username . ":" . $password;
			if($authorization !== hash('sha256', $string_to_be_hashed))
				throw new PhonePeException("Invalid callback");
			$callbackResponse = CallbackResponse::getInstance($body);
			return $callbackResponse;
		}catch (PhonePeException $phonePeException) {
			throw $phonePeException;
		}
	}

	/**
	 * @param string $merchantRefundId
	 * @param string $originalMerchantOrderId
	 * @param $amount
	 * @return StandardCheckoutRefundResponse
	 */
	public function refund(StandardCheckoutRefundRequest $standardCheckoutRefundRequest){
		$payload = json_encode($standardCheckoutRefundRequest);
		$path = Constants::REFUND_ENDPOINT;
		$request = HttpRequest::buildPostRequest(
			$payload,
			$path,
			$this->getHostUrl(),
			$this->getHeaders(),
		);
		try{
			$httpResponseObj = $this->httpClient::postRequest($request->getUrl(), $request->getPayload(), $request->getHeaders());
			$httpResponse = json_decode($httpResponseObj->getResponse());
			return new StandardCheckoutRefundResponse($httpResponse->refundId, $httpResponse->amount, $httpResponse->state);
		}catch (PhonePeException $phonePeException) {
			if($phonePeException->getHttpStatusCode() == 401){
				$this->tokenService->refreshToken();
			}
			throw $phonePeException;
		}
	}

	public function getRefundStatus($merchantRefundId): RefundStatusCheckResponse{
		$path = Constants::REFUND_ENDPOINT . "/" . $merchantRefundId . "/status";
		$request = HttpRequest::buildGetRequest(
			$path,
			$this->getHostUrl(),
			$this->getHeaders()
		);
		try {
			$httpResponseObj = $this->httpClient::getRequest($request->getUrl(), $request->getHeaders());
			$httpResponse = json_decode($httpResponseObj->getResponse());
			$refundStatusCheckResponse = RefundStatusCheckResponse::getInstance($httpResponse);
			return $refundStatusCheckResponse;

		} catch (PhonePeException $phonePeException) {
			if($phonePeException->getHttpStatusCode() == 401){
				$this->tokenService->refreshToken();
			}
			throw $phonePeException;
		}
	}

	public function getAuthHeadersToken()
	{
		try{
			return $this->tokenService->getAuthHeaders();
		}catch (PhonePeException $phonePeException) {
			if ($phonePeException->getHttpStatusCode() == 401) {
				$this->tokenService->refreshToken();
			}
			throw $phonePeException;
		}
	}

	/**
	 * @return string
	 */
	public function getHostUrl(): string
	{
		return $this->hostUrl;
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