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
}
