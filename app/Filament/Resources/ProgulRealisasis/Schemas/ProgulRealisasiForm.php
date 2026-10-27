<?php

namespace App\Filament\Resources\ProgulRealisasis\Schemas;

use App\Models\ProgulIndikator;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProgulRealisasiForm
{
    public static function configure(Schema $schema): Schema
    {
        $user   = Auth::user();
        $isSkpd = $user?->role === 'SKPD';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Identitas Sub Kegiatan & Triwulan')
                    ->schema([
                        Select::make('indikator_id')
                            ->label('Sub Kegiatan / Indikator Program Unggulan')
                            ->options(fn () => ProgulIndikator::with(['program', 'subKegiatan'])
                                ->get()
                                ->mapWithKeys(fn ($ind) => [
                                    $ind->id => '[' . ($ind->program?->nama_program ?? '-') . '] '
                                        . ($ind->subKegiatan?->kode_lengkap ? '(' . $ind->subKegiatan->kode_lengkap . ') ' : '')
                                        . $ind->nama_indikator,
                                ])
                            )
                            ->searchable()
                            ->required()
                            ->disabled($isSkpd)
                            ->columnSpanFull(),

                        Select::make('skpd_id')
                            ->label('SKPD Pelaksana')
                            ->relationship('skpd', 'nama_skpd')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->disabled($isSkpd)
                            ->default($isSkpd ? $user->skpd_id : null)
                            ->helperText('Kosongkan jika lintas SKPD'),

                        Select::make('tahun')
                            ->label('Tahun')
                            ->options(array_combine(range(2025, 2029), range(2025, 2029)))
                            ->required()
                            ->disabled($isSkpd),

                        Select::make('triwulan')
                            ->label('Triwulan')
                            ->options([
                                1 => 'Triwulan I',
                                2 => 'Triwulan II',
                                3 => 'Triwulan III',
                                4 => 'Triwulan IV',
                            ])
                            ->required()
                            ->disabled($isSkpd),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draf'      => 'Draf',
                                'diajukan'  => 'Diajukan',
                                'disetujui' => 'Disetujui',
                                'ditolak'   => 'Ditolak',
                            ])
                            ->default('draf')
                            ->required()
                            ->disabled($isSkpd),
                    ])
                    ->columns(2),

                Section::make('Target & Realisasi Kinerja (Fisik & Keuangan)')
                    ->schema([
                        TextInput::make('target_fisik')
                            ->label('Target Fisik (%)')
                            ->numeric()
                            ->suffix('%')
                            ->placeholder('Contoh: 25.00')
                            ->disabled($isSkpd),

                        TextInput::make('realisasi_fisik')
                            ->label('Realisasi Fisik (%)')
                            ->numeric()
                            ->suffix('%')
                            ->placeholder('Contoh: 25.00'),

                        TextInput::make('target_keuangan')
                            ->label('Target Keuangan (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->disabled($isSkpd),

                        TextInput::make('realisasi_keuangan')
                            ->label('Realisasi Keuangan (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0'),
                    ])
                    ->columns(2),

                Section::make('Catatan Evaluasi Bappeda')
                    ->visible(fn ($record) => !$isSkpd || ($record && $record->catatan_bappeda))
                    ->schema([
                        Textarea::make('catatan_bappeda')
                            ->label('Catatan / Evaluasi Bappeda')
                            ->rows(3)
                            ->disabled($isSkpd)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
