<?php

namespace App\Filament\Resources\KategoriIndikators\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KategoriIndikatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                TextInput::make('nama_kategori')
                    ->required()
                    ->unique(ignoreRecord: true)
            ]);
    }
}
