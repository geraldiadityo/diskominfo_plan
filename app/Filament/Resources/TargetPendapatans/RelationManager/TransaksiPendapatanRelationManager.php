<?php

namespace App\Filament\Resources\TargetPendapatans\RelationManager;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

class TransaksiPendapatanRelationManager extends RelationManager
{
    protected static string $relationship = 'transaksi_pendapatan';
    protected static ?string $modelLabel = 'Setoran';
    protected static ?string $pluralModelLabel = 'Riwayat Setoran';

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                DatePicker::make('tanggal_setor')
                    ->label('Tanggal Setor')
                    ->default(now())
                    ->required()
                    ->columnSpan(1),

                TextInput::make('nomor_bukti')
                    ->label('Nomor Bukti / STS')
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('jumlah_setor')
                    ->label('Jumlah Setor (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('keterangan')
                    ->label('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tanggal_setor')
            ->columns([
                TextColumn::make('tanggal_setor')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('nomor_bukti')
                    ->label('No. Bukti / STS')
                    ->searchable(),

                TextColumn::make('jumlah_setor')
                    ->label('Jumlah (Rp)')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('keterangan')
                    ->wrap()
                    ->limit(50),
            ])
            ->defaultSort('tanggal_setor', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Setoran Baru')
                    ->icon(Heroicon::PlusCircle),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ]);
    }
}
