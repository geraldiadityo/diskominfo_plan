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
            ->columns(2)
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
                    )
                    ->columnSpanFull(),

                TextInput::make('target_keuangan')
                    ->label('Target Keuangan (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->required()
                    ->maxValue(fn(RelationManager $livewire) => $livewire->getOwnerRecord()->pagu_anggaran)
                    ->hint(fn(RelationManager $livewire) => 'Maksimal: Rp ' . number_format($livewire->getOwnerRecord()->pagu_anggaran, 0, ',', '.')),

                TextInput::make('realisasi_keuangan')
                    ->label('Realisasi Keuangan (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->required()
                    ->maxValue(fn(RelationManager $livewire) => $livewire->getOwnerRecord()->pagu_anggaran)
                    ->hint(fn(RelationManager $livewire) => 'Maksimal: Rp ' . number_format($livewire->getOwnerRecord()->pagu_anggaran, 0, ',', '.')),

                TextInput::make('target_fisik')
                    ->label('Target Fisik (%)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),

                TextInput::make('realisasi_fisik')
                    ->label('Realisasi Fisik (%)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),

                Textarea::make('catatan')
                    ->label('Catatan / Keterangan (Optional)')
                    ->columnSpanFull(),

                Textarea::make('alasan')
                    ->label('Alasan')
                    ->disabled(fn() => !in_array(auth()->user()->role, ['SKPD', 'ADMIN']))
                    ->columnSpanFull(),

                Textarea::make('solusi')
                    ->label('Solusi')
                    ->disabled(fn() => !in_array(auth()->user()->role, ['VERIFIKATOR', 'ADMIN']))
                    ->columnSpanFull(),
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
                    ->formatStateUsing(fn(string $state): string => 'Triwulan ' . ['I', 'II', 'III', 'IV'][$state - 1])
                    ->sortable(),

                TextColumn::make('target_keuangan')
                    ->label('Target Keuangan')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('realisasi_keuangan')
                    ->label('Serapan Keuangan')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('target_fisik')
                    ->label('Target Fisik')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('realisasi_fisik')
                    ->label('Realisasi Fisik')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('catatan')
                    ->wrap()
                    ->limit(50),

                \Filament\Tables\Columns\IconColumn::make('is_verified')
                    ->label('Status Verif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Input Realisasi Baru'),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('verifikasi')
                    ->label('Verif')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (\App\Models\RealisasiKegiatan $record) => in_array(auth()->user()->role, ['VERIFIKATOR', 'ADMIN']) && !$record->is_verified)
                    ->action(function (\App\Models\RealisasiKegiatan $record) {
                        $record->update(['is_verified' => true]);
                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil diverifikasi')
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\Action::make('kelola_bukti_fisik')
                    ->label('Kelola Detail & Bukti')
                    ->icon('heroicon-o-list-bullet')
                    ->color('info')
                    ->url(fn (\App\Models\RealisasiKegiatan $record): string => route('filament.admin.resources.realisasi-kegiatans.edit', $record)),
                EditAction::make(),
                DeleteAction::make()
            ]);
    }
}
