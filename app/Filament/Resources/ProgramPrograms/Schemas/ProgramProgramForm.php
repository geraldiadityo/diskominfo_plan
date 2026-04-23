<?php

namespace App\Filament\Resources\ProgramPrograms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                //
                Section::make('Detail Program')
                    ->schema([
                        Select::make('bidang_id')
                            ->relationship('bidang', 'nomenklatur')
                            ->label('Nomenklatur Bidang')
                            ->searchable()
                            ->required()
                            ->preload(),

                        TextInput::make('kode')
                            ->required(),

                        Textarea::make('nomenklatur')
                            ->required()
                            ->columnSpanFull()
                    ])->columns(2)
            ]);
    }
}
