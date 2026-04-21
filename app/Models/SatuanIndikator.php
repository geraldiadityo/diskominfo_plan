<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SatuanIndikator extends Model
{
    //
    protected $fillable = [
        'nama_satuan'
    ];

    public function indikators(): HasMany
    {
        return $this->hasMany(MasterIndikator::class, 'indikator_id');
    }
}
