<?php

namespace App\Filament\Resources\ProgramSubKegiatans\Pages;

use App\Filament\Resources\ProgramSubKegiatans\ProgramSubKegiatanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProgramSubKegiatan extends EditRecord
{
    protected static string $resource = ProgramSubKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
