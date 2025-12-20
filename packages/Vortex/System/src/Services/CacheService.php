<?php

declare(strict_types=1);

namespace Vortex\System\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    /**
     * Get cache statistics
     */
    public function getStatistics(): array
    {
        $driver = config('cache.default');
        
        return [
            'driver' => $driver,
            'total_size' => $this->getTotalSize(),
            'total_keys' => $this->getTotalKeys(),
            'hit_rate' => $this->getHitRate(),
            'miss_rate' => $this->getMissRate(),
            'uptime' => $this->getUptime(),
            'memory_usage' => $this->getMemoryUsage(),
            'cache_types' => $this->getCacheTypes(),
        ];
    }
    
    /**
     * Clear specified cache types
     */
    public function clearCache(array $types): array
    {
        $cleared = [];
        
        foreach ($types as $type) {
            try {
                $this->clearCacheType($type);
                $cleared[] = $type;
            } catch (\Exception $e) {
                Log::error("Failed to clear {$type} cache: " . $e->getMessage());
            }
        }
        
        return $cleared;
    }
    
    /**
     * Rebuild specified cache types
     */
    public function rebuildCache(array $types): array
    {
        $rebuilt = [];
        
        foreach ($types as $type) {
            try {
                $this->rebuildCacheType($type);
                $rebuilt[] = $type;
            } catch (\Exception $e) {
                Log::error("Failed to rebuild {$type} cache: " . $e->getMessage());
            }
        }
        
        return $rebuilt;
    }
    
    /**
     * Clear specific cache type
     */
    protected function clearCacheType(string $type): void
    {
        match ($type) {
            'application' => Artisan::call('cache:clear'),
            'config' => Artisan::call('config:clear'),
            'route' => Artisan::call('route:clear'),
            'view' => Artisan::call('view:clear'),
            'event' => Artisan::call('event:clear'),
            default => null,
        };
    }
    
    /**
     * Rebuild specific cache type
     */
    protected function rebuildCacheType(string $type): void
    {
        match ($type) {
            'config' => Artisan::call('config:cache'),
            'route' => Artisan::call('route:cache'),
            'event' => Artisan::call('event:cache'),
            default => null,
        };
    }
    
    /**
     * Get total cache size
     */
    protected function getTotalSize(): string
    {
        $driver = config('cache.default');
        
        if ($driver === 'file') {
            return $this->getFilesCacheSize();
        }
        
        if ($driver === 'redis') {
            return $this->getRedisCacheSize();
        }
        
        return 'N/A';
    }
    
    /**
     * Get file cache size
     */
    protected function getFilesCacheSize(): string
    {
        $path = storage_path('framework/cache/data');
        
        if (! File::exists($path)) {
            return '0 B';
        }
        
        $size = 0;
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }
        
        return $this->formatBytes($size);
    }
    
    /**
     * Get Redis cache size
     */
    protected function getRedisCacheSize(): string
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info('memory');
            $bytes = $info['used_memory'] ?? 0;
            
            return $this->formatBytes((int) $bytes);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
    
    /**
     * Get total number of cache keys
     */
    protected function getTotalKeys(): int
    {
        $driver = config('cache.default');
        
        if ($driver === 'file') {
            $path = storage_path('framework/cache/data');
            if (! File::exists($path)) {
                return 0;
            }
            return count(File::allFiles($path));
        }
        
        if ($driver === 'redis') {
            try {
                $redis = Redis::connection();
                return (int) $redis->dbsize();
            } catch (\Exception $e) {
                return 0;
            }
        }
        
        return 0;
    }
    
    /**
     * Get cache hit rate
     */
    protected function getHitRate(): ?string
    {
        $driver = config('cache.default');
        
        if ($driver === 'redis') {
            try {
                $redis = Redis::connection();
                $info = $redis->info('stats');
                
                $hits = (int) ($info['keyspace_hits'] ?? 0);
                $misses = (int) ($info['keyspace_misses'] ?? 0);
                $total = $hits + $misses;
                
                if ($total === 0) {
                    return '0%';
                }
                
                $rate = ($hits / $total) * 100;
                return number_format($rate, 1) . '%';
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Get cache miss rate
     */
    protected function getMissRate(): ?string
    {
        $driver = config('cache.default');
        
        if ($driver === 'redis') {
            try {
                $redis = Redis::connection();
                $info = $redis->info('stats');
                
                $hits = (int) ($info['keyspace_hits'] ?? 0);
                $misses = (int) ($info['keyspace_misses'] ?? 0);
                $total = $hits + $misses;
                
                if ($total === 0) {
                    return '0%';
                }
                
                $rate = ($misses / $total) * 100;
                return number_format($rate, 1) . '%';
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Get cache uptime
     */
    protected function getUptime(): ?string
    {
        $driver = config('cache.default');
        
        if ($driver === 'redis') {
            try {
                $redis = Redis::connection();
                $info = $redis->info('server');
                $seconds = (int) ($info['uptime_in_seconds'] ?? 0);
                
                return $this->formatUptime($seconds);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Get memory usage
     */
    protected function getMemoryUsage(): ?string
    {
        $driver = config('cache.default');
        
        if ($driver === 'redis') {
            try {
                $redis = Redis::connection();
                $info = $redis->info('memory');
                
                $used = (int) ($info['used_memory'] ?? 0);
                $max = (int) ($info['maxmemory'] ?? 0);
                
                if ($max === 0) {
                    $max = (int) ($info['total_system_memory'] ?? 0);
                }
                
                $percentage = $max > 0 ? ($used / $max) * 100 : 0;
                
                return sprintf(
                    '%s / %s (%s)',
                    $this->formatBytes($used),
                    $this->formatBytes($max),
                    number_format($percentage, 1) . '%'
                );
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Get cache types information
     */
    protected function getCacheTypes(): array
    {
        return [
            'application' => [
                'size' => $this->getTotalSize(),
                'keys' => $this->getTotalKeys(),
            ],
            'config' => [
                'size' => $this->getConfigCacheSize(),
                'keys' => File::exists(base_path('bootstrap/cache/config.php')) ? 1 : 0,
            ],
            'route' => [
                'size' => $this->getRouteCacheSize(),
                'keys' => File::exists(base_path('bootstrap/cache/routes-v7.php')) ? 1 : 0,
            ],
            'view' => [
                'size' => $this->getViewCacheSize(),
                'keys' => $this->getViewCacheCount(),
            ],
            'event' => [
                'size' => $this->getEventCacheSize(),
                'keys' => File::exists(base_path('bootstrap/cache/events.php')) ? 1 : 0,
            ],
        ];
    }
    
    /**
     * Get config cache size
     */
    protected function getConfigCacheSize(): string
    {
        $file = base_path('bootstrap/cache/config.php');
        if (! File::exists($file)) {
            return '0 B';
        }
        return $this->formatBytes(File::size($file));
    }
    
    /**
     * Get route cache size
     */
    protected function getRouteCacheSize(): string
    {
        $file = base_path('bootstrap/cache/routes-v7.php');
        if (! File::exists($file)) {
            return '0 B';
        }
        return $this->formatBytes(File::size($file));
    }
    
    /**
     * Get view cache size
     */
    protected function getViewCacheSize(): string
    {
        $path = storage_path('framework/views');
        if (! File::exists($path)) {
            return '0 B';
        }
        
        $size = 0;
        foreach (File::allFiles($path) as $file) {
            $size += $file->getSize();
        }
        
        return $this->formatBytes($size);
    }
    
    /**
     * Get view cache count
     */
    protected function getViewCacheCount(): int
    {
        $path = storage_path('framework/views');
        if (! File::exists($path)) {
            return 0;
        }
        return count(File::allFiles($path));
    }
    
    /**
     * Get event cache size
     */
    protected function getEventCacheSize(): string
    {
        $file = base_path('bootstrap/cache/events.php');
        if (! File::exists($file)) {
            return '0 B';
        }
        return $this->formatBytes(File::size($file));
    }
    
    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        
        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
    
    /**
     * Format uptime to human readable
     */
    protected function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        $parts = [];
        if ($days > 0) {
            $parts[] = $days . ' day' . ($days !== 1 ? 's' : '');
        }
        if ($hours > 0) {
            $parts[] = $hours . ' hour' . ($hours !== 1 ? 's' : '');
        }
        if ($minutes > 0 && $days === 0) {
            $parts[] = $minutes . ' minute' . ($minutes !== 1 ? 's' : '');
        }
        
        return empty($parts) ? '0 minutes' : implode(' ', $parts);
    }
}
