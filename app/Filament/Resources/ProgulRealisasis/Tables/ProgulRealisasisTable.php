<?php

namespace App\Filament\Resources\ProgulRealisasis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProgulRealisasisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('indikator.program.nama_program')
                    ->label('Program Unggulan')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('indikator.nama_indikator')
                    ->label('Indikator Kinerja')
                    ->wrap()
                    ->limit(100)
                    ->searchable(),

                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD Pelaksana')
                    ->placeholder('- Lintas SKPD -')
                    ->limit(35)
                    ->wrap(),

                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->badge()
                    ->color('info')
                    ->sortable(),

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
                    ->color(fn (?string $state): string => match ($state) {
                        'draf'      => 'gray',
                        'diajukan'  => 'warning',
                        'disetujui' => 'success',
                        'ditolak'   => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'draf'      => 'Draf',
                        'diajukan'  => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                        default     => $state ?? '-',
                    }),

                TextColumn::make('buktiDukung_count')
                    ->label('Bukti')
                    ->counts('buktiDukung')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('program')
                    ->label('Program Unggulan')
                    ->relationship('indikator.program', 'nama_program')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(array_combine(range(2025, 2029), range(2025, 2029))),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draf'      => 'Draf',
                        'diajukan'  => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                    ]),

                SelectFilter::make('skpd_id')
                    ->label('SKPD Pelaksana')
                    ->relationship('skpd', 'nama_skpd')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('tahun', 'asc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->role === 'ADMIN'),
                ]),
            ]);
    }
}

