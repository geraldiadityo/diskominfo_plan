<?php

namespace App\Filament\Resources\ProgramBidangs\Pages;

use App\Filament\Resources\ProgramBidangs\ProgramBidangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProgramBidangs extends ListRecords
{
    protected static string $resource = ProgramBidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
