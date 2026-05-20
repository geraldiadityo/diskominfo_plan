<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TargetPendapatan extends Model
{
    //
    protected $guarded = [
        'id'
    ];

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
