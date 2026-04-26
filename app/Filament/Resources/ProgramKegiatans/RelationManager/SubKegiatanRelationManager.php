<?php

namespace App\Filament\Resources\ProgramKegiatans\RelationManager;

use App\Filament\Resources\ProgramSubKegiatans\ProgramSubKegiatanResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubKegiatanRelationManager extends RelationManager
{
    protected static string $relationship = 'sub_kegiatan';

    protected static ?string $title = 'Daftar Sub Kegiatan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('kode')
                    ->required(),

                Textarea::make('nomenklatur')
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('kinerja')
                    ->label('Indikator Kinjerja')
                    ->columnSpanFull(),

                TextInput::make('indikator')
                    ->label('Target Indikator'),

                TextInput::make('satuan')
                    ->label('Satuan')
                    ->placeholder('Contoh: Dokumen/Unit/bulan')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomenklatur sub kegiatan')
            ->columns([
                TextColumn::make('kode_lengkap')
                    ->label('kode'),

                TextColumn::make('nomenklatur')
                    ->wrap(),

                TextColumn::make('satuan')
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('buka_detail')
                    ->label('Detail Sub Program')
                    ->icon(Heroicon::ArrowRightCircle)
                    ->color('gray')
                    ->url(fn($record): string => ProgramSubKegiatanResource::getUrl('edit', ['record' => $record]))
            ]);
    }
}
