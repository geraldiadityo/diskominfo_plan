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
        'target_tahunan',
        'nilai_realisasi',
        'status',
        'catatan_bappeda',
        'submitted_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'verified_at'  => 'datetime',
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

