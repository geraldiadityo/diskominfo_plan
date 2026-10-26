<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * Realisasi fisik renja = rata-rata realisasi_fisik dari semua triwulan yang sudah diverifikasi.
     */
    protected function realisasiFisik(): Attribute
    {
        return Attribute::make(
            get: function () {
                $triwulans = $this->realisasi_kegiatans ? $this->realisasi_kegiatans->filter(fn($r) => (bool) $r->is_verified) : collect();
                $count = $triwulans->count();
                if ($count === 0) {
                    return 0.0;
                }
                return round($triwulans->sum('realisasi_fisik') / $count, 2);
            }
        );
    }

    /**
     * Realisasi keuangan renja = total akumulasi dari semua triwulan yang sudah diverifikasi.
     */
    protected function realisasiKeuangan(): Attribute
    {
        return Attribute::make(
            get: function () {
                $triwulans = $this->realisasi_kegiatans ? $this->realisasi_kegiatans->filter(fn($r) => (bool) $r->is_verified) : collect();
                return (float) $triwulans->sum('realisasi_keuangan');
            }
        );
    }

    /**
     * Persentase capaian fisik terhadap target renja.
     */
    protected function persentaseFisik(): Attribute
    {
        return Attribute::make(
            get: function () {
                $target = (float) ($this->target_fisik ?? 0);
                return $target > 0 ? round(($this->realisasi_fisik / $target) * 100, 2) : 0.0;
            }
        );
    }

    /**
     * Persentase capaian keuangan terhadap target renja.
     */
    protected function persentaseKeuangan(): Attribute
    {
        return Attribute::make(
            get: function () {
                $target = (float) ($this->target_keuangan ?? 0);
                return $target > 0 ? round(($this->realisasi_keuangan / $target) * 100, 2) : 0.0;
            }
        );
    }
}

