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


namespace Unit\common\utils;

use PhonePe\common\utils\HttpRequest;
use PHPUnit\Framework\TestCase;

class HttpRequestTest extends TestCase
{
	public function testBuildPostRequest(): void
	{
		$payload = 'foo';
		$path = '/api';
		$baseUrl = 'https://example.com';
		$additionalHeaders = ['Authorization: Bearer token'];

		$request = HttpRequest::buildPostRequest($payload, $path, $baseUrl, $additionalHeaders);

		$this->assertInstanceOf(HttpRequest::class, $request);
		$this->assertEquals('https://example.com/api', $request->getUrl());
		$this->assertEquals('foo', $request->getPayload());
		$this->assertEquals(['Authorization: Bearer token'], $request->getHeaders());
	}

	public function testBuildGetRequest(): void
	{
		$path = '/';
		$baseUrl = 'https://example.com';
		$additionalHeaders = ['Authorization: Bearer token'];

		$request = HttpRequest::buildGetRequest($path, $baseUrl, $additionalHeaders);

		$this->assertInstanceOf(HttpRequest::class, $request);
		$this->assertEquals('https://example.com/', $request->getUrl());
		$this->assertEquals(['Authorization: Bearer token'], $request->getHeaders());
	}
}