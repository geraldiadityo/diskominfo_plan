<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\Tables;

use App\Models\KategoriIndikator;
use App\Models\MasterIndikator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IndikatorPenyelenggaraansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_indikator')
                    ->label('Nama Aspek / Indikator')
                    ->wrap()
                    ->searchable()
                    ->description(fn (MasterIndikator $record): string =>
                        $record->parent_id === null
                            ? 'Aspek (Parent)'
                            : 'Turunan dari: ' . ($record->parent?->nama_indikator ?? '-')
                    ),

                TextColumn::make('child_indikators_count')
                    ->label('Jumlah Anak')
                    ->counts('childIndikators')
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->visible(fn ($livewire) => method_exists($livewire, 'getActiveTab') && $livewire->getActiveTab() !== 'turunan'),

                TextColumn::make('satuan.nama_satuan')
                    ->label('Satuan')
                    ->badge()
                    ->placeholder('-')
                    ->searchable(),

                IconColumn::make('is_measurable')
                    ->label('Dapat Diukur')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('tipe')
                    ->label('Tipe Indikator')
                    ->options([
                        'parent' => 'Aspek (Parent)',
                        'child' => 'Indikator Turunan (Child)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (($data['value'] ?? null) === 'parent') {
                            return $query->whereNull('parent_id');
                        }
                        if (($data['value'] ?? null) === 'child') {
                            return $query->whereNotNull('parent_id');
                        }

                        return $query;
                    }),

                SelectFilter::make('parent_id')
                    ->label('Filter Aspek Induk')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'nama_indikator',
                        modifyQueryUsing: function (Builder $query) {
                            $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');

                            return $query
                                ->when($kategoriId, fn ($q) => $q->where('kategori_id', $kategoriId))
                                ->whereNull('parent_id');
                        }
                    )
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

