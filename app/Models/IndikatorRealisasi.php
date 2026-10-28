<?php

namespace App\Models;

use App\Enums\StatusRealisasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class IndikatorRealisasi extends Model
{
    //
    protected $fillable = [
        'indikator_id',
        'skpd_id',
        'tahun',
        'nilai_realisasi',
        'is_tercapai',
        'bukti_dokument',
        'status',
        'is_verif',
        'catatan_bappeda',
        'submitted_at',
        'verified_at'
    ];

    protected function casts(): array
    {
        return [
            'is_tercapai' => 'boolean',
            'is_verif' => 'boolean',
            'status' => StatusRealisasi::class,
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime'
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (IndikatorRealisasi $model) {
            Cache::forget('master_indikators:iku');
            Cache::forget('master_indikators:penyelenggaraan');
        });

        static::deleted(function (IndikatorRealisasi $model) {
            Cache::forget('master_indikators:iku');
            Cache::forget('master_indikators:penyelenggaraan');
        });
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(MasterIndikator::class, 'indikator_id');
    }

    public function skpd(): BelongsTo
    {
        return $this->belongsTo(Skpd::class, 'skpd_id');
    }

    public function getNilaiTargetAttribute(): ?string
    {
        return IndikatorTarget::where('indikator_id', $this->indikator_id)
            ->where('tahun', $this->tahun)
            ->value('nilai_target');
    }
}
