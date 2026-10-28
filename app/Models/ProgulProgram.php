<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class ProgulProgram extends Model
{
    protected $table = 'progul_programs';

    protected $fillable = [
        'nama_program',
        'deskripsi',
        'urutan',
    ];

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('progul_programs:with_indikators'));
        static::deleted(fn() => Cache::forget('progul_programs:with_indikators'));
    }

    public static function getAllWithIndikatorsCached(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('progul_programs:with_indikators', 3600, fn() =>
            static::with(['indikators.urusan', 'indikators.subKegiatan'])->orderBy('urutan')->get()
        );
    }

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
