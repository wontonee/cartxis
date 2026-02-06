<?php

declare(strict_types=1);

namespace Cartxis\System\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    /**
     * Get all backups from all configured destinations.
     */
    public function getBackups(): Collection
    {
        $disks = config('backup.backup.destination.disks', []);
        $backupName = config('backup.backup.name');
        
        $allBackups = collect();
        
        foreach ($disks as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                
                // Get all zip files in the backup directory
                $backupPath = $backupName;
                
                if ($disk->exists($backupPath)) {
                    $files = $disk->files($backupPath);
                    
                    foreach ($files as $file) {
                        if (str_ends_with($file, '.zip')) {
                            $allBackups->push([
                                'path' => $file,
                                'date' => date('Y-m-d H:i:s', $disk->lastModified($file)),
                                'size' => $this->formatBytes($disk->size($file)),
                                'disk' => $diskName,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $allBackups->sortByDesc('date')->values();
    }

    /**
     * Create a new backup.
     *
     * Uses Spatie backup:run when mysqldump is available.
     * Falls back to a PHP-native database dump otherwise.
     */
    public function createBackup(string $option = ''): void
    {
        $needsDb    = $option !== 'only-files';
        $needsFiles = $option !== 'only-db';
        $hasMysqldump = $this->isMysqldumpAvailable();

        // If mysqldump is available, use Spatie for everything
        if ($hasMysqldump) {
            $this->runSpatieBackup($option);
            return;
        }

        // mysqldump NOT available — handle each scenario

        if ($needsDb && !$needsFiles) {
            // Database-only: pure PHP dump
            $this->createPhpDatabaseBackup();
            return;
        }

        if ($needsFiles && !$needsDb) {
            // Files-only: Spatie can handle this without mysqldump
            $this->runSpatieBackup('only-files');
            return;
        }

        // Full backup (db + files): run Spatie for files, then inject PHP sql dump into the zip
        $this->runSpatieBackup('only-files');
        $this->injectDatabaseDumpIntoLatestBackup();
    }

    /**
     * Delete a specific backup.
     */
    public function deleteBackup(string $disk, string $path): void
    {
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * Get the absolute path for download.
     */
    public function getBackupPath(string $disk, string $path): ?string
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->path($path);
        }
        return null;
    }

    /**
     * Clean old backups.
     */
    public function cleanBackups(): void
    {
        Artisan::call('backup:clean');
    }

    // ─── Internal helpers ───────────────────────────────────────────────

    /**
     * Check whether mysqldump is available on the system.
     */
    protected function isMysqldumpAvailable(): bool
    {
        $binary = config('database.connections.mysql.dump.dump_binary_path', '');
        $cmd    = $binary ? rtrim($binary, '/') . '/mysqldump' : 'mysqldump';

        // Silence stderr; just check exit code
        exec($cmd . ' --version 2>/dev/null', $output, $exitCode);

        return $exitCode === 0;
    }

    /**
     * Run the Spatie backup:run artisan command.
     */
    protected function runSpatieBackup(string $option = ''): void
    {
        $params = ['--disable-notifications' => true];

        if ($option === 'only-db') {
            $params['--only-db'] = true;
        } elseif ($option === 'only-files') {
            $params['--only-files'] = true;
        }

        Artisan::call('backup:run', $params);
        $this->normalizeBackupArchivePaths();
    }

    /**
     * Create a database-only backup using pure PHP (no mysqldump required).
     */
    protected function createPhpDatabaseBackup(): void
    {
        $sql  = $this->generateDatabaseDump();
        $disk = $this->getDestinationDisk();
        $backupName = config('backup.backup.name');

        $filename = date('Y-m-d-H-i-s') . '.zip';
        $zipRelativePath = $backupName . '/' . $filename;
        $zipAbsolutePath = Storage::disk($disk)->path($zipRelativePath);

        // Ensure directory exists
        $dir = dirname($zipAbsolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipAbsolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create backup zip archive.');
        }

        $dbName = config('database.connections.mysql.database', 'database');
        $zip->addFromString('db-dumps/' . $dbName . '-mysql.sql', $sql);
        $zip->close();
    }

    /**
     * Inject a PHP-generated SQL dump into the most recent backup zip.
     */
    protected function injectDatabaseDumpIntoLatestBackup(): void
    {
        $disk = $this->getDestinationDisk();
        $backupName = config('backup.backup.name');
        $storage = Storage::disk($disk);

        if (!$storage->exists($backupName)) {
            return;
        }

        $latestZip = collect($storage->files($backupName))
            ->filter(fn(string $f) => str_ends_with($f, '.zip'))
            ->sortByDesc(fn(string $f) => $storage->lastModified($f))
            ->first();

        if (!$latestZip) {
            return;
        }

        $zipPath = $storage->path($latestZip);
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            return;
        }

        $sql = $this->generateDatabaseDump();
        $dbName = config('database.connections.mysql.database', 'database');
        $zip->addFromString('db-dumps/' . $dbName . '-mysql.sql', $sql);
        $zip->close();
    }

    /**
     * Generate a full SQL dump of the MySQL database using PDO.
     * Produces standard SQL that can be imported via mysql CLI or phpMyAdmin.
     */
    protected function generateDatabaseDump(): string
    {
        $pdo = DB::connection()->getPdo();
        $dbName = config('database.connections.mysql.database');

        $lines = [];
        $lines[] = '-- Cartxis Database Backup';
        $lines[] = '-- Generated: ' . date('Y-m-d H:i:s');
        $lines[] = '-- Database: ' . $dbName;
        $lines[] = '-- PHP-native dump (no mysqldump required)';
        $lines[] = '';
        $lines[] = 'SET NAMES utf8mb4;';
        $lines[] = 'SET FOREIGN_KEY_CHECKS = 0;';
        $lines[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
        $lines[] = 'SET time_zone = "+00:00";';
        $lines[] = '';

        // Get all tables
        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Table structure
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $createSql = $createStmt['Create Table'] ?? $createStmt['Create View'] ?? null;

            if (!$createSql) {
                continue;
            }

            $lines[] = '-- ---------------------------------------------------';
            $lines[] = "-- Table: `{$table}`";
            $lines[] = '-- ---------------------------------------------------';
            $lines[] = "DROP TABLE IF EXISTS `{$table}`;";
            $lines[] = $createSql . ';';
            $lines[] = '';

            // Check if it's a view (no data to dump)
            if (isset($createStmt['Create View'])) {
                continue;
            }

            // Table data — stream in chunks to keep memory low
            $count = (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();

            if ($count === 0) {
                $lines[] = "-- (empty table)";
                $lines[] = '';
                continue;
            }

            $chunkSize = 500;
            $offset = 0;

            $lines[] = "LOCK TABLES `{$table}` WRITE;";

            while ($offset < $count) {
                $rows = $pdo->query("SELECT * FROM `{$table}` LIMIT {$chunkSize} OFFSET {$offset}")->fetchAll(\PDO::FETCH_ASSOC);

                if (empty($rows)) {
                    break;
                }

                $columns = array_keys($rows[0]);
                $columnList = implode('`, `', $columns);

                $valueGroups = [];
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = $pdo->quote((string) $value);
                        }
                    }
                    $valueGroups[] = '(' . implode(', ', $values) . ')';
                }

                $lines[] = "INSERT INTO `{$table}` (`{$columnList}`) VALUES";
                $lines[] = implode(",\n", $valueGroups) . ';';

                $offset += $chunkSize;
            }

            $lines[] = "UNLOCK TABLES;";
            $lines[] = '';
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';
        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * Get the first configured backup destination disk.
     */
    protected function getDestinationDisk(): string
    {
        $disks = config('backup.backup.destination.disks', ['local']);
        return $disks[0] ?? 'local';
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        
        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Normalize ZIP entry paths to use forward slashes so folders render correctly on Windows.
     */
    protected function normalizeBackupArchivePaths(): void
    {
        $disks = config('backup.backup.destination.disks', []);
        $backupName = config('backup.backup.name');

        foreach ($disks as $diskName) {
            $disk = Storage::disk($diskName);

            if (! method_exists($disk, 'path')) {
                continue; // Only supported for local disks
            }

            if (! $disk->exists($backupName)) {
                continue;
            }

            $zipFiles = collect($disk->files($backupName))
                ->filter(fn (string $file) => str_ends_with($file, '.zip'));

            if ($zipFiles->isEmpty()) {
                continue;
            }

            $latestZip = $zipFiles
                ->sortByDesc(fn (string $file) => $disk->lastModified($file))
                ->first();

            if (! $latestZip) {
                continue;
            }

            $zipPath = $disk->path($latestZip);
            $zip = new ZipArchive();

            if ($zip->open($zipPath) !== true) {
                continue;
            }

            $needsFix = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (isset($stat['name']) && str_contains($stat['name'], '\\')) {
                    $needsFix = true;
                    break;
                }
            }

            if (! $needsFix) {
                $zip->close();
                continue;
            }

            $tmpPath = $zipPath . '.tmp';
            $newZip = new ZipArchive();

            if ($newZip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                $zip->close();
                continue;
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (! isset($stat['name'])) {
                    continue;
                }

                $contents = $zip->getFromIndex($i);
                if ($contents === false) {
                    continue;
                }

                $normalizedName = str_replace('\\', '/', $stat['name']);
                $newZip->addFromString($normalizedName, $contents);
            }

            $zip->close();
            $newZip->close();

            @rename($tmpPath, $zipPath);
        }
    }
}
