<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AppSetting extends Model
{
    use HasFactory;

    protected $table = 'app_settings';

    protected $fillable = [
        'app_name',
        'app_logo',
    ];

    /**
     * Boot model events untuk pembersihan file fisik lama & invalidasi cache.
     */
    protected static function booted(): void
    {
        // Saat data sedang di-update
        static::updating(function (AppSetting $setting) {
            if ($setting->isDirty('app_logo')) {
                $oldLogo = $setting->getOriginal('app_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
            }
        });

        // Saat data dihapus
        static::deleting(function (AppSetting $setting) {
            if ($setting->app_logo && Storage::disk('public')->exists($setting->app_logo)) {
                Storage::disk('public')->delete($setting->app_logo);
            }
        });

        // Bersihkan cache setiap kali data disimpan atau dihapus
        static::saved(function () {
            Cache::forget('app_setting_data');
        });

        static::deleted(function () {
            Cache::forget('app_setting_data');
        });
    }

    /**
     * Mengambil record pengaturan (dengan cache 24 jam).
     * Aman dari exception jika tabel belum selesai di-migrate.
     */
    public static function getSetting(): ?self
    {
        try {
            return Cache::remember('app_setting_data', 86400, function () {
                return static::query()->first();
            });
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Ambil nama aplikasi (fallback ke config app.name).
     */
    public static function getAppName(): string
    {
        return static::getSetting()?->app_name ?? config('app.name', 'Dashboard Kota Sungai Penuh');
    }

    /**
     * Ambil URL publik logo aplikasi jika ada dan file fisiknya eksis.
     */
    public static function getAppLogoUrl(): ?string
    {
        $setting = static::getSetting();
        if ($setting && $setting->app_logo && Storage::disk('public')->exists($setting->app_logo)) {
            return asset('storage/' . $setting->app_logo);
        }

        return null;
    }
}

