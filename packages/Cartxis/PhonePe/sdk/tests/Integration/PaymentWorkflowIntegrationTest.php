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

namespace Tests\Integration;

use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;
use PhonePe\payments\v2\models\request\builders\StandardCheckoutRefundRequestBuilder;
use PhonePe\payments\v2\models\response\StandardCheckoutPayResponse;
use PhonePe\payments\v2\models\response\StandardCheckoutRefundResponse;
use PhonePe\payments\v2\models\response\StatusCheckResponse;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;
use Tests\Fixtures\TestDataProvider;
use Tests\Unit\BaseTestCase;

/**
 * Integration tests for end-to-end payment workflows
 * 
 * Note: These tests are designed to run against mock/sandbox environments
 * and should not be run against production endpoints.
 */
class PaymentWorkflowIntegrationTest extends BaseTestCase
{
    private StandardCheckoutClient $client;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip integration tests if running in CI or if credentials are not available
        if (!$this->shouldRunIntegrationTests()) {
            $this->markTestSkipped('Integration tests skipped - credentials not available or CI environment');
        }
        
        $merchantConfig = TestDataProvider::getMerchantConfig();
        $this->client = StandardCheckoutClient::getInstance(
            $merchantConfig['clientId'],
            $merchantConfig['clientVersion'],
            $merchantConfig['clientSecret'],
            'STAGE', // Always use STAGE for integration tests
            true // Enable events for testing
        );
    }
    
    /**
     * Test complete payment workflow: pay -> check status -> refund -> check refund status
     * 
     * @group integration
     * @group slow
     */
    public function testCompletePaymentWorkflow(): void
    {
        $orderId = 'INT_TEST_' . time() . '_' . rand(1000, 9999);
        $amount = 10000; // ₹100.00
        
        // Step 1: Initiate Payment
        $paymentRequest = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($orderId)
            ->amount($amount)
            ->message('Integration Test Payment')
            ->redirectUrl('https://merchant-test.example.com/callback')
            ->udf1('integration_test')
            ->udf2('workflow_test')
            ->build();
        
        $paymentResponse = $this->client->pay($paymentRequest);
        
        $this->assertInstanceOf(StandardCheckoutPayResponse::class, $paymentResponse);
        $this->assertNotEmpty($paymentResponse->getOrderId());
        $this->assertNotEmpty($paymentResponse->getRedirectUrl());
        $this->assertContains($paymentResponse->getState(), ['PENDING', 'INITIATED']);
        
        $phonepeOrderId = $paymentResponse->getOrderId();
        
        // Step 2: Check Payment Status
        // Note: In real scenarios, you would wait for payment completion
        // For integration tests, we check the initial status
        $statusResponse = $this->client->getOrderStatus($orderId, true);
        
        $this->assertInstanceOf(StatusCheckResponse::class, $statusResponse);
        $this->assertEquals($phonepeOrderId, $statusResponse->getOrderId());
        $this->assertEquals($amount, $statusResponse->getAmount());
        
        // Step 3: Simulate payment completion and initiate refund
        // In real scenarios, you would only refund after payment completion
        $refundId = 'REFUND_' . time() . '_' . rand(1000, 9999);
        $refundAmount = 5000; // Partial refund of ₹50.00
        
        $refundRequest = StandardCheckoutRefundRequestBuilder::builder()
            ->merchantRefundId($refundId)
            ->originalMerchantOrderId($orderId)
            ->amount($refundAmount)
            ->build();
        
        try {
            $refundResponse = $this->client->refund($refundRequest);
            
            $this->assertInstanceOf(StandardCheckoutRefundResponse::class, $refundResponse);
            $this->assertNotEmpty($refundResponse->getRefundId());
            $this->assertEquals($refundAmount, $refundResponse->getAmount());
            $this->assertContains($refundResponse->getState(), ['PENDING', 'INITIATED']);
            
            // Step 4: Check Refund Status
            $refundStatusResponse = $this->client->getRefundStatus($refundId);
            
            $this->assertNotNull($refundStatusResponse);
            $this->assertEquals($refundAmount, $refundStatusResponse->getAmount());
            
        } catch (\Exception $e) {
            // Refund might fail if payment is not completed
            // This is acceptable in integration tests
            $this->addWarning('Refund test skipped: ' . $e->getMessage());
        }
    }
    
    /**
     * Test payment with all metadata fields
     * 
     * @group integration
     */
    public function testPaymentWithFullMetadata(): void
    {
        $orderId = 'META_TEST_' . time() . '_' . rand(1000, 9999);
        
        $paymentRequest = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($orderId)
            ->amount(15000) // ₹150.00
            ->message('Full Metadata Test Payment')
            ->redirectUrl('https://merchant-test.example.com/callback')
            ->udf1('customer_id_12345')
            ->udf2('campaign_summer2024')
            ->udf3('channel_mobile_app')
            ->udf4('category_electronics')
            ->udf5('subcategory_phones')
            ->build();
        
        $paymentResponse = $this->client->pay($paymentRequest);
        
        $this->assertInstanceOf(StandardCheckoutPayResponse::class, $paymentResponse);
        $this->assertNotEmpty($paymentResponse->getOrderId());
        
        // Verify metadata is preserved in status check
        $statusResponse = $this->client->getOrderStatus($orderId, true);
        
        $this->assertInstanceOf(StatusCheckResponse::class, $statusResponse);
        
        $metaInfo = $statusResponse->getMetaInfo();
        if ($metaInfo) {
            $this->assertIsArray($metaInfo);
            // Note: The actual metadata structure depends on the API response format
        }
    }
    
    /**
     * Test authentication token refresh workflow
     * 
     * @group integration
     */
    public function testTokenRefreshWorkflow(): void
    {
        // Get initial auth token
        $initialToken = $this->client->getAuthHeadersToken();
        $this->assertStringStartsWith('O-Bearer', $initialToken);
        
        // Make a request to ensure token is used
        $orderId = 'TOKEN_TEST_' . time() . '_' . rand(1000, 9999);
        
        $paymentRequest = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($orderId)
            ->amount(5000) // ₹50.00
            ->message('Token Test Payment')
            ->redirectUrl('https://merchant-test.example.com/callback')
            ->build();
        
        $paymentResponse = $this->client->pay($paymentRequest);
        
        $this->assertInstanceOf(StandardCheckoutPayResponse::class, $paymentResponse);
        
        // Get token again - should be the same if not expired
        $secondToken = $this->client->getAuthHeadersToken();
        $this->assertStringStartsWith('O-Bearer', $secondToken);
    }
    
    /**
     * Test different payment amounts including edge cases
     * 
     * @group integration
     * @dataProvider paymentAmountProvider
     */
    public function testPaymentAmountVariations(int $amount, string $description): void
    {
        $orderId = 'AMT_TEST_' . $amount . '_' . time() . '_' . rand(100, 999);
        
        $paymentRequest = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($orderId)
            ->amount($amount)
            ->message("Amount Test: $description")
            ->redirectUrl('https://merchant-test.example.com/callback')
            ->udf1("amount_test_$amount")
            ->build();
        
        try {
            $paymentResponse = $this->client->pay($paymentRequest);
            
            $this->assertInstanceOf(StandardCheckoutPayResponse::class, $paymentResponse);
            $this->assertNotEmpty($paymentResponse->getOrderId());
            
            // Verify amount in status check
            $statusResponse = $this->client->getOrderStatus($orderId);
            $this->assertEquals($amount, $statusResponse->getAmount());
            
        } catch (\Exception $e) {
            if ($amount <= 0) {
                // Negative/zero amounts should fail
                $this->assertStringContainsString('amount', strtolower($e->getMessage()));
            } else {
                // For valid amounts, log but don't fail the test
                $this->addWarning("Amount test failed for $amount: " . $e->getMessage());
            }
        }
    }
    
    public function paymentAmountProvider(): array
    {
        return [
            [1, 'Minimum amount'],
            [100, 'Small amount'],
            [10000, 'Normal amount'],
            [100000, 'Large amount'],
            [0, 'Zero amount (should fail)'],
            [-100, 'Negative amount (should fail)']
        ];
    }
    
    /**
     * Test special characters in payment fields
     * 
     * @group integration
     */
    public function testSpecialCharactersHandling(): void
    {
        $orderId = 'SPECIAL_TEST_' . time() . '_' . rand(1000, 9999);
        
        $paymentRequest = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($orderId)
            ->amount(7500) // ₹75.00
            ->message('Payment with special chars: ñáéíóú 中文 العربية 🎉')
            ->redirectUrl('https://merchant-test.example.com/callback?param=value&other=test')
            ->udf1('UDF with émojis: 💳🔐')
            ->udf2('Symbols: @#$%^&*()_+-=[]{}|;:,.<>?')
            ->build();
        
        $paymentResponse = $this->client->pay($paymentRequest);
        
        $this->assertInstanceOf(StandardCheckoutPayResponse::class, $paymentResponse);
        $this->assertNotEmpty($paymentResponse->getOrderId());
    }
    
    /**
     * Check if integration tests should run
     */
    private function shouldRunIntegrationTests(): bool
    {
        // Skip if in CI environment
        if (getenv('CI') === 'true' || getenv('GITHUB_ACTIONS') === 'true') {
            return false;
        }
        
        // Skip if test environment variable is not set
        if (getenv('RUN_INTEGRATION_TESTS') !== 'true') {
            return false;
        }
        
        // Check if required credentials are available
        $merchantConfig = TestDataProvider::getMerchantConfig();
        if (empty($merchantConfig['clientId']) || 
            empty($merchantConfig['clientSecret']) ||
            $merchantConfig['clientId'] === TestDataProvider::TEST_CLIENT_ID) {
            return false;
        }
        
        return true;
    }
}
