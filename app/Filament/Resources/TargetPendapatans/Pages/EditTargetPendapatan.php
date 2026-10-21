<?php

namespace App\Filament\Resources\TargetPendapatans\Pages;

use App\Filament\Resources\TargetPendapatans\TargetPendapatanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Override;

class EditTargetPendapatan extends EditRecord
{
    protected static string $resource = TargetPendapatanResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array(strtoupper(Auth::user()?->role ?? ''), ['SKPD', 'ADMIN_SKPD', 'ADMIN SKPD'])) {
            $data['skpd_id'] = Auth::user()->skpd_id;
        }

        return $data;
    }

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
