<?php

namespace App\Filament\Resources\ProgramKegiatans\Pages;

use App\Filament\Resources\ProgramKegiatans\ProgramKegiatanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProgramKegiatan extends EditRecord
{
    protected static string $resource = ProgramKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
