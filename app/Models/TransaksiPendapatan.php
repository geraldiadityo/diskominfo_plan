<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiPendapatan extends Model
{
    //
    protected $guarded = [
        'id'
    ];

    public function target_pendapatan(): BelongsTo
    {
        return $this->belongsTo(TargetPendapatan::class, 'target_pendapatan_id');
    }
}
