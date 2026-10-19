<?php

namespace App\Filament\Resources\IndikatorIkus\Pages;

use App\Filament\Resources\IndikatorIkus\IndikatorIkuResource;
use App\Models\KategoriIndikator;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIndikatorIku extends EditRecord
{
    protected static string $resource = IndikatorIkuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

