<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailTemplate extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'subject',
        'from_name',
        'from_email',
        'reply_to',
        'cc',
        'bcc',
        'html_content',
        'plain_text_content',
        'variables',
        'is_active',
        'is_system',
        'locale',
        'last_sent_at',
        'times_sent',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'variables' => 'json',
        'last_sent_at' => 'datetime',
    ];

    /**
     * Render template with data
     */
    public function render(array $data = []): array
    {
        return [
            'subject' => $this->replaceVariables($this->subject, $data),
            'html' => $this->replaceVariables($this->html_content, $data),
            'text' => $this->replaceVariables($this->plain_text_content ?? '', $data),
            'from' => [
                'name' => $this->from_name,
                'email' => $this->from_email,
            ],
            'reply_to' => $this->reply_to,
        ];
    }

    /**
     * Send email using this template
     */
    public function send(string $to, array $data = [], array $attachments = []): bool
    {
        if (!$this->is_active) {
            Log::warning("Attempted to send inactive email template: {$this->code}");
            return false;
        }

        try {
            $rendered = $this->render($data);
            $config = EmailConfiguration::where('is_active', true)->first();

            Mail::send([], [], function ($message) use ($to, $rendered, $config, $attachments) {
                $message->to($to)
                    ->subject($rendered['subject']);

                // From address
                if ($rendered['from']['email']) {
                    $message->from($rendered['from']['email'], $rendered['from']['name'] ?? '');
                } elseif ($config) {
                    $message->from($config->mail_from_address, $config->mail_from_name);
                }

                // Content
                if ($rendered['html']) {
                    $message->html($rendered['html']);
                }
                if ($rendered['text']) {
                    $message->text($rendered['text']);
                }

                // Reply-To
                if ($rendered['reply_to']) {
                    $message->replyTo($rendered['reply_to']);
                } elseif ($config && $config->reply_to_email) {
                    $message->replyTo($config->reply_to_email);
                }

                // CC
                if ($this->cc) {
                    $ccEmails = array_map('trim', explode(',', $this->cc));
                    $message->cc($ccEmails);
                }

                // BCC
                if ($this->bcc) {
                    $bccEmails = array_map('trim', explode(',', $this->bcc));
                    $message->bcc($bccEmails);
                } elseif ($config && $config->bcc_email) {
                    $message->bcc($config->bcc_email);
                }
                
                // Attachments
                foreach ($attachments as $attachment) {
                    if (is_array($attachment)) {
                        // Format: ['path' => $path, 'name' => $name, 'mime' => $mime]
                        $message->attach($attachment['path'], [
                            'as' => $attachment['name'] ?? null,
                            'mime' => $attachment['mime'] ?? null,
                        ]);
                    } else {
                        // Simple path string
                        $message->attach($attachment);
                    }
                }
            });

            // Update stats
            $this->increment('times_sent');
            $this->update(['last_sent_at' => now()]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email template {$this->code}: " . $e->getMessage(), [
                'template' => $this->code,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Replace variables in content
     */
    private function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            // Skip array values - they should be handled separately in the template
            if (is_array($value)) {
                continue;
            }
            
            // Convert value to string
            $stringValue = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
            
            // Support both {variable} and {{variable}} syntax
            $content = str_replace(['{' . $key . '}', '{{' . $key . '}}'], $stringValue, $content);
        }
        return $content;
    }

    /**
     * Find template by code
     */
    public static function findByCode(string $code, string $locale = 'en'): ?self
    {
        return static::where('code', $code)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get templates by category
     */
    public static function byCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('category', $category)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get available variables for this template
     */
    public function getAvailableVariables(): array
    {
        if (is_array($this->variables)) {
            return $this->variables;
        }

        // Extract variables from content if not set
        $content = $this->subject . ' ' . $this->html_content . ' ' . $this->plain_text_content;
        preg_match_all('/\{([a-z_]+)\}/', $content, $matches);
        
        return array_unique($matches[1] ?? []);
    }

    /**
     * Preview template with sample data
     */
    public function preview(array $sampleData = []): array
    {
        if (empty($sampleData)) {
            $sampleData = $this->getDefaultSampleData();
        }

        return $this->render($sampleData);
    }

    /**
     * Get default sample data based on template category
     */
    private function getDefaultSampleData(): array
    {
        $defaults = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'store_name' => config('app.name', 'Vortex Store'),
            'store_url' => config('app.url'),
            'current_year' => date('Y'),
        ];

        // Category-specific defaults
        if ($this->category === 'order') {
            return array_merge($defaults, [
                'order_number' => 'ORD-001234',
                'order_date' => now()->format('F j, Y'),
                'order_total' => '$149.99',
                'order_status' => 'Processing',
                'tracking_number' => '1Z999AA10123456784',
                'shipping_address' => '123 Main St, New York, NY 10001',
            ]);
        }

        if ($this->category === 'account') {
            return array_merge($defaults, [
                'reset_url' => url('/password/reset/token123'),
                'verification_url' => url('/email/verify/token123'),
                'account_created_at' => now()->format('F j, Y'),
            ]);
        }

        if ($this->category === 'payment') {
            return array_merge($defaults, [
                'payment_method' => 'Credit Card ending in 4242',
                'payment_amount' => '$149.99',
                'transaction_id' => 'TXN-' . uniqid(),
                'payment_date' => now()->format('F j, Y, g:i A'),
            ]);
        }

        return $defaults;
    }
}
