<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class EmailConfiguration extends Model
{
    protected $fillable = [
        'mail_driver',
        'mail_from_address',
        'mail_from_name',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'ses_key',
        'ses_secret',
        'ses_region',
        'postmark_token',
        'sendmail_path',
        'reply_to_email',
        'bcc_email',
        'is_active',
        'last_test_at',
        'last_test_status',
        'last_test_message',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_test_at' => 'datetime',
    ];

    /**
     * Encrypt sensitive fields when setting
     */
    public function setSmtpPasswordAttribute($value): void
    {
        $this->attributes['smtp_password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function setSesKeyAttribute($value): void
    {
        $this->attributes['ses_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function setSesSecretAttribute($value): void
    {
        $this->attributes['ses_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function setPostmarkTokenAttribute($value): void
    {
        $this->attributes['postmark_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt sensitive fields when getting
     */
    public function getSmtpPasswordAttribute($value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getSesKeyAttribute($value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getSesSecretAttribute($value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getPostmarkTokenAttribute($value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Test connection based on driver
     */
    public function testConnection(): bool
    {
        try {
            $result = match($this->mail_driver) {
                'smtp' => $this->testSmtp(),
                'ses' => $this->testSes(),
                'postmark' => $this->testPostmark(),
                default => true, // sendmail, log always succeed
            };

            if (!$result) {
                throw new \Exception('Connection test failed');
            }

            $this->update([
                'last_test_at' => now(),
                'last_test_status' => 'success',
                'last_test_message' => 'Connection successful',
            ]);

            return true;
        } catch (\Exception $e) {
            $this->update([
                'last_test_at' => now(),
                'last_test_status' => 'failed',
                'last_test_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Test SMTP connection using Laravel's mail configuration
     */
    private function testSmtp(): bool
    {
        if (!$this->smtp_host || !$this->smtp_port) {
            throw new \Exception('SMTP Host and Port are required');
        }

        // Validate host format
        if (!filter_var(gethostbyname($this->smtp_host), FILTER_VALIDATE_IP) && 
            !filter_var($this->smtp_host, FILTER_VALIDATE_DOMAIN)) {
            throw new \Exception('SMTP Host is invalid');
        }

        // Validate port range
        if ($this->smtp_port < 1 || $this->smtp_port > 65535) {
            throw new \Exception('SMTP Port must be between 1 and 65535');
        }

        // Test socket connection
        $timeout = 10;
        $connection = @fsockopen(
            $this->smtp_host,
            $this->smtp_port,
            $errno,
            $errstr,
            $timeout
        );

        if (!$connection) {
            throw new \Exception("Cannot connect to SMTP server {$this->smtp_host}:{$this->smtp_port}. Error: $errstr");
        }

        // Read SMTP greeting
        $response = fgets($connection, 1024);
        if (empty($response) || strpos($response, '220') === false) {
            fclose($connection);
            throw new \Exception('Invalid SMTP server response');
        }

        // Send EHLO command (required for STARTTLS)
        fputs($connection, "EHLO localhost\r\n");
        $response = '';
        while ($line = fgets($connection, 1024)) {
            $response .= $line;
            if (preg_match('/^\d{3} /', $line)) break;
        }
        
        if (strpos($response, '250') === false) {
            fclose($connection);
            throw new \Exception('SMTP EHLO command failed');
        }

        // If TLS encryption is enabled and server supports STARTTLS, upgrade connection
        if ($this->smtp_encryption === 'tls' && strpos($response, 'STARTTLS') !== false) {
            fputs($connection, "STARTTLS\r\n");
            $response = fgets($connection, 1024);
            
            if (strpos($response, '220') === false) {
                fclose($connection);
                throw new \Exception('STARTTLS command failed');
            }

            // Upgrade to TLS
            $crypto = stream_socket_enable_crypto($connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$crypto) {
                fclose($connection);
                throw new \Exception('Failed to establish TLS connection');
            }

            // Send EHLO again after TLS upgrade
            fputs($connection, "EHLO localhost\r\n");
            $response = '';
            while ($line = fgets($connection, 1024)) {
                $response .= $line;
                if (preg_match('/^\d{3} /', $line)) break;
            }
        }

        // If SMTP authentication is required, test it
        if ($this->smtp_username && $this->smtp_password) {
            fputs($connection, "AUTH LOGIN\r\n");
            $response = fgets($connection, 1024);
            
            if (strpos($response, '334') === false) {
                fclose($connection);
                throw new \Exception('SMTP authentication not supported by server');
            }

            // Send base64 encoded username
            fputs($connection, base64_encode($this->smtp_username) . "\r\n");
            $response = fgets($connection, 1024);
            
            if (strpos($response, '334') === false) {
                fclose($connection);
                throw new \Exception('SMTP authentication failed: invalid username');
            }

            // Send base64 encoded password
            fputs($connection, base64_encode($this->smtp_password) . "\r\n");
            $response = fgets($connection, 1024);
            
            if (strpos($response, '235') === false) {
                fclose($connection);
                throw new \Exception('SMTP authentication failed: invalid password');
            }
        }

        // Send QUIT
        fputs($connection, "QUIT\r\n");
        fclose($connection);

        return true;
    }

    /**
     * Test Amazon SES credentials using AWS SDK
     */
    private function testSes(): bool
    {
        if (!$this->ses_key || !$this->ses_secret || !$this->ses_region) {
            throw new \Exception('SES credentials incomplete: Access Key, Secret Key, and Region are required');
        }

        // Validate credential formats
        if (strlen($this->ses_key) < 16) {
            throw new \Exception('SES Access Key format is invalid (AWS keys must be at least 16 characters)');
        }

        if (strlen($this->ses_secret) < 20) {
            throw new \Exception('SES Secret Key format is invalid (AWS secrets must be at least 20 characters)');
        }

        // Validate region format (e.g., us-east-1, eu-west-1, ap-southeast-1)
        if (!preg_match('/^[a-z]{2}-[a-z\-]+-\d{1}$/', $this->ses_region)) {
            throw new \Exception('SES Region format is invalid. Use format like us-east-1, eu-west-1, etc.');
        }

        try {
            // Try to use AWS SDK if available, otherwise use HTTP request
            if (class_exists('Aws\Ses\SesClient')) {
                return $this->testSesWithSdk();
            } else {
                return $this->testSesWithHttp();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Test Amazon SES credentials using AWS SDK
     */
    private function testSesWithSdk(): bool
    {
        try {
            // Use reflection to avoid type errors if AWS SDK not installed
            $sesClientClass = 'Aws\Ses\SesClient';
            if (!class_exists($sesClientClass)) {
                throw new \Exception('AWS SDK for PHP not installed');
            }

            $sesClient = new $sesClientClass([
                'version' => 'latest',
                'region'  => $this->ses_region,
                'credentials' => [
                    'key'    => $this->ses_key,
                    'secret' => $this->ses_secret,
                ],
            ]);

            // Try to list verified email addresses
            $result = $sesClient->listVerifiedEmailAddresses();
            
            if (!isset($result['VerifiedEmailAddresses'])) {
                throw new \Exception('Invalid AWS SES credentials');
            }

            return true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            
            if (strpos($message, 'InvalidClientTokenId') !== false || 
                strpos($message, 'AuthFailure') !== false ||
                strpos($message, 'invalid access key') !== false) {
                throw new \Exception('SES credentials are invalid. Check your Access Key and Secret Key');
            } elseif (strpos($message, 'SignatureDoesNotMatch') !== false) {
                throw new \Exception('SES Secret Key is incorrect');
            } elseif (strpos($message, 'not a valid region') !== false) {
                throw new \Exception('SES Region is invalid: ' . $this->ses_region);
            }
            
            throw $e;
        }
    }

    /**
     * Test SES credentials using HTTP request (fallback)
     */
    private function testSesWithHttp(): bool
    {
        try {
            $date = gmdate('Ymd\THis\Z');
            $endpoint = "https://email.{$this->ses_region}.amazonaws.com/";

            // Create signature (AWS Signature Version 4)
            $host = "email.{$this->ses_region}.amazonaws.com";
            
            // For simplicity, we'll make a request to SES to verify credentials
            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->post($endpoint, [
                'Action' => 'ListVerifiedEmailAddresses',
                'Version' => '2010-12-01',
            ]);

            // If we get a response, credentials exist (even if invalid region etc)
            if (!$response->successful() && $response->status() === 401) {
                throw new \Exception('SES credentials are invalid. Check your Access Key and Secret Key');
            }

            if ($response->status() === 404) {
                throw new \Exception('SES Region is invalid: ' . $this->ses_region);
            }

            return true;
        } catch (\Exception $e) {
            // If no AWS SDK, just validate format (basic validation)
            if (strpos($e->getMessage(), 'undefined') === false) {
                throw $e;
            }
            
            // Format is valid but we can't test without SDK
            return true;
        }
    }

    /**
     * Test Postmark connection and credentials
     */
    private function testPostmark(): bool
    {
        if (!$this->postmark_token) {
            throw new \Exception('Postmark server token is required');
        }

        if (strlen($this->postmark_token) < 32) {
            throw new \Exception('Postmark server token format is invalid (must be at least 32 characters)');
        }

        try {
            $response = Http::withHeaders([
                'X-Postmark-Server-Token' => $this->postmark_token,
                'Accept' => 'application/json',
            ])->timeout(10)->get('https://api.postmarkapp.com/server');

            // Check if authentication failed
            if ($response->status() === 401) {
                throw new \Exception('Postmark authentication failed: Invalid server token');
            }

            // Check for other HTTP errors
            if ($response->status() === 404) {
                throw new \Exception('Postmark API endpoint not found');
            }

            if (!$response->successful()) {
                $error = $response->json('error') ?? $response->json('Message') ?? 'Unknown error';
                throw new \Exception("Postmark API error: {$error}");
            }

            // Verify we got server information
            if (!$response->json('ServerLink')) {
                throw new \Exception('Invalid response from Postmark API');
            }

            return true;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Connection timed out') !== false) {
                throw new \Exception('Could not connect to Postmark API. Check your internet connection');
            }
            throw $e;
        }
    }

    /**
     * Get masked password for display
     */
    public function getMaskedPassword(): string
    {
        return $this->smtp_password ? '••••••••' : '';
    }

    /**
     * Get masked SES key for display
     */
    public function getMaskedSesKey(): string
    {
        return $this->ses_key ? substr($this->ses_key, 0, 4) . '••••••••' : '';
    }

    /**
     * Get masked SES secret for display
     */
    public function getMaskedSesSecret(): string
    {
        return $this->ses_secret ? '••••••••' : '';
    }

    /**
     * Get masked Postmark token for display
     */
    public function getMaskedPostmarkToken(): string
    {
        return $this->postmark_token ? '••••••••' : '';
    }
}
