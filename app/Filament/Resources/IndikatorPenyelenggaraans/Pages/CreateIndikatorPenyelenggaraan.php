<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\Pages;

use App\Filament\Resources\IndikatorPenyelenggaraans\IndikatorPenyelenggaraanResource;
use App\Models\KategoriIndikator;
use Filament\Resources\Pages\CreateRecord;

class CreateIndikatorPenyelenggaraan extends CreateRecord
{
    protected static string $resource = IndikatorPenyelenggaraanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $kategoriId = KategoriIndikator::firstOrCreate(['nama_kategori' => 'Penyelenggaraan'])->id;
        $data['kategori_id'] = $kategoriId;

        // Aturan: Parent tidak memiliki satuan dan tidak diukur langsung
        if (empty($data['parent_id'])) {
            $data['satuan_id'] = null;
            $data['is_measurable'] = false;
        } else {
            $data['is_measurable'] = true;
        }

        return $data;
    }
}

