<?php

namespace App\Filament\Resources\TargetPendapatans\Pages;

use App\Filament\Resources\TargetPendapatans\TargetPendapatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTargetPendapatans extends ListRecords
{
    protected static string $resource = TargetPendapatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
