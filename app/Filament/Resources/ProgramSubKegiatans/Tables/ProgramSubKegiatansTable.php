<?php

namespace App\Filament\Resources\ProgramSubKegiatans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProgramSubKegiatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('kode_lengkap')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nomenklatur')
                    ->label('Nama Sub Kegiatan')
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereFullText('nomenklatur', $search);
                        }
                    )
                    ->wrap(),

                TextColumn::make('satuan')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
            ])
            ->filters([
                //
                SelectFilter::make('kegiatan')
                    ->relationship('kegiata', 'nomenklatur')
                    ->label('filter kegiatan')
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
