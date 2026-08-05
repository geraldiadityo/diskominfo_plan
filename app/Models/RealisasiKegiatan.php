<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealisasiKegiatan extends Model
{
    //
    protected $fillable = [
        'renja_skpd_id',
        'triwulan',
        'target_keuangan',
        'target_fisik',
        'realisasi_keuangan',
        'realisasi_fisik',
        'catatan',
        'alasan',
        'solusi',
        'is_verified',
    ];

    public function renja_skpd(): BelongsTo
    {
        return $this->belongsTo(RenjaSkpd::class, 'renja_skpd_id');
    }

    public function bukti_fisik(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BuktiFisikRealisasi::class, 'realisasi_kegiatan_id');
    }
}
