<?php

namespace App\Filament\Resources\ProgulPrograms\Pages;

use App\Filament\Resources\ProgulPrograms\ProgulProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListProgulPrograms extends ListRecords
{
    protected static string $resource = ProgulProgramResource::class;

    protected function getHeaderActions(): array
    {
        return Auth::user()?->role === 'ADMIN'
            ? [CreateAction::make()->label('Tambah Program')]
            : [];
    }
}

