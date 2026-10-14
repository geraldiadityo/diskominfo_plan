<?php

namespace App\Filament\Resources\Skpds\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SkpdsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('kode_skpd')
                    ->label('Kode SKPD')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nama_skpd')
                    ->label('Nama SKPD')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('bidang.kode_lengkap')
                    ->label('Kode Bidang (Mapping)')
                    ->placeholder('Belum di-mapping')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('bidang.nomenklatur')
                    ->label('Nama Bidang')
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
