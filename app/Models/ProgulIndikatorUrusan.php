<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgulIndikatorUrusan extends Model
{
    protected $table = 'progul_indikator_urusan';

    protected $fillable = [
        'indikator_id',
        'nama_urusan',
    ];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(ProgulIndikator::class, 'indikator_id');
    }
}

