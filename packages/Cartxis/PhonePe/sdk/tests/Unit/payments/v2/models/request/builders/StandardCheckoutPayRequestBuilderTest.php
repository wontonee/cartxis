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

namespace Tests\Unit\payments\v2\models\request\builders;

use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;
use PhonePe\payments\v2\models\request\StandardCheckoutPayRequest;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutConstants;
use Tests\Fixtures\TestDataProvider;
use Tests\Unit\BaseTestCase;

class StandardCheckoutPayRequestBuilderTest extends BaseTestCase
{
    public function testBuilderCreation(): void
    {
        $builder = StandardCheckoutPayRequestBuilder::builder();
        
        $this->assertInstanceOf(StandardCheckoutPayRequestBuilder::class, $builder);
    }

    public function testBuildMinimalPaymentRequest(): void
    {
        $testData = TestDataProvider::getPaymentRequestData();
        
        $request = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($testData['merchantOrderId'])
            ->amount($testData['amount'])
            ->message($testData['message'])
            ->redirectUrl($testData['redirectUrl'])
            ->build();

        $this->assertInstanceOf(StandardCheckoutPayRequest::class, $request);
        $this->assertEquals($testData['merchantOrderId'], $request->getMerchantOrderId());
        $this->assertEquals($testData['amount'], $request->getAmount());
    }

    public function testBuildPaymentRequestWithMetaInfo(): void
    {
        $testData = TestDataProvider::getPaymentRequestData();
        
        $request = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($testData['merchantOrderId'])
            ->amount($testData['amount'])
            ->message($testData['message'])
            ->redirectUrl($testData['redirectUrl'])
            ->udf1($testData['metaInfo']['udf1'])
            ->udf2($testData['metaInfo']['udf2'])
            ->build();

        $this->assertInstanceOf(StandardCheckoutPayRequest::class, $request);
        
        $metaInfo = $request->getMetaInfo();
        $this->assertIsArray($metaInfo);
        $this->assertEquals($testData['metaInfo']['udf1'], $metaInfo['udf1']);
        $this->assertEquals($testData['metaInfo']['udf2'], $metaInfo['udf2']);
    }

    public function testBuildPaymentRequestWithAllUdfFields(): void
    {
        $testData = TestDataProvider::getPaymentRequestData();
        
        $request = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($testData['merchantOrderId'])
            ->amount($testData['amount'])
            ->message($testData['message'])
            ->redirectUrl($testData['redirectUrl'])
            ->udf1('udf1_value')
            ->udf2('udf2_value')
            ->udf3('udf3_value')
            ->udf4('udf4_value')
            ->udf5('udf5_value')
            ->build();

        $metaInfo = $request->getMetaInfo();
        $this->assertEquals('udf1_value', $metaInfo['udf1']);
        $this->assertEquals('udf2_value', $metaInfo['udf2']);
        $this->assertEquals('udf3_value', $metaInfo['udf3']);
        $this->assertEquals('udf4_value', $metaInfo['udf4']);
        $this->assertEquals('udf5_value', $metaInfo['udf5']);
    }

    public function testPaymentFlowStructure(): void
    {
        $testData = TestDataProvider::getPaymentRequestData();
        
        $request = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($testData['merchantOrderId'])
            ->amount($testData['amount'])
            ->message($testData['message'])
            ->redirectUrl($testData['redirectUrl'])
            ->build();

        $paymentFlow = $request->getPaymentFlow();
        
        $this->assertIsArray($paymentFlow);
        $this->assertArrayHasKey('type', $paymentFlow);
        $this->assertArrayHasKey('message', $paymentFlow);
        $this->assertArrayHasKey('merchantUrls', $paymentFlow);
        
        $this->assertEquals(StandardCheckoutConstants::STANDARD_CHECKOUT_PAYMENT_FLOW_TYPE, $paymentFlow['type']);
        $this->assertEquals($testData['message'], $paymentFlow['message']);
        $this->assertArrayHasKey('redirectUrl', $paymentFlow['merchantUrls']);
        $this->assertEquals($testData['redirectUrl'], $paymentFlow['merchantUrls']['redirectUrl']);
    }

    public function testJsonSerialization(): void
    {
        $testData = TestDataProvider::getPaymentRequestData();
        
        $request = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($testData['merchantOrderId'])
            ->amount($testData['amount'])
            ->message($testData['message'])
            ->redirectUrl($testData['redirectUrl'])
            ->udf1($testData['metaInfo']['udf1'])
            ->build();

        $json = json_encode($request);
        $this->assertIsString($json);
        
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        
        $this->assertJsonContains([
            'merchantOrderId' => $testData['merchantOrderId'],
            'amount' => $testData['amount']
        ], $json);
    }

    public function testFluentInterface(): void
    {
        $builder = StandardCheckoutPayRequestBuilder::builder();
        
        // Test that each method returns the builder instance for chaining
        $result = $builder->merchantOrderId('test');
        $this->assertSame($builder, $result);
        
        $result = $builder->amount(1000);
        $this->assertSame($builder, $result);
        
        $result = $builder->message('test message');
        $this->assertSame($builder, $result);
        
        $result = $builder->redirectUrl('https://example.com');
        $this->assertSame($builder, $result);
        
        $result = $builder->udf1('value1');
        $this->assertSame($builder, $result);
    }

    /**
     * @dataProvider amountDataProvider
     */
    public function testAmountVariations(int $amount): void
    {
        $testData = TestDataProvider::getPaymentRequestData();
        
        $request = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($testData['merchantOrderId'])
            ->amount($amount)
            ->message($testData['message'])
            ->redirectUrl($testData['redirectUrl'])
            ->build();

        $this->assertEquals($amount, $request->getAmount());
    }

    public static function amountDataProvider(): array
    {
        return TestDataProvider::paymentAmountDataProvider();
    }

    public function testNullMetaInfoHandling(): void
    {
        $testData = TestDataProvider::getPaymentRequestData();
        
        $request = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($testData['merchantOrderId'])
            ->amount($testData['amount'])
            ->message($testData['message'])
            ->redirectUrl($testData['redirectUrl'])
            ->build();

        // Meta info should be null or empty when no UDF fields are set
        $metaInfo = $request->getMetaInfo();
        $this->assertTrue(is_null($metaInfo) || (is_array($metaInfo) && empty($metaInfo)));
    }

    public function testSpecialCharactersInFields(): void
    {
        $specialOrderId = 'ORDER_TEST_@#$%^&*()_+';
        $specialMessage = 'Payment with special chars: ñáéíóú 中文 العربية';
        $specialUrl = 'https://example.com/callback?param=value&other=test';
        
        $request = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($specialOrderId)
            ->amount(1000)
            ->message($specialMessage)
            ->redirectUrl($specialUrl)
            ->udf1('UDF with émojis: 🎉💳')
            ->build();

        $this->assertEquals($specialOrderId, $request->getMerchantOrderId());
        $paymentFlow = $request->getPaymentFlow();
        $this->assertEquals($specialMessage, $paymentFlow['message']);
        $this->assertEquals($specialUrl, $paymentFlow['merchantUrls']['redirectUrl']);
        
        $metaInfo = $request->getMetaInfo();
        $this->assertEquals('UDF with émojis: 🎉💳', $metaInfo['udf1']);
    }

    public function testLongFieldValues(): void
    {
        $longOrderId = str_repeat('A', 100);
        $longMessage = str_repeat('This is a very long payment message. ', 10);
        $longUrl = 'https://example.com/very-long-callback-url-' . str_repeat('param', 20);
        $longUdf = str_repeat('Long UDF value ', 20);
        
        $request = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($longOrderId)
            ->amount(50000)
            ->message($longMessage)
            ->redirectUrl($longUrl)
            ->udf1($longUdf)
            ->build();

        $this->assertEquals($longOrderId, $request->getMerchantOrderId());
        $paymentFlow = $request->getPaymentFlow();
        $this->assertEquals($longMessage, $paymentFlow['message']);
        $this->assertEquals($longUrl, $paymentFlow['merchantUrls']['redirectUrl']);
        
        $metaInfo = $request->getMetaInfo();
        $this->assertEquals($longUdf, $metaInfo['udf1']);
    }
}
