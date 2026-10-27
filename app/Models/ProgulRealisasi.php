<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgulRealisasi extends Model
{
    protected $table = 'progul_realisasis';

    protected $fillable = [
        'indikator_id',
        'skpd_id',
        'tahun',
        'triwulan',
        'target_fisik',
        'target_keuangan',
        'realisasi_fisik',
        'realisasi_keuangan',
        'status',
        'catatan_bappeda',
        'submitted_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'triwulan'           => 'integer',
            'target_fisik'       => 'decimal:2',
            'target_keuangan'    => 'decimal:2',
            'realisasi_fisik'    => 'decimal:2',
            'realisasi_keuangan' => 'decimal:2',
            'submitted_at'       => 'datetime',
            'verified_at'        => 'datetime',
        ];
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(ProgulIndikator::class, 'indikator_id');
    }

    public function skpd(): BelongsTo
    {
        return $this->belongsTo(Skpd::class, 'skpd_id');
    }

    public function buktiDukung(): HasMany
    {
        return $this->hasMany(ProgulBuktiDukung::class, 'realisasi_id');
    }
}
