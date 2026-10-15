<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\Pages;

use App\Filament\Resources\IndikatorPenyelenggaraans\IndikatorPenyelenggaraanResource;
use App\Models\KategoriIndikator;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIndikatorPenyelenggaraan extends EditRecord
{
    protected static string $resource = IndikatorPenyelenggaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

