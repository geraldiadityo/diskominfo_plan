<?php

namespace App\Filament\Resources\Rekenings\Tables;

use App\Models\Rekening;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RekeningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('kode')
                    ->label('Kode Rekening')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                TextColumn::make('uraian')
                    ->label('Uraian Akun / Rekening')
                    ->searchable()
                    ->wrap()
                    ->html()
                    ->formatStateUsing(function ($state, Rekening $record) {
                        $padding = ($record->level - 1) * 15;
                        $marker = $record->level > 1 ? '<span class="text-gray-400 mr-1">└─</span>' : '';
                        $weightClass = $record->level <= 2 ? 'font-bold text-slate-900' : 'text-slate-600';

                        return "<div class='{$weightClass}' style='padding-left: {$padding}px;'>{$marker}{$state}</div>";
                    }),

                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '1' => 'danger',
                        '2' => 'warning',
                        '3' => 'info',
                        '4' => 'success',
                        '5' => 'primary',
                        default => 'gray'
                    })
                    ->sortable()
                    ->alignCenter(),
            ])
            ->defaultSort('kode', 'asc')
            ->filters([
                //
                SelectFilter::make('level')
                    ->label('Saringan Berdasarkan Level')
                    ->options([
                        1 => 'Level 1 (Akun)',
                        2 => 'Level 2 (Kelompok)',
                        3 => 'Level 3 (Jenis)',
                        4 => 'Level 4 (Object)',
                        5 => 'level 5 (Rincian Object)',
                        6 => 'Level 6 (Sub Rincian Object)',
                    ]),
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
