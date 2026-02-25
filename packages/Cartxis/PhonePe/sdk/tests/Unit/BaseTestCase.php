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

namespace Tests\Unit;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PhonePe\common\configs\MerchantConfig;
use PhonePe\common\tokenHandler\OAuthToken;
use PhonePe\common\tokenHandler\TokenService;
use PhonePe\common\utils\HttpResponse;
use Tests\Fixtures\TestDataProvider;

/**
 * Base test case with common utilities and helper methods
 */
abstract class BaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a mock MerchantConfig with test data
     */
    protected function createMockMerchantConfig(): MerchantConfig
    {
        $config = $this->createMock(MerchantConfig::class);
        $config->method('getClientId')->willReturn(TestDataProvider::TEST_CLIENT_ID);
        $config->method('getClientVersion')->willReturn(TestDataProvider::TEST_CLIENT_VERSION);
        $config->method('getClientSecret')->willReturn(TestDataProvider::TEST_CLIENT_SECRET);
        
        return $config;
    }

    /**
     * Create a mock TokenService
     */
    protected function createMockTokenService(): MockObject
    {
        $tokenService = $this->createMock(TokenService::class);
        $tokenService->method('getAuthHeaders')
                    ->willReturn('O-Bearer ' . TestDataProvider::TEST_ACCESS_TOKEN);
        
        return $tokenService;
    }

    /**
     * Create a mock OAuthToken
     */
    protected function createMockOAuthToken(): OAuthToken
    {
        $tokenData = TestDataProvider::getOAuthTokenData();
        
        return new OAuthToken(
            $tokenData['access_token'],
            $tokenData['encrypted_access_token'],
            $tokenData['refresh_token'],
            $tokenData['expires_in'],
            $tokenData['issued_at'],
            $tokenData['expires_at'],
            $tokenData['session_expires_at'],
            $tokenData['token_type']
        );
    }

    /**
     * Create a successful HTTP response with given data
     */
    protected function createSuccessfulHttpResponse(array $data): HttpResponse
    {
        return new HttpResponse(200, ['Content-Type' => 'application/json'], json_encode($data));
    }

    /**
     * Create an error HTTP response
     */
    protected function createErrorHttpResponse(int $statusCode, string $message, ?string $code = null): HttpResponse
    {
        $errorData = [
            'message' => $message,
            'code' => $code ?? 'ERROR_' . $statusCode,
            'data' => ['context' => 'test_error']
        ];
        
        return new HttpResponse($statusCode, ['Content-Type' => 'application/json'], json_encode($errorData));
    }

    /**
     * Assert that an array has the expected structure
     */
    protected function assertArrayStructure(array $expected, array $actual): void
    {
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $actual, "Key '$key' not found in actual array");
            
            if (is_array($value)) {
                $this->assertIsArray($actual[$key], "Key '$key' should be an array");
                $this->assertArrayStructure($value, $actual[$key]);
            }
        }
    }

    /**
     * Assert that a JSON string contains expected data
     */
    protected function assertJsonContains(array $expected, string $json): void
    {
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded, 'JSON should decode to an array');
        
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $decoded);
            $this->assertEquals($value, $decoded[$key]);
        }
    }

    /**
     * Assert that an object has the expected properties and values
     */
    protected function assertObjectProperties(array $expectedProperties, object $object): void
    {
        foreach ($expectedProperties as $property => $expectedValue) {
            $getter = 'get' . ucfirst($property);
            
            $this->assertTrue(
                method_exists($object, $getter),
                "Object should have getter method '$getter'"
            );
            
            $actualValue = $object->$getter();
            $this->assertEquals(
                $expectedValue,
                $actualValue,
                "Property '$property' should have value '$expectedValue', got '$actualValue'"
            );
        }
    }

    /**
     * Create a Mockery mock for static method calls
     */
    protected function createStaticMock(string $className): MockInterface
    {
        return Mockery::mock('alias:' . $className);
    }

    /**
     * Get reflection property value from an object
     */
    protected function getPrivateProperty(object $object, string $propertyName)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        
        return $property->getValue($object);
    }

    /**
     * Set reflection property value on an object
     */
    protected function setPrivateProperty(object $object, string $propertyName, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Call a private method on an object
     */
    protected function callPrivateMethod(object $object, string $methodName, array $args = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $args);
    }
}
