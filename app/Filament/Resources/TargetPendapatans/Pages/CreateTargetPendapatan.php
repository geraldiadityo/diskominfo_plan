<?php

namespace App\Filament\Resources\TargetPendapatans\Pages;

use App\Filament\Resources\TargetPendapatans\TargetPendapatanResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateTargetPendapatan extends CreateRecord
{
    protected static string $resource = TargetPendapatanResource::class;

    #[Override]
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
