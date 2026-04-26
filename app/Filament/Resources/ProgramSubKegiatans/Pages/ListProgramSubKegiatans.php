<?php

namespace App\Filament\Resources\ProgramSubKegiatans\Pages;

use App\Filament\Resources\ProgramSubKegiatans\ProgramSubKegiatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProgramSubKegiatans extends ListRecords
{
    protected static string $resource = ProgramSubKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
