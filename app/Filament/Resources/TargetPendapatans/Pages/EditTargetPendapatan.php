<?php

namespace App\Filament\Resources\TargetPendapatans\Pages;

use App\Filament\Resources\TargetPendapatans\TargetPendapatanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditTargetPendapatan extends EditRecord
{
    protected static string $resource = TargetPendapatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    #[Override]
    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
}
