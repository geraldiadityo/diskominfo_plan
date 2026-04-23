<?php

namespace App\Filament\Resources\ProgramUrusans\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramUrusanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                //
                Section::make('Detail Urusan')
                    ->description('Input Data Urusan Sesuai Kepmendagri')
                    ->schema([
                        TextInput::make('kode')
                            ->label('Kode Urusan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(5)
                            ->placeholder('Contoh: 1'),

                        Textarea::make('nomenklatur')
                            ->label('Nama Urusan')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull()
                    ])->columns(1),
            ]);
    }
}
