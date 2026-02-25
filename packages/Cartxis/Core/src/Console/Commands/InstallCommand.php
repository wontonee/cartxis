<?php

declare(strict_types=1);

namespace Cartxis\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;

class InstallCommand extends Command
{
    protected $signature = 'cartxis:install';

    protected $description = 'Run the Cartxis installation wizard';

    public function handle(): int
    {
        $this->renderWelcomeBanner();

        // ── Step 1: .env ────────────────────────────────────────────────────
        $this->ensureEnvFile();

        // ── Step 2: App settings ─────────────────────────────────────────────
        $appName = $this->ask('App name', 'Cartxis');
        $appUrl  = $this->ask('App URL', 'http://localhost:8000');
        $this->writeEnvValue('APP_NAME', $appName);
        $this->writeEnvValue('APP_URL', $appUrl);
        $this->writeEnvValue('ASSET_URL', $appUrl);

        $this->newLine();

        // ── Step 3: Database ─────────────────────────────────────────────────
        $this->line('<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</fg=yellow>');
        $this->line('<fg=yellow>  Database Configuration</fg=yellow>');
        $this->line('<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</fg=yellow>');

        $dbConnected = false;

        while (! $dbConnected) {
            $dbHost     = $this->ask('DB host', '127.0.0.1');
            $dbPort     = $this->ask('DB port', '3306');
            $dbName     = $this->ask('DB database');
            $dbUser     = $this->ask('DB username', 'root');
            $dbPassword = $this->secret('DB password (leave blank for none)') ?? '';

            $dbConnected = $this->testDatabaseConnection($dbHost, (int) $dbPort, $dbName, $dbUser, $dbPassword);

            if (! $dbConnected) {
                $this->error('Could not connect to the database. Please check your credentials and try again.');

                if (! $this->confirm('Try again?', true)) {
                    $this->error('Installation aborted.');
                    return self::FAILURE;
                }
            }
        }

        $this->writeEnvValue('DB_HOST', $dbHost);
        $this->writeEnvValue('DB_PORT', $dbPort);
        $this->writeEnvValue('DB_DATABASE', $dbName);
        $this->writeEnvValue('DB_USERNAME', $dbUser);
        $this->writeEnvValue('DB_PASSWORD', $dbPassword);

        $this->line('  <fg=green>✔</fg=green> Database connection successful');
        $this->newLine();

        // ── Step 4: App key ──────────────────────────────────────────────────
        if (empty(config('app.key'))) {
            $this->call('key:generate', ['--ansi' => true]);
        } else {
            $this->line('  <fg=green>✔</fg=green> App key already set, skipping');
        }

        // Reload config so DB connection uses new values
        $this->resetDatabaseConfig($dbHost, (int) $dbPort, $dbName, $dbUser, $dbPassword);

        // ── Step 5: Migrations ───────────────────────────────────────────────
        $this->newLine();
        $this->line('<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</fg=yellow>');
        $this->line('<fg=yellow>  Running Migrations</fg=yellow>');
        $this->line('<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</fg=yellow>');
        $this->call('migrate', ['--force' => true]);

        // ── Step 6: Admin credentials ────────────────────────────────────────
        $this->newLine();
        $this->line('<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</fg=yellow>');
        $this->line('<fg=yellow>  Admin Account</fg=yellow>');
        $this->line('<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</fg=yellow>');

        $adminName     = $this->ask('Admin name', 'Admin');
        $adminEmail    = $this->ask('Admin email', 'admin@example.com');
        $adminPassword = $this->askForPassword();

        // Write temp env vars so AdminUserSeeder can pick them up
        $this->writeEnvValue('CARTXIS_ADMIN_NAME', $adminName);
        $this->writeEnvValue('CARTXIS_ADMIN_EMAIL', $adminEmail);
        $this->writeEnvValue('CARTXIS_ADMIN_PASSWORD', $adminPassword);

        // Also inject into the current process environment so env() resolves
        // them immediately (writeEnvValue only updates the .env file on disk).
        putenv("CARTXIS_ADMIN_NAME={$adminName}");
        putenv("CARTXIS_ADMIN_EMAIL={$adminEmail}");
        putenv("CARTXIS_ADMIN_PASSWORD={$adminPassword}");

        // ── Step 7: Seed ─────────────────────────────────────────────────────
        $this->newLine();
        $this->line('<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</fg=yellow>');
        $this->line('<fg=yellow>  Seeding Database</fg=yellow>');
        $this->line('<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</fg=yellow>');
        $this->call('db:seed', ['--class' => 'Cartxis\\Core\\Database\\Seeders\\DatabaseSeeder', '--force' => true]);

        // Remove temp admin env vars (they've been used by the seeder)
        $this->removeEnvValue('CARTXIS_ADMIN_NAME');
        $this->removeEnvValue('CARTXIS_ADMIN_EMAIL');
        $this->removeEnvValue('CARTXIS_ADMIN_PASSWORD');

        // Clear from process environment too
        putenv('CARTXIS_ADMIN_NAME');
        putenv('CARTXIS_ADMIN_EMAIL');
        putenv('CARTXIS_ADMIN_PASSWORD');

        // ── Step 8: Storage link ─────────────────────────────────────────────
        $this->newLine();
        $this->call('storage:link');

        // ── Step 9: Frontend assets ──────────────────────────────────────────
        $this->newLine();
        $this->line('<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</fg=yellow>');
        $this->line('<fg=yellow>  Frontend Assets</fg=yellow>');
        $this->line('<fg=yellow>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</fg=yellow>');

        $npmBin = $this->detectNodePackageManager();

        if ($npmBin && $this->confirm("Build frontend assets now using {$npmBin}? (requires Node.js)", true)) {
            $this->line("  Running {$npmBin} install...");
            passthru("{$npmBin} install");
            $this->line("  Running {$npmBin} run build...");
            passthru("{$npmBin} run build");
            $this->line('  <fg=green>✔</fg=green> Frontend assets built');
        } else {
            $this->line('  Skipped. Run <fg=cyan>npm install && npm run build</fg=cyan> manually when ready.');
        }

        // ── Done ─────────────────────────────────────────────────────────────
        $this->renderSuccessBanner($appUrl, $adminEmail, $adminPassword);

        return self::SUCCESS;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function renderWelcomeBanner(): void
    {
        $this->newLine();
        $this->line('<fg=cyan> ██████╗ █████╗ ██████╗ ████████╗██╗  ██╗██╗███████╗</fg=cyan>');
        $this->line('<fg=cyan>██╔════╝██╔══██╗██╔══██╗╚══██╔══╝╚██╗██╔╝██║██╔════╝</fg=cyan>');
        $this->line('<fg=cyan>██║     ███████║██████╔╝   ██║    ╚███╔╝ ██║███████╗</fg=cyan>');
        $this->line('<fg=cyan>██║     ██╔══██║██╔══██╗   ██║    ██╔██╗ ██║╚════██║</fg=cyan>');
        $this->line('<fg=cyan>╚██████╗██║  ██║██║  ██║   ██║   ██╔╝ ██╗██║███████║</fg=cyan>');
        $this->line('<fg=cyan> ╚═════╝╚═╝  ╚═╝╚═╝  ╚═╝   ╚═╝   ╚═╝  ╚═╝╚═╝╚══════╝</fg=cyan>');
        $this->newLine();
        $this->line('<fg=green>  Open Source Laravel eCommerce Platform</fg=green>');
        $this->line('<fg=gray>  Version ' . config('app.version', '1.0.0') . '  •  https://cartxis.com</fg=gray>');
        $this->newLine();
        $this->line('<fg=yellow>════════════════════════════════════════════════════</fg=yellow>');
        $this->line('<fg=yellow>   Welcome to the Cartxis Installation Wizard</fg=yellow>');
        $this->line('<fg=yellow>════════════════════════════════════════════════════</fg=yellow>');
        $this->newLine();
    }

    private function renderSuccessBanner(string $appUrl, string $adminEmail, string $adminPassword): void
    {
        $this->newLine();
        $this->line('<fg=green>════════════════════════════════════════════════════</fg=green>');
        $this->line('<fg=green>  ✔  Cartxis installation complete!</fg=green>');
        $this->line('<fg=green>════════════════════════════════════════════════════</fg=green>');
        $this->newLine();
        $this->line('  Admin panel : <fg=cyan>' . rtrim($appUrl, '/') . '/admin/login</fg=cyan>');
        $this->line('  Email       : <fg=cyan>' . $adminEmail . '</fg=cyan>');
        $this->line('  Password    : <fg=cyan>' . $adminPassword . '</fg=cyan>');
        $this->newLine();
        $this->line('  To start the development server run:');
        $this->line('  <fg=cyan>  composer run dev</fg=cyan>');
        $this->newLine();
    }

    private function ensureEnvFile(): void
    {
        $envPath     = base_path('.env');
        $examplePath = base_path('.env.example');

        if (! file_exists($envPath)) {
            if (file_exists($examplePath)) {
                copy($examplePath, $envPath);
                $this->line('  <fg=green>✔</fg=green> Created .env from .env.example');
            } else {
                file_put_contents($envPath, '');
                $this->line('  <fg=yellow>!</fg=yellow> No .env.example found, created empty .env');
            }
        } else {
            $this->line('  <fg=green>✔</fg=green> .env file already exists');
        }

        $this->newLine();
    }

    private function testDatabaseConnection(
        string $host,
        int $port,
        string $database,
        string $username,
        string $password
    ): bool {
        try {
            new PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
            );
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    private function resetDatabaseConfig(
        string $host,
        int $port,
        string $database,
        string $username,
        string $password
    ): void {
        config([
            'database.connections.mysql.host'     => $host,
            'database.connections.mysql.port'     => $port,
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.username' => $username,
            'database.connections.mysql.password' => $password,
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    private function writeEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $content = file_exists($envPath) ? file_get_contents($envPath) : '';

        // Escape double quotes in value
        $safeValue = str_contains($value, ' ') ? '"' . addslashes($value) . '"' : $value;
        $line      = "{$key}={$safeValue}";

        if (preg_match("/^{$key}=.*/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", $line, $content);
        } else {
            $content .= PHP_EOL . $line;
        }

        file_put_contents($envPath, $content);
    }

    private function removeEnvValue(string $key): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);
        $content = preg_replace("/^{$key}=.*\n?/m", '', $content);
        file_put_contents($envPath, $content);
    }

    private function askForPassword(): string
    {
        while (true) {
            $password = $this->secret('Admin password (min 8 characters)');

            if (! $password || strlen($password) < 8) {
                $this->error('Password must be at least 8 characters.');
                continue;
            }

            $confirm = $this->secret('Confirm admin password');

            if ($password !== $confirm) {
                $this->error('Passwords do not match. Try again.');
                continue;
            }

            return $password;
        }
    }

    private function detectNodePackageManager(): ?string
    {
        foreach (['pnpm', 'yarn', 'npm'] as $bin) {
            exec("which {$bin} 2>/dev/null", $output, $code);

            if ($code === 0 && ! empty($output)) {
                return $bin;
            }

            $output = [];
        }

        return null;
    }
}
