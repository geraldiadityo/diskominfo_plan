<?php

namespace App\Filament\Resources\ProgramKegiatans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                //
                Section::make('Detail Kegiatan')
                    ->schema([
                        Select::make('program_id')
                            ->relationship('program', 'nomenklatur')
                            ->searchable()
                            ->required(),

                        TextInput::make('kode')
                            ->required(),

                        Textarea::make('nomenklatur')
                            ->required()
                            ->columnSpanFull()
                    ])->columns(2)
            ]);
    }
}
