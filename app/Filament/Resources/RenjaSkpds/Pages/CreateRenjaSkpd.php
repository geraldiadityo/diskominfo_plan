<?php

namespace App\Filament\Resources\RenjaSkpds\Pages;

use App\Filament\Resources\RenjaSkpds\RenjaSkpdResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateRenjaSkpd extends CreateRecord
{
    protected static string $resource = RenjaSkpdResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
