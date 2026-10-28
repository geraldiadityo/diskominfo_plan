<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class ProgramBidang extends Model
{
    //
    protected $fillable = [
        'urusan_id',
        'kode',
        'kode_lengkap',
        'nomenklatur'
    ];

    protected static function booted(): void
    {
        static::creating(function (ProgramBidang $bidang) {
            if (!$bidang->kode_lengkap && $bidang->urusan_id) {
                $parent = $bidang->urusan;
                $bidang->kode_lengkap = $parent->kode . '.' . $bidang->kode;
            }
        });

        static::saved(function () {
            Cache::forget('program_bidangs:all');
            Cache::forget('skpds:with_bidang');
        });

        static::deleted(function () {
            Cache::forget('program_bidangs:all');
            Cache::forget('skpds:with_bidang');
        });
    }

    public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('program_bidangs:all', 3600, fn() => static::with('urusan')->orderBy('kode_lengkap')->get());
    }

    public function urusan(): BelongsTo
    {
        return $this->belongsTo(ProgramUrusan::class, 'urusan_id');
    }

    public function program(): HasMany
    {
        return $this->hasMany(ProgramProgram::class, 'bidang_id');
    }
}
