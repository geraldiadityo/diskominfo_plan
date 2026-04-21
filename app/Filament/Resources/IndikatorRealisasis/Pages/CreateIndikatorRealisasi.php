<?php

namespace App\Filament\Resources\IndikatorRealisasis\Pages;

use App\Filament\Resources\IndikatorRealisasis\IndikatorRealisasiResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateIndikatorRealisasi extends CreateRecord
{
    protected static string $resource = IndikatorRealisasiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if ($user->role === 'SKPD') {
            $data['skpd_id'] = $user->skpd_id;
        }
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
