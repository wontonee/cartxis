<?php

declare(strict_types=1);

namespace Cartxis\System\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
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
                // Skip this disk if there's an error
                continue;
            }
        }
        
        return $allBackups->sortByDesc('date')->values();
    }

    /**
     * Create a new backup.
     */
    public function createBackup(string $option = ''): void
    {
        $command = 'backup:run';
        
        // $option parameters: 'only-db', 'only-files', '' (both)
        $params = [];
        if ($option === 'only-db') {
            $params['--only-db'] = true;
        } elseif ($option === 'only-files') {
            $params['--only-files'] = true;
        }

        $params['--disable-notifications'] = true;

        Artisan::call($command, $params);
        $this->normalizeBackupArchivePaths();
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
