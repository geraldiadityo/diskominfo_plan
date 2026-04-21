<?php

namespace App\Filament\Resources\MasterIndikators\Pages;

use App\Filament\Resources\MasterIndikators\MasterIndikatorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterIndikator extends CreateRecord
{
    protected static string $resource = MasterIndikatorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
