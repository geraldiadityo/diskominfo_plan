<?php

namespace App\Filament\Resources\TargetPendapatans\Tables;

use App\Models\TargetPendapatan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TargetPendapatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD')
                    ->hidden(fn() => Auth::user()->role === 'SKPD')
                    ->searchable(),

                TextColumn::make('rekening.uraian')
                    ->label('Rekening Pendapatan')
                    ->description(fn(TargetPendapatan $record): string => $record->rekening?->kode ?? '')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('target_anggaran')
                    ->label('Target (Rp)')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('realisasi')
                    ->label('Realisasi (Rp)')
                    ->state(fn(TargetPendapatan $record) => $record->transaksi_pendapatan->sum('jumlah_setor'))
                    ->money('IDR', locale: 'id')
                    ->alignRight()
                    ->color('success'),

                TextColumn::make('capaian')
                    ->label('Capaian')
                    ->state(function (TargetPendapatan $record) {
                        $realisasi = $record->transaksi_pendapatan->sum('jumlah_setor');
                        return $record->target_anggaran > 0 ? ($realisasi / $record->target_anggaran) * 100 : 0;
                    })
                    ->formatStateUsing(fn($state) => number_format($state, 2) . '%')
                    ->color(fn($state) => $state < 50 ? 'danger' : ($state < 100 ? 'warning' : 'success'))
                    ->alignCenter()
                    ->badge(),
            ])
            ->filters([
                //
                SelectFilter::make('tahun')
                    ->options([
                        '2025' => '2025',
                        '2026' => '2026',
                        '2027' => '2027',
                        '2028' => '2028',
                        '2029' => '2029',
                        '2030' => '2030'
                    ])
                    ->default(date('Y'))
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
