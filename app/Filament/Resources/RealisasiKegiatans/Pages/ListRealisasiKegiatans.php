<?php

namespace App\Filament\Resources\RealisasiKegiatans\Pages;

use App\Filament\Resources\RealisasiKegiatans\RealisasiKegiatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiKegiatans extends ListRecords
{
    protected static string $resource = RealisasiKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
