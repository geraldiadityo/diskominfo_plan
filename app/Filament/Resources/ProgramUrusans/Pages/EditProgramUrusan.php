<?php

namespace App\Filament\Resources\ProgramUrusans\Pages;

use App\Filament\Resources\ProgramUrusans\ProgramUrusanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProgramUrusan extends EditRecord
{
    protected static string $resource = ProgramUrusanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
