<?php

namespace App\Filament\Resources\ProgulIndikators\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProgulIndikatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('urusan'))
            ->columns([
                TextColumn::make('program.nama_program')
                    ->label('Program Unggulan')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_indikator')
                    ->label('Indikator Kinerja')
                    ->searchable()
                    ->wrap()
                    ->limit(150),

                TextColumn::make('urusan')
                    ->label('Urusan')
                    ->getStateUsing(fn ($record) => $record->urusan->pluck('nama_urusan')->implode(' | '))
                    ->wrap()
                    ->limit(80)
                    ->color('gray'),

                TextColumn::make('realisasis_count')
                    ->label('Realisasi Tersedia')
                    ->counts('realisasis')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('program_id')
                    ->label('Program Unggulan')
                    ->relationship('program', 'nama_program')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('program_id', 'asc')
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

