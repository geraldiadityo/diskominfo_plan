<?php

namespace App\Filament\Resources\SatuanIndikators\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SatuanIndikatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                TextInput::make('nama_satuan')
                    ->required()
                    ->unique(ignoreRecord: true),
            ]);
    }
}
