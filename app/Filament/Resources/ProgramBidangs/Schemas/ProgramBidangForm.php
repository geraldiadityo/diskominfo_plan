<?php

namespace App\Filament\Resources\ProgramBidangs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramBidangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                //
                Section::make('Informasi Bidang Urusan')
                    ->schema([
                        Select::make('urusan_id')
                            ->relationship('urusan', 'nomenklatur')
                            ->label('Urusan Induk')
                            ->searchable()
                            ->preload(),

                        TextInput::make('kode')
                            ->label('Kode Bidan Urusan')
                            ->required()
                            ->maxLength(10),

                        Textarea::make('nomenklatur')
                            ->label('Nomenklatur Bidang')
                            ->required()
                            ->columnSpanFull()
                    ])->columns(2)
            ]);
    }
}
