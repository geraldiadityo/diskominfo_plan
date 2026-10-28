<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class ProgramKegiatan extends Model
{
    //
    protected $fillable = [
        'program_id',
        'kode',
        'kode_lengkap',
        'nomenklatur',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProgramKegiatan $kegiatan) {
            if (!$kegiatan->kode_lengkap && $kegiatan->program_id) {
                $parent = $kegiatan->program;
                $kegiatan->kode_lengkap = $parent->kode_lengkap . '.' . $kegiatan->kode;
            }
        });

        static::saved(fn() => Cache::forget('program_kegiatans:all'));
        static::deleted(fn() => Cache::forget('program_kegiatans:all'));
    }

    public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('program_kegiatans:all', 3600, fn() => static::with('program.bidang')->orderBy('kode_lengkap')->get());
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(ProgramProgram::class, 'program_id');
    }

    public function sub_kegiatan(): HasMany
    {
        return $this->hasMany(ProgramSubKegiatan::class, 'kegiatan_id');
    }
}
