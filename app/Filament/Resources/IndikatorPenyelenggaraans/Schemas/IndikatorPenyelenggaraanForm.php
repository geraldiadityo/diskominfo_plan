<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\Schemas;

use App\Models\KategoriIndikator;
use App\Models\MasterIndikator;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class IndikatorPenyelenggaraanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Indikator Penyelenggaraan')
                    ->schema([
                        Select::make('parent_id')
                            ->label('Indikator Induk / Aspek')
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'nama_indikator',
                                modifyQueryUsing: function (Builder $query, ?MasterIndikator $record) {
                                    $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');

                                    return $query
                                        ->when($kategoriId, fn ($q) => $q->where('kategori_id', $kategoriId))
                                        ->whereNull('parent_id')
                                        ->when($record, fn ($q) => $q->where('id', '!=', $record->id));
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if (! $state) {
                                    $set('satuan_id', null);
                                    $set('is_measurable', false);
                                } else {
                                    $set('is_measurable', true);
                                }
                            })
                            ->helperText('Kosongkan jika ini adalah Aspek (Parent). Pilih Aspek jika ini adalah Indikator turunan (Child).'),

                        Textarea::make('nama_indikator')
                            ->label('Nama Aspek / Indikator')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(1),

                Section::make('Pengukuran & Satuan')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('satuan_id')
                                ->label('Satuan')
                                ->relationship('satuan', 'nama_satuan')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('nama_satuan')
                                        ->label('Nama Satuan')
                                        ->required(),
                                ])
                                ->nullable()
                                ->helperText('Kosongkan untuk Aspek (Parent). Wajib diisi untuk Indikator turunan (Child).'),

                            Toggle::make('is_measurable')
                                ->label('Dapat Diukur')
                                ->default(false)
                                ->inline(false)
                                ->helperText('Aktifkan jika indikator ini memiliki target & realisasi.'),
                        ]),
                    ]),
            ]);
    }
}

