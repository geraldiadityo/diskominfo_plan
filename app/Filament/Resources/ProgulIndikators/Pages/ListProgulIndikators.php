<?php

namespace App\Filament\Resources\ProgulIndikators\Pages;

use App\Filament\Resources\ProgulIndikators\ProgulIndikatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListProgulIndikators extends ListRecords
{
    protected static string $resource = ProgulIndikatorResource::class;

    protected function getHeaderActions(): array
    {
        return Auth::user()?->role === 'ADMIN'
            ? [CreateAction::make()->label('Tambah Indikator')]
            : [];
    }
}

