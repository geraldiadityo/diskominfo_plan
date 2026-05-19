<?php

namespace App\Filament\Resources\Rekenings\Schemas;

use App\Models\Rekening;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RekeningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                //
                Section::make('Formulir Data Rekening')
                    ->description('Silahkan Isi Kode Rekening dan Uraian Sesuai Dengan Aturan BAS (Bagan Akun Standar)')
                    ->schema([
                        Select::make('parent_id')
                            ->label('Induk Rekening (parent)')
                            ->relationship('parent', 'uraian')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} - {$record->uraian}")
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $parent = Rekening::find($state);
                                    $set('level', $parent ? $parent->level + 1 : 1);
                                } else {
                                    $set('level', 1);
                                }
                            })->columnSpan(2),

                        TextInput::make('level')
                            ->label('Level')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),

                        TextInput::make('kode')
                            ->label('Kode Rekening')
                            ->placeholder('Contoh: 5.1.02.01.01.0001')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),

                        TextInput::make('uraian')
                            ->label('Uraian / Nama Rekening')
                            ->required()
                            ->columnSpan(2),
                    ])->columns(3)
            ]);
    }
}
