<?php

namespace App\Filament\Resources\ProgulRealisasis\Pages;

use App\Filament\Resources\ProgulRealisasis\ProgulRealisasiResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProgulRealisasi extends CreateRecord
{
    protected static string $resource = ProgulRealisasiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        if ($user?->role === 'SKPD') {
            $data['skpd_id'] = $user->skpd_id;
        }
        return $data;
    }
}

