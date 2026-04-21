<?php

namespace App\Filament\Resources\SatuanIndikators\Pages;

use App\Filament\Resources\SatuanIndikators\SatuanIndikatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSatuanIndikators extends ListRecords
{
    protected static string $resource = SatuanIndikatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
