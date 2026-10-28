<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

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

            Cache::forget("realisasi_kegiatans:{$model->renja_skpd_id}");
            if ($model->renja_skpd) {
                Cache::forget("renja_skpds:{$model->renja_skpd->skpd_id}:{$model->renja_skpd->tahun}");
                Cache::forget("dashboard:chart_renja:{$model->renja_skpd->tahun}");
                Cache::forget("dashboard:skpd_renja_summary:{$model->renja_skpd->tahun}");
            }
            $tahun = date('Y');
            Cache::forget("dashboard:realisasi_keuangan:{$tahun}");
        });

        static::deleted(function (RealisasiKegiatan $model) {
            Cache::forget("realisasi_kegiatans:{$model->renja_skpd_id}");
            if ($model->renja_skpd) {
                Cache::forget("renja_skpds:{$model->renja_skpd->skpd_id}:{$model->renja_skpd->tahun}");
                Cache::forget("dashboard:chart_renja:{$model->renja_skpd->tahun}");
                Cache::forget("dashboard:skpd_renja_summary:{$model->renja_skpd->tahun}");
            }
            $tahun = date('Y');
            Cache::forget("dashboard:realisasi_keuangan:{$tahun}");
        });
    }

    /**
     * Ambil realisasi kegiatan per renja_skpd (cached 10 menit).
     */
    public static function getByRenjaCached(int $renjaSkpdId): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember("realisasi_kegiatans:{$renjaSkpdId}", 600, fn() =>
            static::with('bukti_fisik')->where('renja_skpd_id', $renjaSkpdId)->get()
        );
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
