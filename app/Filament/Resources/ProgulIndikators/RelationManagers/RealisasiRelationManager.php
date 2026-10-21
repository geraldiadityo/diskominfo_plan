<?php

namespace App\Filament\Resources\ProgulIndikators\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RealisasiRelationManager extends RelationManager
{
    protected static string $relationship = 'realisasis';
    protected static ?string $title = 'Target & Realisasi Tahunan (5 Tahun)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('skpd_id')
                    ->label('SKPD Pelaksana')
                    ->relationship('skpd', 'nama_skpd')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Kosongkan jika lintas SKPD / belum ditentukan'),

                Select::make('tahun')
                    ->label('Tahun')
                    ->options(array_combine(range(2025, 2029), range(2025, 2029)))
                    ->required(),

                TextInput::make('target_tahunan')
                    ->label('Target Tahunan')
                    ->placeholder('Contoh: 100%, 5 unit, 1.000 orang')
                    ->nullable(),

                TextInput::make('nilai_realisasi')
                    ->label('Realisasi')
                    ->placeholder('Isi setelah periode berjalan')
                    ->nullable(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draf'      => 'Draf',
                        'diajukan'  => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                    ])
                    ->default('draf')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tahun')
            ->columns([
                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD Pelaksana')
                    ->placeholder('- Lintas SKPD -')
                    ->wrap()
                    ->limit(40),

                TextColumn::make('target_tahunan')
                    ->label('Target')
                    ->placeholder('-')
                    ->alignCenter(),

                TextColumn::make('nilai_realisasi')
                    ->label('Realisasi')
                    ->placeholder('Belum diisi')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draf'      => 'gray',
                        'diajukan'  => 'warning',
                        'disetujui' => 'success',
                        'ditolak'   => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draf'      => 'Draf',
                        'diajukan'  => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                        default     => $state,
                    }),
            ])
            ->defaultSort('tahun', 'asc')
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Data Realisasi')
                    ->visible(fn () => in_array(Auth::user()?->role, ['ADMIN', 'BAPPEDA', 'SKPD'])),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn () => Auth::user()?->role === 'ADMIN'),
            ]);
    }
}

