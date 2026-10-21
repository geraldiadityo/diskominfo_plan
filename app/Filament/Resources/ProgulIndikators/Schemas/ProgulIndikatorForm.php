<?php

namespace App\Filament\Resources\ProgulIndikators\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgulIndikatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identitas Indikator')
                    ->schema([
                        Select::make('program_id')
                            ->label('Program Unggulan')
                            ->relationship('program', 'nama_program')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('urutan')
                            ->label('No. Urut')
                            ->numeric()
                            ->default(0),

                        Textarea::make('nama_indikator')
                            ->label('Nama Indikator Kinerja')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('detail')
                            ->label('Detail / Deskripsi')
                            ->rows(5)
                            ->nullable()
                            ->columnSpanFull()
                            ->helperText('Deskripsikan kegiatan dan sasaran indikator ini secara detail.'),
                    ])
                    ->columns(2),

                Section::make('Urusan yang Terkait')
                    ->description('Daftar urusan pemerintahan yang terkait dengan indikator ini')
                    ->schema([
                        Repeater::make('urusan')
                            ->relationship('urusan')
                            ->label('')
                            ->schema([
                                TextInput::make('nama_urusan')
                                    ->label('Nama Urusan')
                                    ->required()
                                    ->placeholder('Contoh: Urusan Pendidikan'),
                            ])
                            ->addActionLabel('Tambah Urusan')
                            ->reorderable()
                            ->collapsible()
                            ->columns(1),
                    ]),
            ]);
    }
}

