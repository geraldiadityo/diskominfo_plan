<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    // TTL Constants (dalam detik)
    public const TTL_STATIC  = 3600;   // 60 menit — data master statis
    public const TTL_DYNAMIC = 600;    // 10 menit — data semi-dinamis

    /**
     * Helper: get or cache data.
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Hapus satu atau banyak cache key sekaligus.
     */
    public static function forget(string|array $keys): void
    {
        foreach ((array) $keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Hapus semua cache dengan tag tertentu (jika didukung driver cache).
     */
    public static function flushTag(string|array $tags): void
    {
        Cache::tags($tags)->flush();
    }
}

