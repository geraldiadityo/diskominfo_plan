<?php

namespace App\Filament\Resources\MasterIndikators\Pages;

use App\Filament\Resources\MasterIndikators\MasterIndikatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterIndikators extends ListRecords
{
    protected static string $resource = MasterIndikatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
