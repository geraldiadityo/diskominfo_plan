<?php

namespace App\Filament\Resources\KategoriIndikators\Pages;

use App\Filament\Resources\KategoriIndikators\KategoriIndikatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKategoriIndikators extends ListRecords
{
    protected static string $resource = KategoriIndikatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
