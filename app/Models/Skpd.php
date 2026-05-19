<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skpd extends Model
{
    //
    protected $fillable = [
        'kode_skpd',
        'nama_skpd'
    ];

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
}
