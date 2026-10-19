<?php

namespace App\Filament\Resources\IndikatorIkus\Pages;

use App\Filament\Resources\IndikatorIkus\IndikatorIkuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIndikatorIkus extends ListRecords
{
    protected static string $resource = IndikatorIkuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

