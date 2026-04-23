<?php

namespace App\Filament\Resources\ProgramUrusans\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgramUrusansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('kode')
                    ->label('Kode')
                    ->sortable()
                    ->searchable()
                    ->badge(),

                TextColumn::make('nomenklatur')
                    ->label('Nomenklatur Urusan')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('bidang_count')
                    ->label('Jumlah Bidang')
                    ->counts('bidang')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // Action::make('buka_bidang')
                //     ->label('Lihat Program')
                //     ->icon(Heroicon::ArrowRightCircle)
                //     ->color('primary')
                //     ->url(fn($record): string => route('filament.admin.resource.program-bidangs.edit', $record)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
