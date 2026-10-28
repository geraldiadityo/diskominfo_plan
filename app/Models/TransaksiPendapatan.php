<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class TransaksiPendapatan extends Model
{
    //
    protected $guarded = [
        'id'
    ];

    protected function casts(): array
    {
        return [
            'is_verif' => 'boolean',
            'tanggal_setor' => 'date',
            'jumlah_setor' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (TransaksiPendapatan $transaksi) {
            $tahun = $transaksi->target_pendapatan?->tahun ?? date('Y');
            Cache::forget("dashboard:chart_pendapatan:{$tahun}");
            Cache::forget("dashboard:skpd_pendapatan_summary:{$tahun}");
            Cache::forget("dashboard:skpd_pad_summary:{$tahun}");
            Cache::forget("dashboard:pendapatan_tree:{$tahun}");
        });

        static::deleted(function (TransaksiPendapatan $transaksi) {
            $tahun = $transaksi->target_pendapatan?->tahun ?? date('Y');
            Cache::forget("dashboard:chart_pendapatan:{$tahun}");
            Cache::forget("dashboard:skpd_pendapatan_summary:{$tahun}");
            Cache::forget("dashboard:skpd_pad_summary:{$tahun}");
            Cache::forget("dashboard:pendapatan_tree:{$tahun}");
        });
    }

    public function target_pendapatan(): BelongsTo
    {
        return $this->belongsTo(TargetPendapatan::class, 'target_pendapatan_id');
    }
}
