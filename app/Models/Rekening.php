<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Rekening extends Model
{
    //
    protected $guarded = [
        'id'
    ];

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('rekenings:all'));
        static::deleted(fn() => Cache::forget('rekenings:all'));
    }

    public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('rekenings:all', 3600, fn() => static::orderBy('kode')->get());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Rekening::class, 'parent_id');
    }

    public function target_pendapatan(): HasMany
    {
        return $this->hasMany(TargetPendapatan::class, 'rekening_id');
    }
}
