<?php

namespace App\Filament\Resources\Rekenings\Pages;

use App\Filament\Resources\Rekenings\RekeningResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateRekening extends CreateRecord
{
    protected static string $resource = RekeningResource::class;

    #[Override]
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
