<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramProgram extends Model
{
    //
    protected $fillable = [
        'bidang_id',
        'kode',
        'kode_lengkap',
        'nomenklatur'
    ];

    protected static function booted(): void
    {
        static::creating(function (ProgramProgram $program) {
            if (!$program->kode_lengkap && $program->bidang_id) {
                $parent = $program->bidang;
                $program->kode_lengkap = $parent->kode_lengkap . '.' . $program->kode;
            }
        });
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(ProgramBidang::class, 'bidang_id');
    }

    public function kegiatan(): HasMany
    {
        return $this->hasMany(ProgramKegiatan::class, 'program_id');
    }
}
