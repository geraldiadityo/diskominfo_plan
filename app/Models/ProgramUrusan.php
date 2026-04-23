<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramUrusan extends Model
{
    //
    protected $fillable = [
        'kode',
        'nomenklatur'
    ];

    public function bidang(): HasMany
    {
        return $this->hasMany(ProgramBidang::class, 'urusan_id');
    }
}
