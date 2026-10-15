<?php

namespace App\Filament\Resources\TargetPendapatans\Schemas;

use App\Models\Rekening;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TargetPendapatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                //
                Section::make('Informasi Target Pendapatan')
                    ->schema([
                        Select::make('skpd_id')
                            ->relationship('skpd', 'nama_skpd')
                            ->default(fn() => Auth::user()->skpd_id)
                            ->disabled(fn() => Auth::user()->role === 'SKPD')
                            ->dehydrated()
                            ->required()
                            ->searchable()
                            ->columnSpanFull(),

                        TextInput::make('tahun')
                            ->label('Tahun Anggaran')
                            ->numeric()
                            ->default(date('Y'))
                            ->required(),

                        Select::make('rekening_id')
                            ->label('Rekening Pendapatan')
                            ->required()
                            ->relationship(
                                name: 'rekening',
                                modifyQueryUsing: fn(Builder $query) => $query
                                    ->where('level', 4)
                                    ->where(function (Builder $q) {
                                        $q->where('kode', 'like', '4%')
                                            ->orWhere('kode', 'like', ' 4%');
                                    })
                                    ->orderBy('kode', 'asc')
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} - {$record->uraian}")
                            ->searchable(['kode', 'uraian'])
                            ->optionsLimit(1000)
                            ->preload()
                            ->columnSpanFull(),

                        TextInput::make('target_anggaran')
                            ->label('Target Anggaran')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required(),

                        Textarea::make('keterangan_target')
                            ->label('Keterangan')
                            ->placeholder('Contoh: Retribusi Parkir Pasar Atas')
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }
}
