<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skpd extends Model
{
    //
    protected $fillable = [
        'kode_skpd',
        'nama_skpd',
        'bidang_id',
    ];

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(ProgramBidang::class, 'bidang_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'skpd_id');
    }

    public function indikator(): HasMany
    {
        return $this->hasMany(MasterIndikator::class, 'skpd_id');
    }

    public function realisasi(): HasMany
    {
        return $this->hasMany(IndikatorRealisasi::class, 'skpd_id');
    }

    public function target_pendapatan(): HasMany
    {
        return $this->hasMany(TargetPendapatan::class, 'skpd_id');
    }

    public function renja(): HasMany
    {
        return $this->hasMany(RenjaSkpd::class, 'skpd_id');
    }

    /**
     * Dapatkan kode bidang yang di-mapping ke SKPD ini.
     * Jika belum di-mapping, kembalikan kode urusan asli sebagai fallback.
     */
    public function getKodeBidangDisplay(?string $kodeUrusan = null): string
    {
        if ($this->bidang) {
            return $this->bidang->kode_lengkap;
        }

        return $kodeUrusan ?? '';
    }

    /**
     * Format / ganti kode atau teks yang mengandung "X.XX" dengan kode bidang SKPD.
     */
    public function formatKodeWithBidang(?string $code): string
    {
        if (!$code) {
            return '';
        }

        if ($this->bidang && !empty($this->bidang->kode_lengkap)) {
            return str_replace(['X.XX', 'X.Xx', 'x.xx'], $this->bidang->kode_lengkap, $code);
        }

        return $code;
    }

    /**
     * Helper statis untuk memformat kode (menggantikan X.XX dengan kode bidang SKPD).
     */
    public static function formatDisplayKode(?string $kode, ?int $skpdId = null): string
    {
        if (!$kode) {
            return '';
        }

        if (!str_contains(strtoupper($kode), 'X.XX')) {
            return $kode;
        }

        $skpdId = $skpdId ?: (\Illuminate\Support\Facades\Auth::user()?->role === 'SKPD' ? \Illuminate\Support\Facades\Auth::user()->skpd_id : null);
        if (!$skpdId) {
            return $kode;
        }

        static $skpdCache = [];
        if (!isset($skpdCache[$skpdId])) {
            $skpdCache[$skpdId] = static::with('bidang')->find($skpdId);
        }

        $skpd = $skpdCache[$skpdId];
        if ($skpd?->bidang?->kode_lengkap) {
            return str_ireplace('X.XX', $skpd->bidang->kode_lengkap, $kode);
        }

        return $kode;
    }

    /**
     * Helper statis untuk memformat nama/nomenklatur urusan atau bidang
     * (menggantikan X.XX atau BIDANG XX dengan kode/nama bidang mapping SKPD).
     */
    public static function formatDisplayNomenklatur(?string $nomenklatur, ?int $skpdId = null): string
    {
        if (!$nomenklatur) {
            return '';
        }

        $upper = strtoupper($nomenklatur);
        if (!str_contains($upper, 'X.XX') && !str_contains($upper, 'BIDANG XX')) {
            return $nomenklatur;
        }

        $skpdId = $skpdId ?: (\Illuminate\Support\Facades\Auth::user()?->role === 'SKPD' ? \Illuminate\Support\Facades\Auth::user()->skpd_id : null);
        if (!$skpdId) {
            return $nomenklatur;
        }

        static $skpdCache = [];
        if (!isset($skpdCache[$skpdId])) {
            $skpdCache[$skpdId] = static::with('bidang')->find($skpdId);
        }

        $skpd = $skpdCache[$skpdId];
        if ($skpd?->bidang) {
            $result = str_ireplace('X.XX', $skpd->bidang->kode_lengkap, $nomenklatur);
            $result = str_ireplace('BIDANG XX', 'BIDANG ' . $skpd->bidang->kode_lengkap . ' (' . $skpd->bidang->nomenklatur . ')', $result);
            return $result;
        }

        return $nomenklatur;
    }
}
