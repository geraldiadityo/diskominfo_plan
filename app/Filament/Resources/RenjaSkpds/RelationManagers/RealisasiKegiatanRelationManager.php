<?php

namespace App\Filament\Resources\RenjaSkpds\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use Override;

class RealisasiKegiatanRelationManager extends RelationManager
{
    protected static string $relationship = 'realisasi_kegiatans';
    protected static ?string $title = 'Realisasi Triwulan';

    protected static ?string $modelLabel = 'Realisasi';
    protected static ?string $pluralModelLabel = 'Realisasi Triwulan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Select::make('triwulan')
                    ->label('Pilih Triwulan')
                    ->options([
                        1 => 'Triwulan I',
                        2 => 'Triwulan II',
                        3 => 'Triwulan III',
                        4 => 'Triwulan IV',
                    ])
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn(Unique $rule, RelationManager $livewire) => $rule->where('renja_skpd_id', $livewire->getOwnerRecord()->id)
                    ),

                TextInput::make('realisasi_keuangan')
                    ->label('Realisasi Keuangan (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->required()
                    ->maxValue(fn(RelationManager $livewire) => $livewire->getOwnerRecord()->pagu_anggaran)
                    ->hint(fn(RelationManager $livewire) => 'Maksimal: Rp ' . number_format($livewire->getOwnerRecord()->pagu_anggaran, 0, ',', '.')),

                TextInput::make('realisasi_fisik')
                    ->label('Realisasi Fisik (%)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),

                Textarea::make('catatan')
                    ->label('Catatan / Keterangan (Optinal)')
                    ->columnSpanFull()
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('triwulan')
            ->columns([
                TextColumn::make('triwulan')
                    ->label('Triwulan')
                    ->formatStateUsing(fn(string $state): string => 'Triwulan ' . ['I', 'III', 'III', 'IV'][$state - 1])
                    ->sortable(),

                TextColumn::make('realisasi_keuangan')
                    ->label('Serapan Anggaran')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('realisasi_fisik')
                    ->label('Fisik')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('catatan')
                    ->wrap()
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Input Realisasi Baru'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ]);
    }
}
