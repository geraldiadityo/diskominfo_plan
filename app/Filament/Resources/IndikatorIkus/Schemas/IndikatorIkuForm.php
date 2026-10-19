<?php

namespace App\Filament\Resources\IndikatorIkus\Schemas;

use App\Models\KategoriIndikator;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IndikatorIkuForm
{
    public static function configure(Schema $schema): Schema
    {
        $kategoriIkuId = KategoriIndikator::where('nama_kategori', 'IKU')->value('id');

        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Indikator Kinerja Utama')
                    ->schema([
                        Hidden::make('kategori_id')
                            ->default($kategoriIkuId),

                        Textarea::make('nama_indikator')
                            ->label('Nama Indikator Kinerja Utama (IKU)')
                            ->required()
                            ->columnSpanFull()
                            ->rows(3),

                        Select::make('skpd_id')
                            ->label('SKPD Penanggung Jawab')
                            ->relationship('skpd', 'nama_skpd')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('satuan_id')
                            ->label('Satuan Indikator')
                            ->relationship('satuan', 'nama_satuan')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Section::make('Pengukuran & Kondisi Awal')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('kondisi_awal')
                                ->label('Kondisi Awal / Baseline')
                                ->helperText('Nilai realisasi pada kondisi awal atau baseline.'),

                            Toggle::make('is_measurable')
                                ->label('Dapat Diukur')
                                ->default(true)
                                ->inline(false)
                                ->helperText('Aktifkan jika indikator ini memiliki target dan realisasi.'),
                        ]),
                    ]),
            ]);
    }
}

