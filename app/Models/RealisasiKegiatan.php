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

    protected static function booted(): void
    {
        static::saved(function (RealisasiKegiatan $model) {
            if ($model->bukti_fisik()->count() > 0 && ($model->wasChanged('target_fisik') || $model->wasRecentlyCreated)) {
                $model->recalculateRealisasiFisik();
            }
        });
    }

    /**
     * Hitung dan sinkronkan realisasi fisik berdasarkan status persetujuan bukti fisik.
     */
    public function recalculateRealisasiFisik(): void
    {
        $totalIndikator = $this->bukti_fisik()->count();
        $targetFisik = (float) ($this->target_fisik ?? 0);

        // Bukti fisik bersifat opsional: jika belum/tidak ada indikator bukti fisik,
        // jangan reset nilai realisasi fisik yang sudah diinput.
        if ($totalIndikator === 0) {
            return;
        }

        if ($targetFisik <= 0) {
            $this->updateQuietly(['realisasi_fisik' => 0]);
            return;
        }

        $hasBobot = \Illuminate\Support\Facades\Schema::hasColumn('bukti_fisik_realisasis', 'bobot');

        if ($hasBobot && $this->bukti_fisik()->whereNotNull('bobot')->exists()) {
            $totalBobot = (float) $this->bukti_fisik()->sum('bobot');
            $bobotDisetujui = (float) $this->bukti_fisik()
                ->where('status_verifikasi', 'disetujui')
                ->sum('bobot');

            $persentaseCapaian = $totalBobot > 0 ? ($bobotDisetujui / $totalBobot) : 0;
        } else {
            $indikatorDisetujui = $this->bukti_fisik()
                ->where('status_verifikasi', 'disetujui')
                ->count();

            $persentaseCapaian = $indikatorDisetujui / $totalIndikator;
        }

        $realisasiFisik = round($persentaseCapaian * $targetFisik, 2);
        $realisasiFisik = min(100.00, max(0.00, $realisasiFisik));

        $this->updateQuietly(['realisasi_fisik' => $realisasiFisik]);
    }
}
