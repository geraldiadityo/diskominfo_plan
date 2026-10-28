<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class IndikatorTarget extends Model
{
    //
    protected $fillable = [
        'indikator_id',
        'tahun',
        'nilai_target'
    ];

    protected static function booted(): void
    {
        static::saved(function (IndikatorTarget $model) {
            Cache::forget('master_indikators:iku');
            Cache::forget('master_indikators:penyelenggaraan');
        });

        static::deleted(function (IndikatorTarget $model) {
            Cache::forget('master_indikators:iku');
            Cache::forget('master_indikators:penyelenggaraan');
        });
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(MasterIndikator::class, 'indikator_id');
    }
}
