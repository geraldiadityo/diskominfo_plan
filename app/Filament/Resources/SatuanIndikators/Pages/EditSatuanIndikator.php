<?php

namespace App\Filament\Resources\SatuanIndikators\Pages;

use App\Filament\Resources\SatuanIndikators\SatuanIndikatorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSatuanIndikator extends EditRecord
{
    protected static string $resource = SatuanIndikatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
