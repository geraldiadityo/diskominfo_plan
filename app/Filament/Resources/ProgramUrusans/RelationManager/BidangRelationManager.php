<?php

namespace App\Filament\Resources\ProgramUrusans\RelationManager;

use App\Filament\Resources\ProgramBidangs\ProgramBidangResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BidangRelationManager extends RelationManager
{
    protected static string $relationship = 'bidang';

    protected static ?string $title = 'Daftar Bidang Urusan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode')
                    ->required()
                    ->label('Kode Bidang'),

                Textarea::make('nomenklatur')
                    ->required()
                    ->label('Nomenklatur Bidang'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomenklatur')
            ->columns([
                TextColumn::make('kode_lengkap')
                    ->label('Kode')
                    ->sortable(),

                TextColumn::make('nomenklatur')
                    ->label('Nomenklatur Bidang')
                    ->wrap(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('buka_bidang')
                    ->label('Lihat Program')
                    ->icon(Heroicon::ArrowRightCircle)
                    ->color('primary')
                    ->url(fn($record): string => ProgramBidangResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
