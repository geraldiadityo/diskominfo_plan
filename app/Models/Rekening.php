<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rekening extends Model
{
    //
    protected $guarded = [
        'id'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Rekening::class, 'parent_id');
    }

    public function target_pendapatan(): HasMany
    {
        return $this->hasMany(TargetPendapatan::class, 'rekening_id');
    }
}
