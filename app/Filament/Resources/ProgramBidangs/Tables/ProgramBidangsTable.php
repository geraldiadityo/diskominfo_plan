<?php

namespace App\Filament\Resources\ProgramBidangs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProgramBidangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('kode_lengkap')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nomenklatur')
                    ->label('Nomenklatur Bidang')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('urusan.nomenklatur')
                    ->label('Urusan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
                SelectFilter::make('urusan')
                    ->relationship('urusan', 'nomenklatur')
                    ->searchable(),
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
