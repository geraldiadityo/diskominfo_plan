<?php

namespace App\Filament\Resources\MasterIndikators\RelationManager;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TargetRelationManager extends RelationManager
{
    protected static string $relationship = 'target';
    protected static ?string $title = 'Data Target Tahunan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tahun')
                    ->label('Tahun')
                    ->required()
                    ->numeric()
                    ->disabled()
                    ->columnSpan(1),

                TextInput::make('nilai_target')
                    ->label('Nilai Target')
                    ->required()
                    ->suffix(fn($livewire) => $livewire->ownerRecord->satuan?->nama_satuan ?? '')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Tahun')
            ->columns([
                TextColumn::make('tahun')
                    ->label('tahun')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('nilai_target')
                    ->label('Nilai Target')
                    ->alignRight()
                    ->suffix(fn($livewire) => ' ' . ($livewire->ownerRecord->satuan?->nama_satuan ?? '')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('md'),
                DeleteAction::make()
            ])->defaultSort('tahun', 'asc');
    }
}
