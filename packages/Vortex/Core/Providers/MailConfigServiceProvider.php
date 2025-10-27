<?php

namespace Vortex\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Vortex\Core\Models\EmailConfiguration;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     * 
     * This provider loads email configuration from the database
     * and applies it to Laravel's mail configuration at runtime.
     */
    public function boot(): void
    {
        // Always try to configure mail from database
        // (except during migrations)
        try {
            $this->configureMailFromDatabase();
        } catch (\Exception $e) {
            // Silently fail during installation
            logger()->debug('Mail configuration not loaded: ' . $e->getMessage());
        }
    }

    /**
     * Configure Laravel's mail system from database settings.
     */
    protected function configureMailFromDatabase(): void
    {
        // Check if table exists before querying
        if (!Schema::hasTable('email_configurations')) {
            return;
        }

        $config = EmailConfiguration::first();

        if (!$config) {
            return;
        }

        // Set default mailer
        Config::set('mail.default', $config->mail_driver);

        // Set from address and name
        Config::set('mail.from.address', $config->mail_from_address);
        Config::set('mail.from.name', $config->mail_from_name);

        // Clear all driver-specific configurations first to avoid conflicts
        Config::set('services.ses', []);
        Config::set('services.postmark', []);

        // Configure based on driver
        switch ($config->mail_driver) {
            case 'smtp':
                $this->configureSMTP($config);
                break;

            case 'ses':
                $this->configureSES($config);
                break;

            case 'postmark':
                $this->configurePostmark($config);
                break;

            case 'sendmail':
                $this->configureSendmail($config);
                break;

            case 'log':
                // Log driver needs no additional configuration
                break;
        }

        // Clear cached mail manager to force reload
        app()->forgetInstance('mail.manager');
    }

    /**
     * Configure SMTP mailer.
     */
    protected function configureSMTP(EmailConfiguration $config): void
    {
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $config->smtp_host,
            'port' => $config->smtp_port,
            'username' => $config->smtp_username,
            'password' => $config->smtp_password, // Already decrypted by model accessor
            'encryption' => $config->smtp_encryption === 'none' ? null : $config->smtp_encryption,
            'timeout' => null,
        ]);
    }

    /**
     * Configure Amazon SES mailer.
     */
    protected function configureSES(EmailConfiguration $config): void
    {
        Config::set('mail.mailers.ses', [
            'transport' => 'ses',
        ]);

        Config::set('services.ses', [
            'key' => $config->ses_key, // Already decrypted by model accessor
            'secret' => $config->ses_secret, // Already decrypted by model accessor
            'region' => $config->ses_region,
        ]);
    }

    /**
     * Configure Postmark mailer.
     */
    protected function configurePostmark(EmailConfiguration $config): void
    {
        Config::set('mail.mailers.postmark', [
            'transport' => 'postmark',
        ]);

        Config::set('services.postmark', [
            'token' => $config->postmark_token, // Already decrypted by model accessor
        ]);
    }

    /**
     * Configure Sendmail mailer.
     */
    protected function configureSendmail(EmailConfiguration $config): void
    {
        Config::set('mail.mailers.sendmail', [
            'transport' => 'sendmail',
            'path' => $config->sendmail_path ?: '/usr/sbin/sendmail -bs -i',
        ]);
    }
}
