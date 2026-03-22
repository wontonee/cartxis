<?php

namespace Cartxis\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ExtensionMakeCommand extends Command
{
    protected $signature = 'cartxis:extension:make
        {name? : Extension name (e.g. "Sales Chat") or code (e.g. "sales-chat")}
        {--code= : Explicit extension code (kebab-case)}
        {--template= : Template folder inside extension/templates}
        {--vendor= : PHP namespace vendor segment}
        {--author= : Extension author name}
        {--author-url= : Extension author URL}
        {--interactive : Ask for missing values interactively}
        {--force : Overwrite target folder if it already exists}';

    protected $aliases = ['vortex:extension:make'];

    protected $description = 'Scaffold a new filesystem extension in the /extension directory.';

    public function handle(): int
    {
        $extensionRoot = base_path('extension');
        $templatesRoot = $extensionRoot . '/templates';

        $nameInput = trim((string) ($this->argument('name') ?? ''));
        $codeInput = trim((string) ($this->option('code') ?? ''));
        $templateInput = trim((string) ($this->option('template') ?? ''));
        $vendorInput = trim((string) ($this->option('vendor') ?? ''));
        $authorInput = trim((string) ($this->option('author') ?? ''));
        $authorUrlInput = trim((string) ($this->option('author-url') ?? ''));

        $interactiveRequested = (bool) $this->option('interactive') || ($nameInput === '' && $codeInput === '');

        if ($this->input->isInteractive() && $interactiveRequested) {
            $templates = $this->discoverTemplates($templatesRoot);

            if ($templateInput === '') {
                if (!empty($templates)) {
                    $defaultIndex = array_search('payment-gateway', $templates, true);
                    $templateInput = $this->choice(
                        'Choose extension template',
                        $templates,
                        $defaultIndex === false ? 0 : $defaultIndex
                    );
                } else {
                    $templateInput = 'payment-gateway';
                }
            }

            if ($nameInput === '') {
                $nameInput = trim((string) $this->ask('Extension display name', 'My Extension'));
            }

            if ($codeInput === '') {
                $defaultCode = $this->normalizeCode($nameInput);
                $codeInput = trim((string) $this->ask('Extension code (kebab-case)', $defaultCode));
            }

            if ($vendorInput === '') {
                $vendorInput = trim((string) $this->ask('PHP vendor namespace', 'Cartxis'));
            }

            if ($authorInput === '') {
                $authorInput = trim((string) $this->ask('Author name', 'Cartxis Commerce'));
            }

            if ($authorUrlInput === '') {
                $authorUrlInput = trim((string) $this->ask('Author URL', 'https://cartxiscommerce.com'));
            }
        }

        $template = $templateInput !== '' ? $templateInput : 'payment-gateway';
        $templatePath = $templatesRoot . '/' . $template;

        $code = $this->normalizeCode($codeInput !== '' ? $codeInput : $nameInput);

        if ($code === '') {
            $this->error('Unable to determine extension code. Provide a valid name or --code option.');
            return self::FAILURE;
        }

        $name = $this->normalizeName($nameInput, $code);
        $vendor = $this->normalizeSegment($vendorInput) ?: 'Cartxis';
        $author = $authorInput !== '' ? $authorInput : 'Cartxis Commerce';
        $authorUrl = $authorUrlInput !== '' ? $authorUrlInput : 'https://cartxiscommerce.com';

        if (!File::isDirectory($templatePath)) {
            $this->error("Template not found: {$templatePath}");
            return self::FAILURE;
        }

        $targetPath = $extensionRoot . '/' . $code;
        if (File::exists($targetPath)) {
            if (!$this->option('force')) {
                if ($this->input->isInteractive() && $interactiveRequested) {
                    $overwrite = (bool) $this->confirm("Target already exists: {$targetPath}. Overwrite?", false);
                    if (!$overwrite) {
                        $this->warn('Scaffold cancelled.');
                        return self::FAILURE;
                    }
                } else {
                    $this->error("Target already exists: {$targetPath}. Use --force to overwrite.");
                    return self::FAILURE;
                }
            }

            File::deleteDirectory($targetPath);
        }

        File::makeDirectory($targetPath, 0755, true);
        File::copyDirectory($templatePath, $targetPath);

        $extensionStudly = Str::studly(str_replace('-', ' ', $code));
        $namespace = $vendor . '\\' . $extensionStudly;
        $providerClass = $extensionStudly . 'ServiceProvider';
        $gatewayClass = $extensionStudly . 'Gateway';
        $paymentCode = Str::snake(str_replace('-', ' ', $code));

        $this->rewriteManifest(
            $targetPath . '/extension.json',
            $code,
            $name,
            $author,
            $authorUrl,
            $namespace,
            $providerClass
        );

        $this->renameIfExists(
            $targetPath . '/src/Providers/AcmeExampleServiceProvider.php',
            $targetPath . '/src/Providers/' . $providerClass . '.php'
        );

        $this->renameIfExists(
            $targetPath . '/src/Services/AcmeExampleGateway.php',
            $targetPath . '/src/Services/' . $gatewayClass . '.php'
        );

        $replacements = [
            'Acme\\ExampleGateway' => $namespace,
            'AcmeExampleServiceProvider' => $providerClass,
            'AcmeExampleGateway' => $gatewayClass,
            'acme-example-gateway' => $code,
            'Acme Example Gateway' => $name,
            'Acme Example' => $name,
            'acme_example' => $paymentCode,
            'Acme Inc.' => $author,
            'https://acme.test' => $authorUrl,
        ];

        foreach (File::allFiles($targetPath) as $file) {
            $path = $file->getPathname();
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, ['php', 'json', 'md'], true)) {
                continue;
            }

            $content = File::get($path);
            $updated = str_replace(array_keys($replacements), array_values($replacements), $content);
            if ($updated !== $content) {
                File::put($path, $updated);
            }
        }

        $this->newLine();
        $this->info('Extension scaffold created successfully.');
        $this->line("Path: {$targetPath}");
        $this->line("Code: {$code}");
        $this->line("Provider: {$namespace}\\Providers\\{$providerClass}");
        $this->newLine();
        $this->line('Next steps:');
        $this->line('1) php artisan cartxis:extensions:sync');
        $this->line("2) php artisan cartxis:extensions:install {$code}");
        $this->line("3) php artisan cartxis:extensions:activate {$code}");

        return self::SUCCESS;
    }

    protected function rewriteManifest(
        string $manifestPath,
        string $code,
        string $name,
        string $author,
        string $authorUrl,
        string $namespace,
        string $providerClass
    ): void {
        if (!File::exists($manifestPath)) {
            return;
        }

        $manifest = json_decode((string) File::get($manifestPath), true) ?: [];

        $manifest['code'] = $code;
        $manifest['name'] = $name;
        $manifest['author'] = $author;
        $manifest['author_url'] = $authorUrl;
        $manifest['provider'] = $namespace . '\\Providers\\' . $providerClass;
        $manifest['provider_file'] = 'src/Providers/' . $providerClass . '.php';

        File::put(
            $manifestPath,
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );
    }

    protected function renameIfExists(string $from, string $to): void
    {
        if (File::exists($from)) {
            File::move($from, $to);
        }
    }

    protected function normalizeCode(string $value): string
    {
        $value = Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->value();

        return $value;
    }

    protected function normalizeName(string $nameInput, string $code): string
    {
        $nameInput = trim($nameInput);

        if ($nameInput !== '' && preg_match('/[\s]/', $nameInput)) {
            return Str::of($nameInput)->squish()->value();
        }

        return Str::title(str_replace('-', ' ', $code));
    }

    protected function normalizeSegment(string $value): string
    {
        $clean = Str::of($value)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', ' ')
            ->squish()
            ->value();

        return Str::studly($clean);
    }

    /**
     * @return array<int, string>
     */
    protected function discoverTemplates(string $templatesRoot): array
    {
        if (!File::isDirectory($templatesRoot)) {
            return [];
        }

        return collect(File::directories($templatesRoot))
            ->map(fn (string $path) => basename($path))
            ->sort()
            ->values()
            ->all();
    }
}
