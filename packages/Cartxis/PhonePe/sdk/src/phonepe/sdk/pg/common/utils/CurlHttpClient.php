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


namespace PhonePe\common\utils;

use PhonePe\common\exceptions\PhonePeException;

class CurlHttpClient
{
	/**
	 * @desc Helper function to send a post request
	 * @param string $url
	 * @param string $body
	 * @param $headers
	 * @return mixed
	 * @throws PhonePeException
	 */
	public static function postRequest($url, $body, $headers)
	{
		$headers_array = [];
		foreach ($headers as $key => $value) {
			$headers_array[] = $key . ":" . $value;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER , $headers_array);

		$response = curl_exec($ch);
		$responseHeaders = curl_getinfo($ch);
		$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpStatus == 200)
			return new HttpResponse($httpStatus, $responseHeaders, $response);
		else {
			$responseArray = json_decode($response, true);
			$data = $responseArray['data'] ?? $responseArray['context'];
			$code = $responseArray['code'] ?? $responseArray['errorCode'];
			throw new PhonePeException($responseArray['message'], $httpStatus, $code, $data);
		}
	}

	/**
	 * @desc Helper function to send a get request
	 * @param string $url
	 * @param string $headers
	 * @return mixed
	 * @throws PhonePeException
	 */
	public static function getRequest($url, $headers)
	{
		$headers_array = [];
		foreach ($headers as $key => $value) {
			$headers_array[] = $key . ":" . $value;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_array);

		$response = curl_exec($ch);
		$responseHeaders = curl_getinfo($ch);
		$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpStatus == 200)
			return new HttpResponse($httpStatus, $responseHeaders, $response);
		else {
			$responseArray = json_decode($response, true);
			$data = $responseArray['data'] ?? $responseArray['context'];
			$code = $responseArray['code'] ?? $responseArray['errorCode'];
			throw new PhonePeException($responseArray['message'], $httpStatus, $code, $data);
		}
	}

}