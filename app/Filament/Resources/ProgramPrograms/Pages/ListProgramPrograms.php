<?php

namespace App\Filament\Resources\ProgramPrograms\Pages;

use App\Filament\Resources\ProgramPrograms\ProgramProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProgramPrograms extends ListRecords
{
    protected static string $resource = ProgramProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
