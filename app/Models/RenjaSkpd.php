<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RenjaSkpd extends Model
{
    //
    protected $fillable = [
        'skpd_id',
        'sub_kegiatan_id',
        'tahun',
        'pagu_anggaran',
        'target_keuangan',
        'target_fisik',
        'target_kinerja'
    ];

    public function skpd(): BelongsTo
    {
        return $this->belongsTo(Skpd::class, 'skpd_id');
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(ProgramSubKegiatan::class, 'sub_kegiatan_id');
    }

    public function realisasi_kegiatans(): HasMany
    {
        return $this->hasMany(RealisasiKegiatan::class, 'renja_skpd_id');
    }
}
