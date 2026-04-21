<?php

namespace App\Models;

use App\Enums\StatusRealisasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterIndikator extends Model
{
    //
    protected $fillable = [
        'parent_id',
        'skpd_id',
        'kategori_id',
        'nama_indikator',
        'satuan_id',
        'kondisi_awal',
        'is_measurable'
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriIndikator::class, 'kategori_id');
    }

    public function skpd(): BelongsTo
    {
        return $this->belongsTo(Skpd::class, 'skpd_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(SatuanIndikator::class, 'satuan_id');
    }

    public function children(): BelongsTo
    {
        return $this->belongsTo(MasterIndikator::class, 'parent_id');
    }

    public function target(): HasMany
    {
        return $this->hasMany(IndikatorTarget::class, 'indikator_id');
    }

    public function realisasi(): HasMany
    {
        return $this->hasMany(IndikatorRealisasi::class, 'indikator_id');
    }

    protected static function booted()
    {
        static::updated(function (MasterIndikator $indikator) {
            if ($indikator->wasChanged('skpd_id')) {
                if (is_null($indikator->skpd_id)) {
                    $indikator->realisasi()
                        ->where('status', StatusRealisasi::DRAF)
                        ->delete();
                } else {
                    $indikator->realisasi()
                        ->where('status', StatusRealisasi::DRAF)
                        ->update([
                            'skpd_id' => $indikator->skpd_id,
                        ]);
                }
            }
        });
    }
}
