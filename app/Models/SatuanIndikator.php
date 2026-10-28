<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class SatuanIndikator extends Model
{
    //
    protected $fillable = [
        'nama_satuan'
    ];

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('satuan_indikators:all'));
        static::deleted(fn() => Cache::forget('satuan_indikators:all'));
    }

    public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('satuan_indikators:all', 3600, fn() => static::orderBy('nama_satuan')->get());
    }

    public function indikators(): HasMany
    {
        return $this->hasMany(MasterIndikator::class, 'indikator_id');
    }
}
