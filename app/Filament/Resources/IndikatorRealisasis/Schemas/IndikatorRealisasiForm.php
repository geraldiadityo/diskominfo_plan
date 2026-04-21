<?php

namespace App\Filament\Resources\IndikatorRealisasis\Schemas;

use App\Models\IndikatorTarget;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class IndikatorRealisasiForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isSkpd = $user->role === 'SKPD';
        return $schema
            ->columns(1)
            ->components([
                //
                Section::make('Data Realisasi SKPD')
                    ->schema([
                        Select::make('indikator_id')
                            ->label('Indikator')
                            ->relationship('indikator', 'nama_indikator', fn($query) => $isSkpd ? $query->where('is_measurable', true)->where('skpd_id', $user->skpd_id) : $query->where('is_measurable', true))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(!$isSkpd),

                        Select::make('tahun')
                            ->label('Tahun Realisasi')
                            ->options(array_combine(range(2024, 2030), range(2024, 2030)))
                            ->required()
                            ->disabled(!$isSkpd),

                        TextInput::make('target_tahunan')
                            ->label('Target Tahunan')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (TextInput $component, $record) {
                                if ($record) {
                                    $target = IndikatorTarget::where('indikator_id', $record->indikator_id)
                                        ->where('tahun', $record->tahun)
                                        ->first();

                                    if ($target) {
                                        $component->state($target->nilai_target);
                                    } else {
                                        $component->state('0 / Belum Di set');
                                    }
                                }
                            })
                            ->suffix(fn($record) => $record?->indikator?->satuan?->nama_satuan ?? ''),

                        TextInput::make('nilai_realisasi')
                            ->label('Capaian / Nilai realisasi')
                            ->required()
                            ->disabled(!$isSkpd),

                        FileUpload::make('bukti_dokument')
                            ->label('Bukti Dokument')
                            ->directory('bukti_realisasi')
                            ->disk('public')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'])
                            ->maxSize(5120)
                            ->disabled(!$isSkpd)
                            ->columnSpanFull()
                    ])->columns(2),

                Section::make('Verifikasi Bappeda')
                    ->visible(fn($record) => !$isSkpd || ($record && $record->catatan_bappeda))
                    ->schema([
                        Textarea::make('catatan_bappeda')
                            ->label('Catatan / Evaluasi')
                            ->disabled($isSkpd)
                            ->columnSpanFull()
                    ])
            ]);
    }
}
