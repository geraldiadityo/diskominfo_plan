<?php

namespace App\Filament\Resources\KategoriIndikators\Pages;

use App\Filament\Resources\KategoriIndikators\KategoriIndikatorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKategoriIndikator extends EditRecord
{
    protected static string $resource = KategoriIndikatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
