<?php

namespace App\Filament\Resources\ProgramUrusans\Pages;

use App\Filament\Resources\ProgramUrusans\ProgramUrusanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProgramUrusans extends ListRecords
{
    protected static string $resource = ProgramUrusanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
