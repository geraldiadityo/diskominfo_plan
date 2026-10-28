<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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

    protected static function booted(): void
    {
        static::saved(function (ProgulRealisasi $model) {
            Cache::forget("progul_realisasis:{$model->indikator_id}:{$model->tahun}");
        });

        static::deleted(function (ProgulRealisasi $model) {
            Cache::forget("progul_realisasis:{$model->indikator_id}:{$model->tahun}");
        });
    }

    /**
     * Ambil realisasi progul per indikator per tahun (cached 10 menit).
     */
    public static function getByIndikatorAndTahunCached(int $indikatorId, int $tahun): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("progul_realisasis:{$indikatorId}:{$tahun}", 600, fn() =>
            static::with(['skpd', 'buktiDukung'])
                ->where('indikator_id', $indikatorId)
                ->where('tahun', $tahun)
                ->orderBy('triwulan')
                ->get()
        );
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
