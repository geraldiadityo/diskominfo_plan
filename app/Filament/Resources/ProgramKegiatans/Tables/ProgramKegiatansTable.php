<?php

namespace App\Filament\Resources\ProgramKegiatans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProgramKegiatansTable
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
                    ->label('Nomenklatur Kegiatan')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('program.nomenklatur')
                    ->label('program')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
                SelectFilter::make('program')
                    ->relationship('program', 'nomenklatur')
                    ->searchable()
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
