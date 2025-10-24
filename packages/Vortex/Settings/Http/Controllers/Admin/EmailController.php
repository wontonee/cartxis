<?php

namespace Vortex\Settings\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Vortex\Core\Models\EmailConfiguration;
use Vortex\Core\Models\EmailTemplate;

class EmailController
{
    public function index(): Response
    {
        $configuration = EmailConfiguration::first() ?? new EmailConfiguration([
            'mail_driver' => 'smtp',
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
            'smtp_encryption' => config('mail.mailers.smtp.encryption'),
        ]);

        $templates = EmailTemplate::orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return Inertia::render('Admin/Settings/Email/Index', [
            'configuration' => [
                'id' => $configuration->id,
                'mail_driver' => $configuration->mail_driver,
                'mail_from_address' => $configuration->mail_from_address,
                'mail_from_name' => $configuration->mail_from_name,
                'reply_to_email' => $configuration->reply_to_email,
                'bcc_email' => $configuration->bcc_email,
                
                // SMTP
                'smtp_host' => $configuration->smtp_host,
                'smtp_port' => $configuration->smtp_port,
                'smtp_username' => $configuration->smtp_username,
                'smtp_password_masked' => $configuration->getMaskedPassword(),
                'smtp_encryption' => $configuration->smtp_encryption,
                
                // SES
                'ses_key_masked' => $configuration->getMaskedSesKey(),
                'ses_secret_masked' => $configuration->getMaskedSesSecret(),
                'ses_region' => $configuration->ses_region,
                
                // Postmark
                'postmark_token_masked' => $configuration->getMaskedPostmarkToken(),
                
                // Sendmail
                'sendmail_path' => $configuration->sendmail_path,
                
                // Testing
                'last_test_at' => $configuration->last_test_at,
                'last_test_status' => $configuration->last_test_status,
                'last_test_message' => $configuration->last_test_message,
                'is_active' => $configuration->is_active,
            ],
            'templates' => $templates,
            'drivers' => [
                'smtp' => 'SMTP',
                'ses' => 'Amazon SES',
                'postmark' => 'Postmark',
                'sendmail' => 'Sendmail',
                'log' => 'Log (Testing)',
            ],
            'categories' => [
                'order' => 'Order Emails',
                'account' => 'Account Emails',
                'payment' => 'Payment Emails',
            ],
        ]);
    }

    public function saveConfiguration(Request $request)
    {
        $validated = $request->validate([
            'mail_driver' => 'required|in:smtp,ses,postmark,sendmail,log',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
            'reply_to_email' => 'nullable|email',
            'bcc_email' => 'nullable|email',
            
            // SMTP fields
            'smtp_host' => 'required_if:mail_driver,smtp|nullable|string',
            'smtp_port' => 'required_if:mail_driver,smtp|nullable|integer',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|in:tls,ssl,none',
            
            // SES fields
            'ses_key' => 'required_if:mail_driver,ses|nullable|string',
            'ses_secret' => 'required_if:mail_driver,ses|nullable|string',
            'ses_region' => 'required_if:mail_driver,ses|nullable|string',
            
            // Postmark fields
            'postmark_token' => 'required_if:mail_driver,postmark|nullable|string',
            
            // Sendmail fields
            'sendmail_path' => 'nullable|string',
        ]);

        $config = EmailConfiguration::first() ?? new EmailConfiguration();
        
        // Only update password if provided (not masked value)
        if ($request->filled('smtp_password') && $request->smtp_password !== '••••••••') {
            $validated['smtp_password'] = $request->smtp_password;
        } else {
            unset($validated['smtp_password']);
        }
        
        // Only update SES credentials if provided
        if ($request->filled('ses_key') && !str_contains($request->ses_key, '•')) {
            $validated['ses_key'] = $request->ses_key;
        } else {
            unset($validated['ses_key']);
        }
        
        if ($request->filled('ses_secret') && $request->ses_secret !== '••••••••') {
            $validated['ses_secret'] = $request->ses_secret;
        } else {
            unset($validated['ses_secret']);
        }
        
        // Only update Postmark token if provided
        if ($request->filled('postmark_token') && $request->postmark_token !== '••••••••') {
            $validated['postmark_token'] = $request->postmark_token;
        } else {
            unset($validated['postmark_token']);
        }

        if ($config->exists) {
            $config->update($validated);
        } else {
            $config->fill($validated);
            $config->save();
        }

        return Redirect::back()->with('success', 'Email configuration saved successfully');
    }

    public function testConnection(Request $request)
    {
        $config = EmailConfiguration::first();

        if (!$config) {
            return Redirect::back()->with('error', 'Please save email configuration first');
        }

        if ($config->testConnection()) {
            return Redirect::back()->with('success', 'Connection test successful! Email settings are working correctly.');
        }

        return Redirect::back()->with('error', 'Connection test failed: ' . $config->last_test_message);
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $config = EmailConfiguration::first();

        if (!$config || !$config->is_active) {
            return Redirect::back()->with('error', 'Email configuration not found or inactive');
        }

        try {
            \Illuminate\Support\Facades\Mail::raw('This is a test email from your Vortex store.', function ($message) use ($request, $config) {
                $message->to($request->email)
                    ->subject('Test Email from ' . $config->mail_from_name)
                    ->from($config->mail_from_address, $config->mail_from_name);
            });

            return Redirect::back()->with('success', 'Test email sent successfully to ' . $request->email);
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function updateTemplate(Request $request, int $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'subject' => 'required|string|max:500',
            'html_content' => 'required|string',
            'plain_text_content' => 'nullable|string',
            'from_name' => 'nullable|string|max:255',
            'from_email' => 'nullable|email',
            'reply_to' => 'nullable|email',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return Redirect::back()->with('success', 'Email template updated successfully');
    }

    public function toggleTemplate(int $id)
    {
        $template = EmailTemplate::findOrFail($id);
        
        $template->update([
            'is_active' => !$template->is_active,
        ]);

        $status = $template->is_active ? 'enabled' : 'disabled';
        return Redirect::back()->with('success', "Email template {$status} successfully");
    }

    public function previewTemplate(int $id)
    {
        $template = EmailTemplate::findOrFail($id);
        $preview = $template->preview();

        return response()->json($preview);
    }

    public function sendTestTemplate(Request $request, int $id)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $template = EmailTemplate::findOrFail($id);
        $sampleData = $template->preview()['html']; // Use preview data

        if ($template->send($request->email)) {
            return Redirect::back()->with('success', 'Test email sent successfully to ' . $request->email);
        }

        return Redirect::back()->with('error', 'Failed to send test email');
    }
}
