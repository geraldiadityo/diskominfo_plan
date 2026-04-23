<?php

namespace App\Filament\Resources\ProgramBidangs\RelationManager;

use App\Filament\Resources\ProgramPrograms\ProgramProgramResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgramRelationManager extends RelationManager
{
    protected static string $relationship = 'program';

    protected static ?string $title = 'Daftar Program';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('kode')
                    ->label('Kode Program')
                    ->required(),

                Textarea::make('nomenklatur')
                    ->label('Nomenklatur Program')
                    ->required(),
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
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('buka_program')
                    ->label('Detail')
                    ->icon(Heroicon::ArrowRightCircle)
                    ->color('primary')
                    ->url(fn($record): string => ProgramProgramResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
