<?php

namespace App\Filament\Resources\IndikatorIkus\Pages;

use App\Filament\Resources\IndikatorIkus\IndikatorIkuResource;
use App\Models\KategoriIndikator;
use Filament\Resources\Pages\CreateRecord;

class CreateIndikatorIku extends CreateRecord
{
    protected static string $resource = IndikatorIkuResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $kategoriId = KategoriIndikator::where('nama_kategori', 'IKU')->value('id');
        $data['kategori_id'] = $kategoriId;
        $data['parent_id']   = null;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

