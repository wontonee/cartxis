<?php

namespace Vortex\Reports\Services;

use Illuminate\Support\Facades\Cache;

class ReportCacheService
{
    /**
     * Get cached report data or execute callback.
     */
    public function remember(string $reportType, array $filters, callable $callback): mixed
    {
        if (!config('reports.cache.enabled')) {
            return $callback();
        }

        $cacheKey = $this->getCacheKey($reportType, $filters);
        $ttl = config("reports.cache.ttl.{$reportType}", 900);

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Clear cache for a specific report type.
     */
    public function forget(string $reportType, array $filters = []): bool
    {
        $cacheKey = $this->getCacheKey($reportType, $filters);
        return Cache::forget($cacheKey);
    }

    /**
     * Clear all report caches.
     */
    public function flush(string $reportType = null): void
    {
        $prefix = config('reports.cache.prefix');
        
        if ($reportType) {
            Cache::tags(["{$prefix}{$reportType}"])->flush();
        } else {
            Cache::tags(["{$prefix}sales", "{$prefix}products", "{$prefix}customers"])->flush();
        }
    }

    /**
     * Generate cache key from report type and filters.
     */
    protected function getCacheKey(string $reportType, array $filters): string
    {
        $prefix = config('reports.cache.prefix');
        $hash = md5(json_encode($filters));
        
        return "{$prefix}{$reportType}_{$hash}";
    }
}
