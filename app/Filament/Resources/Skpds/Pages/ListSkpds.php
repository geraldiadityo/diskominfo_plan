<?php

namespace App\Filament\Resources\Skpds\Pages;

use App\Filament\Resources\Skpds\SkpdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSkpds extends ListRecords
{
    protected static string $resource = SkpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
