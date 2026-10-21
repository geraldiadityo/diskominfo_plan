<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgulBuktiDukung extends Model
{
    protected $table = 'progul_bukti_dukung';

    protected $fillable = [
        'realisasi_id',
        'keterangan',
        'file_bukti',
        'status_verifikasi',
        'catatan_verifikasi',
    ];

    public function realisasi(): BelongsTo
    {
        return $this->belongsTo(ProgulRealisasi::class, 'realisasi_id');
    }
}

