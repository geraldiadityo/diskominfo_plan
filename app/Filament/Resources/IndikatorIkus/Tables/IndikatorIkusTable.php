<?php

namespace App\Filament\Resources\IndikatorIkus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IndikatorIkusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_indikator')
                    ->label('Nama Indikator Kinerja Utama')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD Penanggung Jawab')
                    ->wrap()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('satuan.nama_satuan')
                    ->label('Satuan')
                    ->badge()
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('kondisi_awal')
                    ->label('Baseline')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_measurable')
                    ->label('Dapat Diukur')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('skpd_id')
                    ->label('SKPD Penanggung Jawab')
                    ->relationship('skpd', 'nama_skpd')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('satuan_id')
                    ->label('Satuan')
                    ->relationship('satuan', 'nama_satuan')
                    ->searchable()
                    ->preload(),
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

