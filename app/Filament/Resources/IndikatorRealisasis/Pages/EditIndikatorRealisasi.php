<?php

namespace App\Filament\Resources\IndikatorRealisasis\Pages;

use App\Filament\Resources\IndikatorRealisasis\IndikatorRealisasiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIndikatorRealisasi extends EditRecord
{
    protected static string $resource = IndikatorRealisasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
