<?php

namespace App\Filament\Resources\ProgramPrograms\Pages;

use App\Filament\Resources\ProgramPrograms\ProgramProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProgramProgram extends EditRecord
{
    protected static string $resource = ProgramProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
