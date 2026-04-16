<?php

namespace App\Filament\Resources\Skpds\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SkpdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                TextInput::make('kode_skpd')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('nama_skpd')
                    ->required()
                    ->maxLength(255)
            ]);
    }
}
