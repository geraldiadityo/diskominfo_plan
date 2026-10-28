<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class ProgramUrusan extends Model
{
    //
    protected $fillable = [
        'kode',
        'nomenklatur'
    ];

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('program_urusans:all'));
        static::deleted(fn() => Cache::forget('program_urusans:all'));
    }

    public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('program_urusans:all', 3600, fn() => static::orderBy('kode')->get());
    }

    public function bidang(): HasMany
    {
        return $this->hasMany(ProgramBidang::class, 'urusan_id');
    }
}
