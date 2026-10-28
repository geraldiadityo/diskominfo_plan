<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class ProgramSubKegiatan extends Model
{
    //
    protected $fillable = [
        'kegiatan_id',
        'kode',
        'kode_lengkap',
        'nomenklatur',
        'kinerja',
        'indikator',
        'satuan',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProgramSubKegiatan $subKegiatan) {
            if (!$subKegiatan->kode_lengkap && $subKegiatan->kegiatan_id) {
                $parent = $subKegiatan->kegiatan;
                $subKegiatan->kode_lengkap = $parent->kode_lengkap . '.' . $subKegiatan->kode;
            }
        });

        static::saved(fn() => Cache::forget('program_sub_kegiatans:all'));
        static::deleted(fn() => Cache::forget('program_sub_kegiatans:all'));
    }

    public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('program_sub_kegiatans:all', 3600, fn() => static::with('kegiatan.program.bidang')->orderBy('kode_lengkap')->get());
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(ProgramKegiatan::class, 'kegiatan_id');
    }
}
