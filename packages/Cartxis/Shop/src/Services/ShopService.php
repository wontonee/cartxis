<?php

namespace Cartxis\Shop\Services;

abstract class ShopService
{
    /**
     * Handle service exceptions.
     *
     * @param \Exception $e
     * @param string $message
     * @return void
     * @throws \Exception
     */
    protected function handleException(\Exception $e, $message = 'An error occurred')
    {
        \Log::error($message . ': ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);

        throw $e;
    }

    /**
     * Format response data.
     *
     * @param mixed $data
     * @param string $message
     * @param bool $success
     * @return array
     */
    protected function formatResponse($data = null, $message = '', $success = true)
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Cache key prefix.
     *
     * @return string
     */
    protected function cachePrefix()
    {
        return 'shop';
    }

    /**
     * Get cache key.
     *
     * @param string $key
     * @return string
     */
    protected function getCacheKey($key)
    {
        return $this->cachePrefix() . '.' . $key;
    }

    /**
     * Remember cache.
     *
     * @param string $key
     * @param int $ttl
     * @param \Closure $callback
     * @return mixed
     */
    protected function remember($key, $ttl, \Closure $callback)
    {
        return cache()->remember(
            $this->getCacheKey($key),
            $ttl,
            $callback
        );
    }

    /**
     * Forget cache.
     *
     * @param string $key
     * @return bool
     */
    protected function forget($key)
    {
        return cache()->forget($this->getCacheKey($key));
    }
}
