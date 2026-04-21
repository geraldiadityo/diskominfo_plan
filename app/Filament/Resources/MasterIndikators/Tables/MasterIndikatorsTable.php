<?php

namespace App\Filament\Resources\MasterIndikators\Tables;

use App\Models\MasterIndikator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MasterIndikatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('kategori.nama_kategori')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama_indikator')
                    ->label('Indikator / Program')
                    ->wrap()
                    ->searchable()
                    ->description(fn(MasterIndikator $record): string => $record->children ? 'Induk: ' . $record->children->nama_indikator : 'Root Level'),

                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD')
                    ->wrap()
                    ->sortable()
                    ->placeholder('- Lintas SKPD / Grup -'),

                TextColumn::make('satuan.nama_satuan')
                    ->label('satuan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_measurable')
                    ->label('Measurable')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                //
                SelectFilter::make('kategori_id')
                    ->label('Kategori Indikatori')
                    ->relationship('kategori', 'nama_kategori')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('skpd_id')
                    ->label('SKPD Penanggung Jawab')
                    ->relationship('skpd', 'nama_skpd')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('is_measurable')
                    ->label('Status Pengukuran')
                    ->options([
                        1 => 'Dapat Di ukur (true)',
                        0 => 'Hanya Group / Header'
                    ])
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
