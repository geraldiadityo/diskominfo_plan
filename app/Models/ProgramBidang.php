<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramBidang extends Model
{
    //
    protected $fillable = [
        'urusan_id',
        'kode',
        'kode_lengkap',
        'nomenklatur'
    ];

    protected static function booted(): void
    {
        static::creating(function (ProgramBidang $bidang) {
            if (!$bidang->kode_lengkap && $bidang->urusan_id) {
                $parent = $bidang->urusan;
                $bidang->kode_lengkap = $parent->kode . '.' . $bidang->kode;
            }
        });
    }

    public function urusan(): BelongsTo
    {
        return $this->belongsTo(ProgramUrusan::class);
    }

    public function program(): HasMany
    {
        return $this->hasMany(ProgramProgram::class);
    }
}
