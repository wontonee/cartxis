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

namespace Tests\Unit\common\TokenHandler;

use Mockery;
use Mockery\MockInterface;
use PhonePe\common\configs\Constants;
use PhonePe\common\configs\MerchantConfig;
use PhonePe\common\exceptions\PhonePeException;
use PhonePe\common\tokenHandler\OAuthToken;
use PhonePe\common\tokenHandler\TokenService;
use PhonePe\common\utils\CurlHttpClient;
use Tests\Fixtures\TestDataProvider;
use Tests\Unit\BaseTestCase;

class TokenServiceTest extends BaseTestCase
{
    private MockInterface $mockHttpClient;
    private MerchantConfig $mockMerchantConfig;
    private TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockMerchantConfig = $this->createMockMerchantConfig();
        $this->mockHttpClient = $this->createStaticMock(CurlHttpClient::class);
        
        $this->tokenService = new TokenService(
            $this->mockMerchantConfig,
            TestDataProvider::TEST_ENV,
            $this->mockHttpClient
        );
    }

    public function testPrepareOAuthTokenSuccess(): void
    {
        $tokenData = TestDataProvider::getOAuthTokenData();
        $httpResponse = $this->createSuccessfulHttpResponse($tokenData);
        
        $this->mockHttpClient
            ->shouldReceive('postRequest')
            ->once()
            ->andReturn($httpResponse);

        $result = $this->tokenService->prepareOAuthToken();

        $this->assertInstanceOf(OAuthToken::class, $result);
        $this->assertEquals($tokenData['access_token'], $result->getAccessToken());
        $this->assertEquals($tokenData['token_type'], $result->getTokenType());
        $this->assertEquals($tokenData['expires_in'], $result->getExpiresIn());
    }

    public function testGetAuthHeadersWithValidToken(): void
    {
        $tokenData = TestDataProvider::getOAuthTokenData();
        $httpResponse = $this->createSuccessfulHttpResponse($tokenData);
        
        $this->mockHttpClient
            ->shouldReceive('postRequest')
            ->once()
            ->andReturn($httpResponse);

        $authHeaders = $this->tokenService->getAuthHeaders();

        $this->assertStringStartsWith(Constants::O_BEARER . ' ', $authHeaders);
        $this->assertStringContainsString($tokenData['access_token'], $authHeaders);
    }

    public function testSetAuthTokenInHeadersCreatesNewToken(): void
    {
        $tokenData = TestDataProvider::getOAuthTokenData();
        $httpResponse = $this->createSuccessfulHttpResponse($tokenData);
        
        $this->mockHttpClient
            ->shouldReceive('postRequest')
            ->once()
            ->andReturn($httpResponse);

        $token = $this->callPrivateMethod($this->tokenService, 'setAuthTokenInHeaders');

        $this->assertEquals($tokenData['access_token'], $token);
    }

    public function testSetAuthTokenInHeadersReusesValidToken(): void
    {
        // First call - creates token
        $tokenData = TestDataProvider::getOAuthTokenData();
        $httpResponse = $this->createSuccessfulHttpResponse($tokenData);
        
        $this->mockHttpClient
            ->shouldReceive('postRequest')
            ->once()
            ->andReturn($httpResponse);

        $firstToken = $this->callPrivateMethod($this->tokenService, 'setAuthTokenInHeaders');
        
        // Second call - should reuse token (no additional HTTP call)
        $secondToken = $this->callPrivateMethod($this->tokenService, 'setAuthTokenInHeaders');
        
        $this->assertEquals($firstToken, $secondToken);
        $this->assertEquals($tokenData['access_token'], $secondToken);
    }

    public function testSetAuthTokenInHeadersRefreshesExpiredToken(): void
    {
        // Create an expired token
        $expiredTokenData = TestDataProvider::getOAuthTokenData();
        $expiredTokenData['issued_at'] = time() - 3700; // More than an hour ago
        $expiredTokenData['expires_at'] = time() - 100; // Expired
        
        $expiredResponse = $this->createSuccessfulHttpResponse($expiredTokenData);
        
        // New fresh token
        $freshTokenData = TestDataProvider::getOAuthTokenData();
        $freshTokenData['access_token'] = 'fresh_access_token';
        $freshResponse = $this->createSuccessfulHttpResponse($freshTokenData);
        
        $this->mockHttpClient
            ->shouldReceive('postRequest')
            ->twice() // Once for expired, once for fresh
            ->andReturn($expiredResponse, $freshResponse);

        // First call creates expired token
        $firstToken = $this->callPrivateMethod($this->tokenService, 'setAuthTokenInHeaders');
        
        // Second call should refresh the token
        $secondToken = $this->callPrivateMethod($this->tokenService, 'setAuthTokenInHeaders');
        
        $this->assertNotEquals($firstToken, $secondToken);
        $this->assertEquals('fresh_access_token', $secondToken);
    }

    public function testRefreshToken(): void
    {
        $tokenData = TestDataProvider::getOAuthTokenData();
        $httpResponse = $this->createSuccessfulHttpResponse($tokenData);
        
        $this->mockHttpClient
            ->shouldReceive('postRequest')
            ->once()
            ->andReturn($httpResponse);

        $this->tokenService->refreshToken();
        
        $cachedToken = $this->tokenService->getCachedTokenData();
        $this->assertInstanceOf(OAuthToken::class, $cachedToken);
        $this->assertEquals($tokenData['access_token'], $cachedToken->getAccessToken());
    }

    public function testPrepareOAuthTokenWithHttpError(): void
    {
        $this->expectException(PhonePeException::class);
        $this->expectExceptionMessage('Unauthorized access');
        
        $this->mockHttpClient
            ->shouldReceive('postRequest')
            ->once()
            ->andThrow(new PhonePeException('Unauthorized access', 401, 'UNAUTHORIZED'));

        $this->tokenService->prepareOAuthToken();
    }

    public function testPreparePayloadStructure(): void
    {
        $payload = $this->callPrivateMethod($this->tokenService, 'preparePayload');
        
        $this->assertIsString($payload);
        
        // Parse the payload
        parse_str($payload, $parsedData);
        
        $this->assertArrayHasKey('client_id', $parsedData);
        $this->assertArrayHasKey('client_version', $parsedData);
        $this->assertArrayHasKey('client_secret', $parsedData);
        $this->assertArrayHasKey('grant_type', $parsedData);
        
        $this->assertEquals(TestDataProvider::TEST_CLIENT_ID, $parsedData['client_id']);
        $this->assertEquals(TestDataProvider::TEST_CLIENT_VERSION, $parsedData['client_version']);
        $this->assertEquals(TestDataProvider::TEST_CLIENT_SECRET, $parsedData['client_secret']);
        $this->assertEquals(Constants::CLIENT_CREDENTIALS, $parsedData['grant_type']);
    }

    public function testPrepareHeaders(): void
    {
        $headers = $this->callPrivateMethod($this->tokenService, 'prepareHeaders');
        
        $this->assertIsArray($headers);
        $this->assertContains('Content-type: application/x-www-form-urlencoded', $headers);
    }

    public function testGetCachedTokenData(): void
    {
        $tokenData = TestDataProvider::getOAuthTokenData();
        $httpResponse = $this->createSuccessfulHttpResponse($tokenData);
        
        $this->mockHttpClient
            ->shouldReceive('postRequest')
            ->once()
            ->andReturn($httpResponse);

        // Initially should be null
        $this->assertNull($this->tokenService->getCachedTokenData());
        
        // Trigger token creation via setAuthTokenInHeaders (which caches the token)
        $this->callPrivateMethod($this->tokenService, 'setAuthTokenInHeaders');
        
        // Now should return the token
        $cachedToken = $this->tokenService->getCachedTokenData();
        $this->assertInstanceOf(OAuthToken::class, $cachedToken);
        $this->assertEquals($tokenData['access_token'], $cachedToken->getAccessToken());
    }

    public function testSetCachedTokenData(): void
    {
        $mockToken = $this->createMockOAuthToken();
        
        $this->tokenService->setCachedTokenData($mockToken);
        
        $retrievedToken = $this->tokenService->getCachedTokenData();
        $this->assertSame($mockToken, $retrievedToken);
    }

    /**
     * @dataProvider environmentDataProvider
     */
    public function testTokenServiceWithDifferentEnvironments(string $environment): void
    {
        $mockConfig = $this->createMockMerchantConfig();
        $mockHttpClient = $this->createStaticMock(CurlHttpClient::class);
        
        $tokenService = new TokenService($mockConfig, $environment, $mockHttpClient);
        
        $this->assertInstanceOf(TokenService::class, $tokenService);
    }

    public static function environmentDataProvider(): array
    {
        return TestDataProvider::environmentDataProvider();
    }

    public function testTokenExpiryCalculation(): void
    {
        $currentTime = time();
        $tokenData = TestDataProvider::getOAuthTokenData();
        $tokenData['issued_at'] = $currentTime - 2700; // 45 minutes ago
        $tokenData['expires_at'] = $currentTime + 900; // 15 minutes from now
        $tokenData['expires_in'] = 3600; // 1 hour total
        
        $httpResponse = $this->createSuccessfulHttpResponse($tokenData);
        
        // Should refresh because less than half TTL remaining (15 min < 30 min)
        $freshTokenData = TestDataProvider::getOAuthTokenData();
        $freshTokenData['access_token'] = 'refreshed_token';
        $freshResponse = $this->createSuccessfulHttpResponse($freshTokenData);
        
        $this->mockHttpClient
            ->shouldReceive('postRequest')
            ->twice() // First for token that needs refresh, second for refresh
            ->andReturn($httpResponse, $freshResponse);

        // First call - creates token that needs refresh
        $firstToken = $this->callPrivateMethod($this->tokenService, 'setAuthTokenInHeaders');
        
        // Second call - should refresh due to TTL logic
        $secondToken = $this->callPrivateMethod($this->tokenService, 'setAuthTokenInHeaders');
        
        $this->assertEquals('refreshed_token', $secondToken);
    }

    public function testMultipleEnvironmentTokenServices(): void
    {
        $mockConfig = $this->createMockMerchantConfig();
        $mockHttpClient = $this->createStaticMock(CurlHttpClient::class);
        
        $prodService = new TokenService($mockConfig, 'PRODUCTION', $mockHttpClient);
        $uatService = new TokenService($mockConfig, 'UAT', $mockHttpClient);
        $stageService = new TokenService($mockConfig, 'STAGE', $mockHttpClient);
        
        $this->assertInstanceOf(TokenService::class, $prodService);
        $this->assertInstanceOf(TokenService::class, $uatService);
        $this->assertInstanceOf(TokenService::class, $stageService);
    }
}
