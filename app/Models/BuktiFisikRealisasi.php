<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiFisikRealisasi extends Model
{
    protected $fillable = [
        'realisasi_kegiatan_id',
        'nama_indikator',
        'target',
        'realisasi',
        'file_bukti',
        'status_verifikasi',
        'catatan_verifikasi',
    ];

    public function realisasi_kegiatan()
    {
        return $this->belongsTo(RealisasiKegiatan::class, 'realisasi_kegiatan_id');
    }
}