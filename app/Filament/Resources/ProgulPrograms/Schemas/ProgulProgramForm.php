<?php

namespace App\Filament\Resources\ProgulPrograms\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgulProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Program Unggulan')
                    ->schema([
                        TextInput::make('nama_program')
                            ->label('Nama Program Unggulan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Sungai Penuh Maju'),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi Program')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),

                        TextInput::make('urutan')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil tampil lebih dulu'),
                    ])
                    ->columns(2),
            ]);
    }
}

