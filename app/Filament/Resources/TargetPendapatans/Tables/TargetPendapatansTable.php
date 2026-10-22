<?php

namespace App\Filament\Resources\TargetPendapatans\Tables;

use App\Models\TargetPendapatan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TargetPendapatansTable
{
    public static function configure(Table $table): Table
    {
        $isAdminSkpd = in_array(strtoupper(Auth::user()?->role ?? ''), ['SKPD', 'ADMIN_SKPD', 'ADMIN SKPD']);

        return $table
            ->columns([
                //
                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD')
                    ->hidden(fn() => $isAdminSkpd)
                    ->searchable(),

                TextColumn::make('rekening.uraian')
                    ->label('Rekening Pendapatan')
                    ->description(fn(TargetPendapatan $record): string => $record->rekening?->kode ?? '')
                    ->wrap()
                    ->searchable(['uraian', 'kode']),

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
                    ->state(fn(TargetPendapatan $record) => $record->transaksi_pendapatan->where('is_verif', true)->sum('jumlah_setor'))
                    ->description(function (TargetPendapatan $record): ?string {
                        $pending = $record->transaksi_pendapatan->where('is_verif', false)->sum('jumlah_setor');
                        return $pending > 0 ? 'Pending: Rp ' . number_format($pending, 0, ',', '.') : null;
                    })
                    ->money('IDR', locale: 'id')
                    ->alignRight()
                    ->color('success'),

                TextColumn::make('capaian')
                    ->label('Capaian')
                    ->state(function (TargetPendapatan $record) {
                        $realisasi = $record->transaksi_pendapatan->where('is_verif', true)->sum('jumlah_setor');
                        return $record->target_anggaran > 0 ? ($realisasi / $record->target_anggaran) * 100 : 0;
                    })
                    ->formatStateUsing(fn($state) => number_format($state, 2) . '%')
                    ->color(fn($state) => $state < 50 ? 'danger' : ($state < 100 ? 'warning' : 'success'))
                    ->alignCenter()
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('skpd_id')
                    ->label('Pilih SKPD')
                    ->relationship(
                        name: 'skpd',
                        titleAttribute: 'nama_skpd',
                        modifyQueryUsing: fn(Builder $query) => $query->orderBy('nama_skpd')
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua SKPD')
                    ->visible(fn() => !$isAdminSkpd),

                SelectFilter::make('tahun')
                    ->label('Tahun Realisasi')
                    ->options(function () {
                        $dbYears = TargetPendapatan::select('tahun')->distinct()->pluck('tahun')->map(fn($y) => (string) $y)->toArray();
                        $currentYear = (int) date('Y');
                        $rangeYears = array_map('strval', range($currentYear - 2, $currentYear + 4));
                        $all = array_unique(array_merge($dbYears, $rangeYears));
                        sort($all);
                        return array_combine($all, $all);
                    })
                    ->default(date('Y')),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(fn() => $isAdminSkpd ? 1 : 2)
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
