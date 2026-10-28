<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class KategoriIndikator extends Model
{
    //
    protected $fillable = [
        'nama_kategori'
    ];

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('kategori_indikators:all'));
        static::deleted(fn() => Cache::forget('kategori_indikators:all'));
    }

    public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('kategori_indikators:all', 3600, fn() => static::orderBy('nama_kategori')->get());
    }

    public function indikators(): HasMany
    {
        return $this->hasMany(MasterIndikator::class, 'kategori_id');
    }
}
