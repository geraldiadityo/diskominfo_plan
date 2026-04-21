<?php

namespace App\Filament\Resources\MasterIndikators\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterIndikatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                //
                Section::make('Informasi Utama')
                    ->schema([
                        Select::make('parent_id')
                            ->label('Indikator Induk / Header')
                            ->relationship('children', 'nama_indikator')
                            ->searchable()
                            ->preload()
                            ->helperText('Kosongkan Jika Ini Adalah Header Paling Atas.'),

                        Select::make('skpd_id')
                            ->label('SKPD Penanggung Jawab')
                            ->relationship('skpd', 'nama_skpd')
                            ->searchable()
                            ->preload()
                            ->helperText('Bisa Dikosongkan untuk level grup/urusan'),

                        Select::make('kategori_id')
                            ->label('Kategori Indikator')
                            ->relationship('kategori', 'nama_kategori')
                            ->searchable()
                            ->preload()
                            ->helperText('bisa dikosongkan untuk grouping'),

                        Textarea::make('nama_indikator')
                            ->label('Nama Urusan / Program / Indikator')
                            ->required()
                            ->columnSpanFull()
                            ->rows(3),
                    ])->columns(2),

                Section::make('Target & Pengukuran')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('satuan_id')
                                ->label('Satuan')
                                ->relationship('satuan', 'nama_satuan')
                                ->searchable()
                                ->preload()
                                ->helperText('Bisa dikosongkan untuk group'),

                            TextInput::make('kondisi_awal')
                                ->label('Kondisi Awal'),

                            Toggle::make('is_measurable')
                                ->label('Dapat diukur')
                                ->default(true)
                                ->inline(false)
                                ->helperText('Aktifkan Jika indikator ini memiliki target & realisasi')
                        ])
                    ])
            ]);
    }
}
