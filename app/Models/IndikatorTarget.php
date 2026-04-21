<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndikatorTarget extends Model
{
    //
    protected $fillable = [
        'indikator_id',
        'tahun',
        'nilai_target'
    ];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(MasterIndikator::class, 'indikator_id');
    }
}
