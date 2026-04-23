<?php

namespace App\Filament\Resources\ProgramPrograms\RelationManager;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KegiatanRelationManager extends RelationManager
{
    protected static string $relationship = 'kegiatan';

    protected static ?string $title = 'Daftar Kegiatan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('kode')
                    ->label('Kode Kegiatan')
                    ->required(),

                Textarea::make('nomenklatur')
                    ->label('Nomenklatur Kegiatan')
                    ->required()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomenklatur')
            ->columns([
                TextColumn::make('kode_lengkap')
                    ->label('Kode'),

                TextColumn::make('nomenklatur')
                    ->wrap(),
            ])
            ->headerActions([
                CreateAction::make()
            ]);
    }
}
