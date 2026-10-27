<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgulIndikator extends Model
{
    protected $table = 'progul_indikators';

    protected $fillable = [
        'program_id',
        'sub_kegiatan_id',
        'rincian',
        'nama_indikator',
        'detail',
        'urutan',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(ProgulProgram::class, 'program_id');
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(ProgramSubKegiatan::class, 'sub_kegiatan_id');
    }

    public function urusan(): HasMany
    {
        return $this->hasMany(ProgulIndikatorUrusan::class, 'indikator_id');
    }

    public function realisasis(): HasMany
    {
        return $this->hasMany(ProgulRealisasi::class, 'indikator_id')->orderBy('tahun')->orderBy('triwulan');
    }
}
