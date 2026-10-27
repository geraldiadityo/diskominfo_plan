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

                TextColumn::make('indikator.subKegiatan.kode_lengkap')
                    ->label('Kode Rekening')
                    ->fontFamily('mono')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('indikator.nama_indikator')
                    ->label('Sub Kegiatan / Indikator')
                    ->wrap()
                    ->limit(80)
                    ->searchable(),

                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD Pelaksana')
                    ->placeholder('- Lintas SKPD -')
                    ->limit(30)
                    ->wrap(),

                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('triwulan')
                    ->label('TW')
                    ->formatStateUsing(fn ($state) => 'TW ' . $state)
                    ->badge()
                    ->color('primary')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('target_fisik')
                    ->label('Target Fisik')
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '%' : '-')
                    ->alignCenter(),

                TextColumn::make('realisasi_fisik')
                    ->label('Real. Fisik')
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '%' : '-')
                    ->alignCenter(),

                TextColumn::make('target_keuangan')
                    ->label('Target Keu.')
                    ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('realisasi_keuangan')
                    ->label('Real. Keu.')
                    ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                SelectFilter::make('triwulan')
                    ->label('Triwulan')
                    ->options([
                        1 => 'Triwulan I',
                        2 => 'Triwulan II',
                        3 => 'Triwulan III',
                        4 => 'Triwulan IV',
                    ]),

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
