<?php

namespace App\Filament\Resources\RenjaSkpds\Pages;

use App\Filament\Resources\RenjaSkpds\RenjaSkpdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRenjaSkpds extends ListRecords
{
    protected static string $resource = RenjaSkpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
