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

namespace Tests\Unit\payments\v2\standardCheckout;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PhonePe\common\exceptions\PhonePeException;
use PhonePe\common\tokenHandler\TokenService;
use PhonePe\common\utils\CurlHttpClient;
use PhonePe\common\utils\HttpResponse;
use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;
use PhonePe\payments\v2\models\request\StandardCheckoutPayRequest;
use PhonePe\payments\v2\models\request\StandardCheckoutRefundRequest;
use PhonePe\payments\v2\models\response\CallbackResponse;
use PhonePe\payments\v2\models\response\RefundStatusCheckResponse;
use PhonePe\payments\v2\models\response\StandardCheckoutPayResponse;
use PhonePe\payments\v2\models\response\StandardCheckoutRefundResponse;
use PhonePe\payments\v2\models\response\StatusCheckResponse;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;
use Tests\Fixtures\TestDataProvider;
use Tests\Unit\BaseTestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class StandardCheckoutClientTest extends BaseTestCase
{
    private StandardCheckoutClient $client;
    private MockInterface $mockHttpClient;
    private MockObject $mockTokenService;

    public function setUp(): void
    {
        parent::setUp();

        // Mock the HTTP Client as an alias for static calls
        $this->mockHttpClient = $this->createStaticMock('PhonePe\common\utils\CurlHttpClient');

        // Mock the TokenService to avoid its internal logic
        $this->mockTokenService = $this->createMock(TokenService::class);
        $this->mockTokenService->method('getAuthHeaders')
            ->willReturn('O-Bearer ' . TestDataProvider::TEST_ACCESS_TOKEN);

        // Create client instance
        $merchantConfig = TestDataProvider::getMerchantConfig();
        $this->client = StandardCheckoutClient::getInstance(
            $merchantConfig['clientId'],
            $merchantConfig['clientVersion'],
            $merchantConfig['clientSecret'],
            $merchantConfig['env'],
            false,
            new CurlHttpClient()
        );

        // Use reflection to inject the mocked TokenService
        $this->setPrivateProperty($this->client, 'tokenService', $this->mockTokenService);
    }

    public function testGetInstance(): void
    {
        $merchantConfig = TestDataProvider::getMerchantConfig();
        $client = StandardCheckoutClient::getInstance(
            $merchantConfig['clientId'],
            $merchantConfig['clientVersion'],
            $merchantConfig['clientSecret'],
            $merchantConfig['env']
        );

        $this->assertInstanceOf(StandardCheckoutClient::class, $client);
    }

    public function testGetInstanceSingleton(): void
    {
        $merchantConfig = TestDataProvider::getMerchantConfig();

        $client1 = StandardCheckoutClient::getInstance(
            $merchantConfig['clientId'],
            $merchantConfig['clientVersion'],
            $merchantConfig['clientSecret'],
            $merchantConfig['env']
        );

        $client2 = StandardCheckoutClient::getInstance(
            $merchantConfig['clientId'],
            $merchantConfig['clientVersion'],
            $merchantConfig['clientSecret'],
            $merchantConfig['env']
        );

        // Should return the same instance for same configuration
        $this->assertSame($client1, $client2);
    }

    public function testGetInstanceDifferentConfig(): void
    {
        $merchantConfig = TestDataProvider::getMerchantConfig();

        $client1 = StandardCheckoutClient::getInstance(
            $merchantConfig['clientId'],
            $merchantConfig['clientVersion'],
            $merchantConfig['clientSecret'],
            $merchantConfig['env']
        );

        $client2 = StandardCheckoutClient::getInstance(
            'different_client_id',
            $merchantConfig['clientVersion'],
            $merchantConfig['clientSecret'],
            $merchantConfig['env']
        );

        // Should return different instances for different configurations
        $this->assertNotSame($client1, $client2);
    }

    public function testPaySuccess(): void
    {
        $responseData = TestDataProvider::getPaymentResponseData();

        $this->mockHttpClient->shouldReceive('postRequest')
            ->once()
            ->andReturn(new HttpResponse(200, [], json_encode($responseData)));

        $paymentData = TestDataProvider::getPaymentRequestData();
        $payRequest = new StandardCheckoutPayRequest(
            $paymentData['merchantOrderId'],
            $paymentData['amount'],
            $paymentData['metaInfo'],
            []
        );

        $response = $this->client->pay($payRequest);

        $this->assertInstanceOf(StandardCheckoutPayResponse::class, $response);
        $this->assertEquals($responseData['orderId'], $response->getOrderId());
        $this->assertEquals($responseData['state'], $response->getState());
        $this->assertEquals($responseData['redirectUrl'], $response->getRedirectUrl());
    }

    public function testPayWithBuilderRequest(): void
    {
        $responseData = TestDataProvider::getPaymentResponseData();

        $this->mockHttpClient->shouldReceive('postRequest')
            ->once()
            ->andReturn(new HttpResponse(200, [], json_encode($responseData)));

        $paymentData = TestDataProvider::getPaymentRequestData();
        $payRequest = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($paymentData['merchantOrderId'])
            ->amount($paymentData['amount'])
            ->message($paymentData['message'])
            ->redirectUrl($paymentData['redirectUrl'])
            ->udf1($paymentData['metaInfo']['udf1'])
            ->build();

        $response = $this->client->pay($payRequest);

        $this->assertInstanceOf(StandardCheckoutPayResponse::class, $response);
        $this->assertEquals($responseData['orderId'], $response->getOrderId());
    }

    public function testPayWithUnauthorizedError(): void
    {
        $this->expectException(PhonePeException::class);
        $this->expectExceptionMessage('Unauthorized access');

        $this->mockTokenService->expects($this->once())
            ->method('refreshToken');

        $this->mockHttpClient->shouldReceive('postRequest')
            ->once()
            ->andThrow(new PhonePeException('Unauthorized access', 401, 'UNAUTHORIZED'));

        $paymentData = TestDataProvider::getPaymentRequestData();
        $payRequest = new StandardCheckoutPayRequest(
            $paymentData['merchantOrderId'],
            $paymentData['amount'],
            [],
            []
        );

        $this->client->pay($payRequest);
    }

    public function testPayWithBadRequestError(): void
    {
        $this->expectException(PhonePeException::class);
        $this->expectExceptionMessage('Invalid amount');

        $this->mockHttpClient->shouldReceive('postRequest')
            ->once()
            ->andThrow(new PhonePeException('Invalid amount', 400, 'INVALID_AMOUNT'));

        $paymentData = TestDataProvider::getPaymentRequestData();
        $payRequest = new StandardCheckoutPayRequest(
            $paymentData['merchantOrderId'],
            -100, // Invalid amount
            [],
            []
        );

        $this->client->pay($payRequest);
    }

    public function testGetOrderStatusSuccess(): void
    {
        $responseData = TestDataProvider::getOrderStatusResponseData();

        $this->mockHttpClient->shouldReceive('getRequest')
            ->once()
            ->andReturn(new HttpResponse(200, [], json_encode($responseData)));

        $response = $this->client->getOrderStatus(TestDataProvider::TEST_MERCHANT_ORDER_ID);

        $this->assertInstanceOf(StatusCheckResponse::class, $response);
        $this->assertEquals($responseData['state'], $response->getState());
        $this->assertEquals($responseData['amount'], $response->getAmount());
    }

    public function testGetOrderStatusWithDetails(): void
    {
        $responseData = TestDataProvider::getOrderStatusResponseData();

        $this->mockHttpClient->shouldReceive('getRequest')
            ->once()
            ->with(Mockery::on(function ($url) {
                return strpos($url, 'details=true') !== false;
            }), Mockery::any())
            ->andReturn(new HttpResponse(200, [], json_encode($responseData)));

        $response = $this->client->getOrderStatus(TestDataProvider::TEST_MERCHANT_ORDER_ID, true);

        $this->assertInstanceOf(StatusCheckResponse::class, $response);
        $this->assertEquals($responseData['state'], $response->getState());
    }

    public function testGetOrderStatusWithoutDetails(): void
    {
        $responseData = TestDataProvider::getOrderStatusResponseData();

        $this->mockHttpClient->shouldReceive('getRequest')
            ->once()
            ->with(Mockery::on(function ($url) {
                return strpos($url, 'details=false') !== false;
            }), Mockery::any())
            ->andReturn(new HttpResponse(200, [], json_encode($responseData)));

        $response = $this->client->getOrderStatus(TestDataProvider::TEST_MERCHANT_ORDER_ID, false);

        $this->assertInstanceOf(StatusCheckResponse::class, $response);
    }

    public function testGetOrderStatusNotFound(): void
    {
        $this->expectException(PhonePeException::class);
        $this->expectExceptionMessage('Order not found');

        $this->mockHttpClient->shouldReceive('getRequest')
            ->once()
            ->andThrow(new PhonePeException('Order not found', 404, 'ORDER_NOT_FOUND'));

        $this->client->getOrderStatus('NONEXISTENT_ORDER_ID');
    }

    public function testRefundSuccess(): void
    {
        $responseData = TestDataProvider::getRefundResponseData();

        $this->mockHttpClient->shouldReceive('postRequest')
            ->once()
            ->andReturn(new HttpResponse(200, [], json_encode($responseData)));

        $refundData = TestDataProvider::getRefundRequestData();
        $refundRequest = new StandardCheckoutRefundRequest(
            $refundData['merchantRefundId'],
            $refundData['originalMerchantOrderId'],
            $refundData['amount']
        );

        $response = $this->client->refund($refundRequest);

        $this->assertInstanceOf(StandardCheckoutRefundResponse::class, $response);
        $this->assertEquals($responseData['refundId'], $response->getRefundId());
        $this->assertEquals($responseData['amount'], $response->getAmount());
        $this->assertEquals($responseData['state'], $response->getState());
    }

    public function testRefundWithInsufficientAmount(): void
    {
        $this->expectException(PhonePeException::class);
        $this->expectExceptionMessage('Insufficient amount for refund');

        $this->mockHttpClient->shouldReceive('postRequest')
            ->once()
            ->andThrow(new PhonePeException('Insufficient amount for refund', 400, 'INSUFFICIENT_AMOUNT'));

        $refundRequest = new StandardCheckoutRefundRequest(
            TestDataProvider::TEST_REFUND_ID,
            TestDataProvider::TEST_MERCHANT_ORDER_ID,
            999999999 // Amount higher than original payment
        );

        $this->client->refund($refundRequest);
    }

    public function testGetRefundStatusSuccess(): void
    {
        $responseData = [
            'originalMerchantOrderId' => TestDataProvider::TEST_MERCHANT_ORDER_ID,
            'amount' => TestDataProvider::TEST_REFUND_AMOUNT,
            'state' => 'COMPLETED',
            'paymentDetails' => []
        ];

        $this->mockHttpClient->shouldReceive('getRequest')
            ->once()
            ->andReturn(new HttpResponse(200, [], json_encode($responseData)));

        $response = $this->client->getRefundStatus(TestDataProvider::TEST_REFUND_ID);

        $this->assertInstanceOf(RefundStatusCheckResponse::class, $response);
        $this->assertEquals($responseData['state'], $response->getState());
        $this->assertEquals($responseData['amount'], $response->getAmount());
    }

    public function testVerifyCallbackResponseSuccess(): void
    {
        $callbackData = TestDataProvider::getCallbackData();

        $response = $this->client->verifyCallbackResponse(
            $callbackData['headers'],
            json_encode($callbackData['body']), // Convert array to JSON string
            $callbackData['credentials']['username'],
            $callbackData['credentials']['password']
        );

        $this->assertInstanceOf(CallbackResponse::class, $response);
    }

    public function testVerifyCallbackResponseInvalidSignature(): void
    {
        $this->expectException(PhonePeException::class);
        $this->expectExceptionMessage('Invalid callback');

        $callbackData = TestDataProvider::getCallbackData();

        // Use wrong credentials to make signature verification fail
        $this->client->verifyCallbackResponse(
            $callbackData['headers'],
            json_encode($callbackData['body']), // Convert array to JSON string
            'wrong_username',
            'wrong_password'
        );
    }

    public function testGetAuthHeadersToken(): void
    {
        // Create a fresh client with a clean mock for this specific test
        $freshMockTokenService = $this->createMock(TokenService::class);
        $freshMockTokenService->expects($this->once())
            ->method('getAuthHeaders')
            ->willReturn('O-Bearer test_token_12345');

        // Create a fresh client instance for this test
        $merchantConfig = TestDataProvider::getMerchantConfig();
        $freshClient = StandardCheckoutClient::getInstance(
            'unique_client_' . uniqid(), // Unique client to avoid singleton conflicts
            $merchantConfig['clientVersion'],
            $merchantConfig['clientSecret'],
            $merchantConfig['env'],
            false,
            new CurlHttpClient()
        );

        // Inject the fresh mock
        $this->setPrivateProperty($freshClient, 'tokenService', $freshMockTokenService);

        $authHeaders = $freshClient->getAuthHeadersToken();

        $this->assertEquals('O-Bearer test_token_12345', $authHeaders);
    }

    public function testGetAuthHeadersTokenWithRefresh(): void
    {
        $this->mockTokenService->expects($this->once())
            ->method('getAuthHeaders')
            ->willThrowException(new PhonePeException('Token expired', 401, 'TOKEN_EXPIRED'));

        $this->mockTokenService->expects($this->once())
            ->method('refreshToken');

        $this->expectException(PhonePeException::class);
        $this->expectExceptionMessage('Token expired');

        $this->client->getAuthHeadersToken();
    }

    public function testGetMerchantConfig(): void
    {
        $config = $this->client->getMerchantConfig();

        $this->assertNotNull($config);
        $this->assertEquals(TestDataProvider::TEST_CLIENT_ID, $config->getClientId());
        $this->assertEquals(TestDataProvider::TEST_CLIENT_VERSION, $config->getClientVersion());
        $this->assertEquals(TestDataProvider::TEST_CLIENT_SECRET, $config->getClientSecret());
    }

    public function testGetHostUrl(): void
    {
        $hostUrl = $this->client->getHostUrl();

        $this->assertIsString($hostUrl);
        $this->assertStringStartsWith('https://', $hostUrl);
    }

    public function testGetHeaders(): void
    {
        $headers = $this->client->getHeaders();

        $this->assertIsArray($headers);
        $this->assertArrayHasKey('accept', $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertArrayHasKey('x-source', $headers);
        $this->assertArrayHasKey('x-source-version', $headers);
        $this->assertArrayHasKey('x-source-platform', $headers);
        $this->assertArrayHasKey('x-source-platform-version', $headers);

        $this->assertEquals('application/json', $headers['accept']);
        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertStringStartsWith('O-Bearer', $headers['Authorization']);
    }

    /**
     * @dataProvider httpErrorStatusProvider
     */
    public function testPaymentErrorHandling(int $httpStatus, string $errorCode): void
    {
        $this->expectException(PhonePeException::class);

        if ($httpStatus === 401) {
            $this->mockTokenService->expects($this->once())->method('refreshToken');
        }

        $this->mockHttpClient->shouldReceive('postRequest')
            ->once()
            ->andThrow(new PhonePeException("HTTP Error: $httpStatus", $httpStatus, $errorCode));

        $paymentData = TestDataProvider::getPaymentRequestData();
        $payRequest = new StandardCheckoutPayRequest(
            $paymentData['merchantOrderId'],
            $paymentData['amount'],
            [],
            []
        );

        $this->client->pay($payRequest);
    }

    public static function httpErrorStatusProvider(): array
    {
        return TestDataProvider::httpErrorStatusProvider();
    }

    /**
     * @dataProvider environmentDataProvider
     */
    public function testClientWithDifferentEnvironments(string $environment): void
    {
        $client = StandardCheckoutClient::getInstance(
            TestDataProvider::TEST_CLIENT_ID,
            TestDataProvider::TEST_CLIENT_VERSION,
            TestDataProvider::TEST_CLIENT_SECRET,
            $environment
        );

        $this->assertInstanceOf(StandardCheckoutClient::class, $client);

        $hostUrl = $client->getHostUrl();
        $this->assertIsString($hostUrl);
        $this->assertStringStartsWith('https://', $hostUrl);
    }

    public static function environmentDataProvider(): array
    {
        return TestDataProvider::environmentDataProvider();
    }
}
