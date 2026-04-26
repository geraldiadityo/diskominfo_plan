<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramKegiatan extends Model
{
    //
    protected $fillable = [
        'program_id',
        'kode',
        'kode_lengkap',
        'nomenklatur',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProgramKegiatan $kegiatan) {
            if (!$kegiatan->kode_lengkap && $kegiatan->program_id) {
                $parent = $kegiatan->program;
                $kegiatan->kode_lengkap = $parent->kode_lengkap . '.' . $kegiatan->kode;
            }
        });
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(ProgramProgram::class, 'program_id');
    }

    public function sub_kegiatan(): HasMany
    {
        return $this->hasMany(ProgramSubKegiatan::class, 'kegiatan_id');
    }
}
