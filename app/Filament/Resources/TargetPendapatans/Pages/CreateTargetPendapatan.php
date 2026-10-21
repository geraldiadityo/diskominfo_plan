<?php

namespace App\Filament\Resources\TargetPendapatans\Pages;

use App\Filament\Resources\TargetPendapatans\TargetPendapatanResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Override;

class CreateTargetPendapatan extends CreateRecord
{
    protected static string $resource = TargetPendapatanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (in_array(strtoupper(Auth::user()?->role ?? ''), ['SKPD', 'ADMIN_SKPD', 'ADMIN SKPD'])) {
            $data['skpd_id'] = Auth::user()->skpd_id;
        }

        return $data;
    }

    #[Override]
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
