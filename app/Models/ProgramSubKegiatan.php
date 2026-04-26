<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(ProgramKegiatan::class, 'kegiatan_id');
    }
}
