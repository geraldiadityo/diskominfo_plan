<?php

namespace App\Filament\Resources\RealisasiKegiatans\Pages;

use App\Filament\Resources\RealisasiKegiatans\RealisasiKegiatanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRealisasiKegiatan extends EditRecord
{
    protected static string $resource = RealisasiKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
