<?php

namespace App\Filament\Resources\ProgulRealisasis\Pages;

use App\Filament\Resources\ProgulRealisasis\ProgulRealisasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListProgulRealisasis extends ListRecords
{
    protected static string $resource = ProgulRealisasiResource::class;

    protected function getHeaderActions(): array
    {
        return in_array(Auth::user()?->role, ['ADMIN', 'SKPD'])
            ? [CreateAction::make()->label('Tambah Realisasi')]
            : [];
    }
}

