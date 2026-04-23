<?php

namespace App\Filament\Resources\ProgramBidangs\Pages;

use App\Filament\Resources\ProgramBidangs\ProgramBidangResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProgramBidang extends EditRecord
{
    protected static string $resource = ProgramBidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
