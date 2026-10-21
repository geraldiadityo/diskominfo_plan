<?php

namespace App\Filament\Resources\ProgulPrograms\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class IndikatorRelationManager extends RelationManager
{
    protected static string $relationship = 'indikators';
    protected static ?string $title = 'Indikator Kinerja Program';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('urutan')
                    ->label('No. Urut')
                    ->numeric()
                    ->default(0)
                    ->columnSpan(1),

                Textarea::make('nama_indikator')
                    ->label('Nama Indikator Kinerja')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('detail')
                    ->label('Detail / Deskripsi')
                    ->rows(4)
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_indikator')
            ->modifyQueryUsing(fn ($query) => $query->with('urusan'))
            ->columns([
                TextColumn::make('urutan')
                    ->label('#')
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('nama_indikator')
                    ->label('Indikator Kinerja')
                    ->searchable()
                    ->wrap()
                    ->limit(120),

                TextColumn::make('urusan')
                    ->label('Urusan')
                    ->getStateUsing(fn ($record) => $record->urusan->pluck('nama_urusan')->implode(', '))
                    ->wrap()
                    ->limit(80),

                TextColumn::make('realisasis_count')
                    ->label('Data Realisasi')
                    ->counts('realisasis')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->defaultSort('urutan', 'asc')
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Indikator')
                    ->visible(fn () => Auth::user()?->role === 'ADMIN'),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => Auth::user()?->role === 'ADMIN'),
                DeleteAction::make()
                    ->visible(fn () => Auth::user()?->role === 'ADMIN'),
            ]);
    }
}

