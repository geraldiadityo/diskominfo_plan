<?php

namespace App\Filament\Resources\ProgramPrograms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProgramProgramsTable
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
                    ->label('Nomenklatur Program')
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereFullText('nomenklatur', $search);
                    }),

                TextColumn::make('bidang.nomenklatur')
                    ->label('urusan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
                SelectFilter::make('bidang')
                    ->relationship('bidang', 'nomenklatur')
                    ->searchable()
                    ->preload()
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
