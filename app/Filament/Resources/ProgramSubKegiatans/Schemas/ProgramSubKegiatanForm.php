<?php

namespace App\Filament\Resources\ProgramSubKegiatans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramSubKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                //
                Section::make('Sub Kegiatan')
                    ->schema([
                        Select::make('kegiatan_id')
                            ->relationship('kegiatan', 'nomenklatur')
                            ->label('Kegiatan')
                            ->searchable()
                            ->required(),
                    ])->columns(1),

                Section::make('Detail Sub Kegiatan')
                    ->schema([
                        TextInput::make('kode')
                            ->required()
                            ->maxLength(10),

                        TextInput::make('kode_lengkap')
                            ->disabled()
                            ->dehydrated(false),

                        Textarea::make('nomenklatur')
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('kinerja')
                            ->columnSpanFull(),

                        TextInput::make('indikator')
                            ->label('Indikator Kinerja'),

                        TextInput::make('satuan')
                            ->label('Satuan Indikator'),
                    ])->columns(2),
            ]);
    }
}
