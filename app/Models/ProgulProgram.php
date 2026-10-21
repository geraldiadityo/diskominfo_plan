<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgulProgram extends Model
{
    protected $table = 'progul_programs';

    protected $fillable = [
        'nama_program',
        'deskripsi',
        'urutan',
    ];

    public function indikators(): HasMany
    {
        return $this->hasMany(ProgulIndikator::class, 'program_id')->orderBy('urutan');
    }

    public function getIconAttribute(): string
    {
        $namaLower = strtolower($this->nama_program);

        if (str_contains($namaLower, 'maju')) {
            return 'shield-check';
        }
        if (str_contains($namaLower, 'sejahtera')) {
            return 'heart';
        }
        if (str_contains($namaLower, 'nyaman')) {
            return 'sparkles';
        }
        if (str_contains($namaLower, 'unggul')) {
            return 'academic-cap';
        }
        if (str_contains($namaLower, 'sehat')) {
            return 'plus-circle';
        }
        if (str_contains($namaLower, 'agamais')) {
            return 'star';
        }
        if (str_contains($namaLower, 'perempuan')) {
            return 'user-group';
        }
        if (str_contains($namaLower, 'ilok')) {
            return 'light-bulb';
        }
        if (str_contains($namaLower, 'melayani')) {
            return 'face-smile';
        }

        return 'bolt';
    }
}

