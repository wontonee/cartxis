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

use PhonePe\common\configs\Headers;
class HttpRequest
{

	public array $headers;
	public string $url;
	public string $payload;

	/**
	 * @return mixed
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @return mixed
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return mixed
	 */
	public function getPayload()
	{
		return $this->payload;
	}

	/**
	 * @param string $payload
	 * @param string $path
	 * @param string $baseUrl
	 * @param $headers
	 * @return HttpRequest
	 */
	public static function buildPostRequest($payload, $path, $baseUrl, $headers): HttpRequest
	{
		$request = new HttpRequest();
		$request->url = ($baseUrl . $path);
		$request->payload = $payload;
		$request->headers = $headers;
		return $request;
	}

	/**
	 * @param string $path
	 * @param string $baseUrl
	 * @param array $headers
	 * @return HttpRequest
	 */
	public static function buildGetRequest($path, $baseUrl, $headers): HttpRequest
	{
		$request = new HttpRequest();
		$request->url = ($baseUrl . $path);
		$request->headers = $headers;
		return $request;
	}


}