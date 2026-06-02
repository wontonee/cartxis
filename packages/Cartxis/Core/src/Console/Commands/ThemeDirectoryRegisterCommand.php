<?php

declare(strict_types=1);

namespace Cartxis\Core\Console\Commands;

use Cartxis\Core\Services\ThemeDirectoryEnvConfigurator;
use Illuminate\Console\Command;

class ThemeDirectoryRegisterCommand extends Command
{
    protected $signature = 'theme:directory:register
                            {--name= : Store name sent to the Cartxis directory}
                            {--url= : Store URL sent to the Cartxis directory}
                            {--force : Register a new key even if one already exists}';

    protected $description = 'Register (or verify) the Cartxis theme directory API key in .env';

    public function handle(ThemeDirectoryEnvConfigurator $configurator): int
    {
        if ($this->option('force') && $configurator->isApiKeyConfigured()) {
            $envPath = base_path('.env');
            $content = file_exists($envPath) ? (string) file_get_contents($envPath) : '';
            $content = preg_replace('/^CARTXIS_THEME_API_KEY=.*\n?/m', '', $content);
            file_put_contents($envPath, $content);
            putenv('CARTXIS_THEME_API_KEY');
        }

        $appName = (string) ($this->option('name') ?: config('app.name', 'Cartxis'));
        $appUrl = (string) ($this->option('url') ?: config('app.url', 'http://localhost'));

        $result = $configurator->ensureConfigured($appName, $appUrl);

        return match ($result['status']) {
            'already_configured' => $this->reportSuccess('Theme directory API key is already saved in .env.'),
            'registered' => $this->reportSuccess($result['message']),
            default => $this->reportFailure($result['message']),
        };
    }

    protected function reportSuccess(string $message): int
    {
        $this->components->info($message);
        $this->line('  Directory URL: '.config('theme.directory.url'));
        $this->line('  Install enabled: '.(app(\Cartxis\Core\Services\RemoteThemeDirectoryClient::class)->canInstall() ? 'yes' : 'no'));

        return self::SUCCESS;
    }

    protected function reportFailure(string $message): int
    {
        $this->components->error($message);
        $this->line('  Check CARTXIS_THEME_DIRECTORY_URL and that cartxis.com/api is reachable.');

        return self::FAILURE;
    }
}
