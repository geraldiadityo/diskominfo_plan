<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class TargetPendapatan extends Model
{
    //
    protected $guarded = [
        'id'
    ];

    protected static function booted(): void
    {
        static::saved(function (TargetPendapatan $target) {
            $tahun = $target->tahun ?? date('Y');
            Cache::forget("dashboard:target_pendapatan_total:{$tahun}");
            Cache::forget("dashboard:chart_pendapatan:{$tahun}");
            Cache::forget("dashboard:skpd_pendapatan_summary:{$tahun}");
            Cache::forget("dashboard:skpd_pad_summary:{$tahun}");
            Cache::forget("dashboard:pendapatan_tree:{$tahun}");
            Cache::forget("target_pendapatans:{$target->skpd_id}:{$tahun}");
        });

        static::deleted(function (TargetPendapatan $target) {
            $tahun = $target->tahun ?? date('Y');
            Cache::forget("dashboard:target_pendapatan_total:{$tahun}");
            Cache::forget("dashboard:chart_pendapatan:{$tahun}");
            Cache::forget("dashboard:skpd_pendapatan_summary:{$tahun}");
            Cache::forget("dashboard:skpd_pad_summary:{$tahun}");
            Cache::forget("dashboard:pendapatan_tree:{$tahun}");
            Cache::forget("target_pendapatans:{$target->skpd_id}:{$tahun}");
        });
    }

    public function skpd(): BelongsTo
    {
        return $this->belongsTo(Skpd::class, 'skpd_id');
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'rekening_id');
    }

    public function transaksi_pendapatan(): HasMany
    {
        return $this->hasMany(TransaksiPendapatan::class, 'target_pendapatan_id');
    }
}
