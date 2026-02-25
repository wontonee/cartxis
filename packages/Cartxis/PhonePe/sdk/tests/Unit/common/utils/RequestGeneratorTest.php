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

namespace Tests\Unit\common\utils;

use PhonePe\common\utils\HttpResponse;
use Tests\Unit\BaseTestCase;

class RequestGeneratorTest extends BaseTestCase
{
    public function testHttpResponseCreation(): void
    {
        $statusCode = 200;
        $headers = ['Content-Type' => 'application/json'];
        $responseBody = '{"test": "value"}';
        
        $httpResponse = new HttpResponse($statusCode, $headers, $responseBody);
        
        $this->assertInstanceOf(HttpResponse::class, $httpResponse);
        $this->assertEquals($statusCode, $httpResponse->getStatusCode());
        $this->assertEquals($responseBody, $httpResponse->getResponse());
    }
    
    public function testHttpResponseWithDifferentStatusCodes(): void
    {
        $testCases = [
            [200, 'Success'],
            [400, 'Bad Request'], 
            [401, 'Unauthorized'],
            [404, 'Not Found'],
            [500, 'Internal Server Error']
        ];
        
        foreach ($testCases as [$statusCode, $message]) {
            $response = new HttpResponse($statusCode, [], json_encode(['message' => $message]));
            
            $this->assertEquals($statusCode, $response->getStatusCode());
            $this->assertStringContainsString($message, $response->getResponse());
        }
    }
    
    public function testHttpResponseWithHeaders(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer token123',
            'X-Request-ID' => 'req-456'
        ];
        
        $response = new HttpResponse(200, $headers, '{}');
        
        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testHttpResponseWithEmptyBody(): void
    {
        $response = new HttpResponse(204, [], '');
        
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('', $response->getResponse());
    }
    
    public function testHttpResponseWithLargeBody(): void
    {
        $largeBody = str_repeat('a', 10000);
        $response = new HttpResponse(200, [], $largeBody);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($largeBody, $response->getResponse());
        $this->assertEquals(10000, strlen($response->getResponse()));
    }
}
