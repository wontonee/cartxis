<?php

declare(strict_types=1);

namespace Cartxis\System\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Cartxis\Settings\Models\Setting;
use Cartxis\System\Jobs\DisableScheduledMaintenance;
use Cartxis\System\Jobs\EnableScheduledMaintenance;
use Cartxis\System\Models\MaintenanceLog;

class MaintenanceService
{
    /**
     * Enable maintenance mode
     */
    public function enable(array $data): array
    {
        $secret = $data['secret'] ?? Str::random(32);
        
        // Save settings
        Setting::setMany([
            'system.maintenance_enabled' => true,
            'system.maintenance_title' => $data['title'] ?? 'We\'ll be back soon!',
            'system.maintenance_message' => $data['message'] ?? 'We are performing scheduled maintenance.',
            'system.maintenance_retry_after' => $data['retry_after'] ?? 3600,
            'system.maintenance_secret' => $secret,
            'system.maintenance_allowed_ips' => json_encode($data['allowed_ips'] ?? []),
            'system.maintenance_contact_email' => $data['contact_email'] ?? '',
        ]);
        
        // Put application in maintenance mode
        $this->executeDown($secret, $data);
        
        // Log the action
        MaintenanceLog::create([
            'action' => 'enabled',
            'reason' => $data['message'] ?? 'Manual maintenance mode activation',
            'actual_start' => now(),
            'admin_id' => auth('admin')->id(),
            'admin_name' => auth('admin')->user()?->name ?? 'System',
            'ip_address' => request()->ip(),
        ]);
        
        return [
            'secret' => $secret,
            'bypass_url' => url($secret),
        ];
    }
    
    /**
     * Disable maintenance mode
     */
    public function disable(): void
    {
        Setting::set('system.maintenance_enabled', false);
        
        Artisan::call('up');
        
        // Update log
        $log = MaintenanceLog::where('action', 'enabled')
            ->whereNull('actual_end')
            ->latest()
            ->first();
            
        if ($log) {
            $log->update(['actual_end' => now()]);
        }
        
        // Log the disable action
        MaintenanceLog::create([
            'action' => 'disabled',
            'reason' => 'Manual maintenance mode deactivation',
            'actual_start' => now(),
            'admin_id' => auth('admin')->id(),
            'admin_name' => auth('admin')->user()?->name ?? 'System',
            'ip_address' => request()->ip(),
        ]);
    }
    
    /**
     * Schedule maintenance
     */
    public function schedule(array $data): void
    {
        // Save scheduled times
        Setting::setMany([
            'system.maintenance_start_time' => $data['start_time'],
            'system.maintenance_end_time' => $data['end_time'],
        ]);
        
        // Log the schedule
        MaintenanceLog::create([
            'action' => 'scheduled',
            'reason' => $data['message'] ?? 'Scheduled maintenance',
            'scheduled_start' => $data['start_time'],
            'scheduled_end' => $data['end_time'],
            'admin_id' => auth('admin')->id(),
            'admin_name' => auth('admin')->user()?->name ?? 'System',
            'ip_address' => request()->ip(),
        ]);
        
        // Schedule jobs
        if (isset($data['start_time'])) {
            EnableScheduledMaintenance::dispatch($data)
                ->delay($data['start_time']);
        }
            
        if (isset($data['end_time'])) {
            DisableScheduledMaintenance::dispatch()
                ->delay($data['end_time']);
        }
    }
    
    /**
     * Get current maintenance status
     */
    public function getStatus(): array
    {
        return [
            'enabled' => (bool) Setting::get('system.maintenance_enabled', false),
            'title' => Setting::get('system.maintenance_title', 'We\'ll be back soon!'),
            'message' => Setting::get('system.maintenance_message', ''),
            'retry_after' => (int) Setting::get('system.maintenance_retry_after', 3600),
            'secret' => Setting::get('system.maintenance_secret', ''),
            'allowed_ips' => json_decode(Setting::get('system.maintenance_allowed_ips', '[]'), true),
            'bypass_admin' => (bool) Setting::get('system.maintenance_bypass_admin', true),
            'contact_email' => Setting::get('system.maintenance_contact_email', ''),
            'show_eta' => (bool) Setting::get('system.maintenance_show_eta', true),
            'start_time' => Setting::get('system.maintenance_start_time', ''),
            'end_time' => Setting::get('system.maintenance_end_time', ''),
        ];
    }
    
    /**
     * Update maintenance settings
     */
    public function updateSettings(array $data): void
    {
        $settings = [];
        
        if (isset($data['title'])) {
            $settings['system.maintenance_title'] = $data['title'];
        }
        
        if (isset($data['message'])) {
            $settings['system.maintenance_message'] = $data['message'];
        }
        
        if (isset($data['retry_after'])) {
            $settings['system.maintenance_retry_after'] = $data['retry_after'];
        }
        
        if (isset($data['allowed_ips'])) {
            $settings['system.maintenance_allowed_ips'] = json_encode($data['allowed_ips']);
        }
        
        if (isset($data['bypass_admin'])) {
            $settings['system.maintenance_bypass_admin'] = $data['bypass_admin'];
        }
        
        if (isset($data['contact_email'])) {
            $settings['system.maintenance_contact_email'] = $data['contact_email'];
        }
        
        if (isset($data['show_eta'])) {
            $settings['system.maintenance_show_eta'] = $data['show_eta'];
        }
        
        if (! empty($settings)) {
            Setting::setMany($settings);
        }
    }
    
    /**
     * Regenerate secret token
     */
    public function regenerateSecret(): string
    {
        $secret = Str::random(32);
        Setting::set('system.maintenance_secret', $secret);
        
        return $secret;
    }
    
    /**
     * Get maintenance history
     */
    public function getHistory(int $limit = 10): array
    {
        return MaintenanceLog::latest()
            ->take($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'reason' => $log->reason,
                    'scheduled_start' => $log->scheduled_start?->format('Y-m-d H:i:s'),
                    'scheduled_end' => $log->scheduled_end?->format('Y-m-d H:i:s'),
                    'actual_start' => $log->actual_start?->format('Y-m-d H:i:s'),
                    'actual_end' => $log->actual_end?->format('Y-m-d H:i:s'),
                    'duration' => $this->calculateDuration($log),
                    'admin_name' => $log->admin_name,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }
    
    /**
     * Execute Laravel down command
     */
    protected function executeDown(string $secret, array $data): void
    {
        $params = [
            '--secret' => $secret,
            '--retry' => $data['retry_after'] ?? 3600,
        ];
        
        // Add redirect if specified
        if (isset($data['redirect'])) {
            $params['--redirect'] = $data['redirect'];
        }
        
        // Add refresh interval if specified
        if (isset($data['refresh'])) {
            $params['--refresh'] = $data['refresh'];
        }
        
        // Add status code if specified
        if (isset($data['status'])) {
            $params['--status'] = $data['status'];
        }
        
        Artisan::call('down', $params);
    }
    
    /**
     * Calculate duration between start and end
     */
    protected function calculateDuration($log): ?string
    {
        if (! $log->actual_start) {
            return null;
        }
        
        $end = $log->actual_end ?? now();
        $diff = $log->actual_start->diff($end);
        
        $parts = [];
        if ($diff->d > 0) {
            $parts[] = $diff->d . ' day' . ($diff->d !== 1 ? 's' : '');
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' hour' . ($diff->h !== 1 ? 's' : '');
        }
        if ($diff->i > 0 && $diff->d === 0) {
            $parts[] = $diff->i . ' minute' . ($diff->i !== 1 ? 's' : '');
        }
        
        return empty($parts) ? '< 1 minute' : implode(' ', $parts);
    }
}
