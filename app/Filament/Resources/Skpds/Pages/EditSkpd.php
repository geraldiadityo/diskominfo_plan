<?php

namespace App\Filament\Resources\Skpds\Pages;

use App\Filament\Resources\Skpds\SkpdResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSkpd extends EditRecord
{
    protected static string $resource = SkpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
