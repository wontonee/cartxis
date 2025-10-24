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
            match($this->mail_driver) {
                'smtp' => $this->testSmtp(),
                'ses' => $this->testSes(),
                'postmark' => $this->testPostmark(),
                default => true, // sendmail, log always succeed
            };

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
     * Test SMTP connection
     */
    private function testSmtp(): bool
    {
        $connection = @fsockopen(
            $this->smtp_host,
            $this->smtp_port,
            $errno,
            $errstr,
            5
        );

        if (!$connection) {
            throw new \Exception("SMTP connection failed: $errstr ($errno)");
        }

        fclose($connection);
        return true;
    }

    /**
     * Test Amazon SES credentials
     */
    private function testSes(): bool
    {
        // Basic validation - in production you'd use AWS SDK
        if (!$this->ses_key || !$this->ses_secret || !$this->ses_region) {
            throw new \Exception('SES credentials incomplete');
        }

        // For now, just validate format
        if (strlen($this->ses_key) < 16) {
            throw new \Exception('SES Access Key appears invalid');
        }

        return true;
    }

    /**
     * Test Postmark connection
     */
    private function testPostmark(): bool
    {
        if (!$this->postmark_token) {
            throw new \Exception('Postmark token is required');
        }

        $response = Http::withHeaders([
            'X-Postmark-Server-Token' => $this->postmark_token,
            'Accept' => 'application/json',
        ])->get('https://api.postmarkapp.com/server');

        if (!$response->successful()) {
            throw new \Exception('Postmark authentication failed');
        }

        return true;
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
