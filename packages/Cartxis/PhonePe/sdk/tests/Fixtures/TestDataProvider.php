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

namespace Tests\Fixtures;

class TestDataProvider
{
    // Merchant Configuration
    public const TEST_CLIENT_ID = 'test_client_id_12345';
    public const TEST_CLIENT_VERSION = 1;
    public const TEST_CLIENT_SECRET = 'test_client_secret_abcdef123456789';
    public const TEST_ENV = 'UAT';

    // Payment Data
    public const TEST_MERCHANT_ORDER_ID = 'ORDER_TEST_123456789';
    public const TEST_AMOUNT = 10000; // ₹100.00 in paise
    public const TEST_MESSAGE = 'Test Payment Transaction';
    public const TEST_REDIRECT_URL = 'https://merchant-test.example.com/callback';

    // Refund Data
    public const TEST_REFUND_ID = 'REFUND_TEST_987654321';
    public const TEST_REFUND_AMOUNT = 5000; // ₹50.00 in paise

    // Response Data
    public const TEST_ORDER_ID = 'PE_ORDER_123456789';
    public const TEST_PAYMENT_STATE = 'PENDING';
    public const TEST_REDIRECT_RESPONSE_URL = 'https://phonepe.com/payment-redirect?token=abc123';

    // OAuth Data
    public const TEST_ACCESS_TOKEN = 'test_access_token_abcdef123456789';
    public const TEST_TOKEN_TYPE = 'bearer';
    public const TEST_EXPIRES_IN = 3600;

    // Error Data
    public const TEST_ERROR_MESSAGE = 'Test error occurred';
    public const TEST_ERROR_CODE = 'TEST_ERROR_001';

    /**
     * Get merchant configuration array
     */
    public static function getMerchantConfig(): array
    {
        return [
            'clientId' => self::TEST_CLIENT_ID,
            'clientVersion' => self::TEST_CLIENT_VERSION,
            'clientSecret' => self::TEST_CLIENT_SECRET,
            'env' => self::TEST_ENV
        ];
    }

    /**
     * Get payment request data
     */
    public static function getPaymentRequestData(): array
    {
        return [
            'merchantOrderId' => self::TEST_MERCHANT_ORDER_ID,
            'amount' => self::TEST_AMOUNT,
            'message' => self::TEST_MESSAGE,
            'redirectUrl' => self::TEST_REDIRECT_URL,
            'metaInfo' => [
                'udf1' => 'test_user_data_1',
                'udf2' => 'test_user_data_2'
            ]
        ];
    }

    /**
     * Get payment response data
     */
    public static function getPaymentResponseData(): array
    {
        return [
            'orderId' => self::TEST_ORDER_ID,
            'state' => self::TEST_PAYMENT_STATE,
            'redirectUrl' => self::TEST_REDIRECT_RESPONSE_URL,
            'expireAt' => time() + 600
        ];
    }

    /**
     * Get refund request data
     */
    public static function getRefundRequestData(): array
    {
        return [
            'merchantRefundId' => self::TEST_REFUND_ID,
            'originalMerchantOrderId' => self::TEST_MERCHANT_ORDER_ID,
            'amount' => self::TEST_REFUND_AMOUNT
        ];
    }

    /**
     * Get refund response data
     */
    public static function getRefundResponseData(): array
    {
        return [
            'refundId' => self::TEST_REFUND_ID,
            'amount' => self::TEST_REFUND_AMOUNT,
            'state' => 'PENDING'
        ];
    }

    /**
     * Get order status response data
     */
    public static function getOrderStatusResponseData(): array
    {
        return [
            'orderId' => self::TEST_ORDER_ID,
            'state' => 'COMPLETED',
            'amount' => self::TEST_AMOUNT,
            'expireAt' => time() + 600,
            'metaInfo' => [
                'udf1' => 'test_user_data_1',
                'udf2' => 'test_user_data_2'
            ],
            'paymentDetails' => [
                'method' => 'UPI',
                'vpa' => 'test@phonepe'
            ]
        ];
    }

    /**
     * Get OAuth token response data
     */
    public static function getOAuthTokenData(): array
    {
        $issuedAt = time();
        return [
            'access_token' => self::TEST_ACCESS_TOKEN,
            'encrypted_access_token' => 'encrypted_' . self::TEST_ACCESS_TOKEN,
            'refresh_token' => 'refresh_token_xyz789',
            'expires_in' => self::TEST_EXPIRES_IN,
            'issued_at' => $issuedAt,
            'expires_at' => $issuedAt + self::TEST_EXPIRES_IN,
            'session_expires_at' => $issuedAt + (self::TEST_EXPIRES_IN * 2),
            'token_type' => self::TEST_TOKEN_TYPE
        ];
    }

    /**
     * Get callback headers and body
     */
    public static function getCallbackData(): array
    {
        $username = 'test_callback_user';
        $password = 'test_callback_password';
        $authorization = hash('sha256', $username . ':' . $password);

        return [
            'headers' => [
                'authorization' => $authorization
            ],
            'body' => [
                'orderId' => self::TEST_ORDER_ID,
                'state' => 'COMPLETED',
                'amount' => self::TEST_AMOUNT
            ],
            'credentials' => [
                'username' => $username,
                'password' => $password
            ]
        ];
    }

    /**
     * Get error response data
     */
    public static function getErrorResponseData(int $httpStatus = 400, ?string $code = null): array
    {
        return [
            'message' => self::TEST_ERROR_MESSAGE,
            'code' => $code ?? self::TEST_ERROR_CODE,
            'data' => [
                'context' => 'test_error_context'
            ],
            'httpStatus' => $httpStatus
        ];
    }

    /**
     * Data provider for different environments
     */
    public static function environmentDataProvider(): array
    {
        return [
            'Production' => ['PRODUCTION'],
            'UAT' => ['UAT'],
            'Stage' => ['STAGE']
        ];
    }

    /**
     * Data provider for invalid environments
     */
    public static function invalidEnvironmentDataProvider(): array
    {
        return [
            'Empty string' => [''],
            'Null' => [null],
            'Invalid env' => ['INVALID_ENV'],
            'Lowercase' => ['production']
        ];
    }

    /**
     * Data provider for payment amounts (edge cases)
     */
    public static function paymentAmountDataProvider(): array
    {
        return [
            'Minimum amount' => [1],
            'Small amount' => [100],
            'Normal amount' => [10000],
            'Large amount' => [999999999],
            'Zero amount' => [0],
            'Negative amount' => [-100]
        ];
    }

    /**
     * Data provider for HTTP error status codes
     */
    public static function httpErrorStatusProvider(): array
    {
        return [
            'Bad Request' => [400, 'BAD_REQUEST'],
            'Unauthorized' => [401, 'UNAUTHORIZED'],
            'Forbidden' => [403, 'FORBIDDEN'],
            'Not Found' => [404, 'NOT_FOUND'],
            'Internal Server Error' => [500, 'INTERNAL_SERVER_ERROR'],
            'Service Unavailable' => [503, 'SERVICE_UNAVAILABLE']
        ];
    }
}
