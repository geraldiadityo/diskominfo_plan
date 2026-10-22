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
                            ->relationship(
                                name: 'skpd',
                                titleAttribute: 'nama_skpd',
                                modifyQueryUsing: fn(Builder $query) => $query->orderBy('nama_skpd')
                            )
                            ->default(fn() => Auth::user()?->skpd_id)
                            ->disabled(fn() => in_array(strtoupper(Auth::user()?->role ?? ''), ['SKPD', 'ADMIN_SKPD', 'ADMIN SKPD']))
                            ->dehydrated()
                            ->required()
                            ->searchable()
                            ->preload()
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
                                    ->whereBetween('level', [1, 6])
                                    ->where('kode', 'like', '4%')
                                    ->orderBy('kode', 'asc')
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} - {$record->uraian}")
                            ->searchable(['kode', 'uraian'])
                            ->optionsLimit(2000)
                            ->preload()
                            ->helperText('Pilih rekening pendapatan (Level 1 s/d 6). Disarankan memilih rekening paling spesifik (Level 5 atau 6) yang akan dihitung otomatis ke rekening induk.')
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
