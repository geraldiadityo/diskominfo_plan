<?php

namespace App\Filament\Resources\RealisasiPenyelenggaraans\Schemas;

use App\Enums\StatusRealisasi;
use App\Models\IndikatorTarget;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class RealisasiPenyelenggaraanForm
{
    public static function configure(Schema $schema): Schema
    {
        $user   = Auth::user();
        $isSkpd = $user?->role === 'SKPD';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Indikator & Target')
                    ->schema([
                        TextInput::make('aspek_display')
                            ->label('Aspek / Kelompok')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->indikator?->parent?->nama_indikator ?? '-')
                            ),

                        TextInput::make('indikator_display')
                            ->label('Nama Indikator')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->indikator?->nama_indikator ?? '-')
                            ),

                        TextInput::make('skpd_display')
                            ->label('SKPD Penanggung Jawab')
                            ->disabled()
                            ->dehydrated(false)
                            ->hidden($isSkpd)
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->skpd?->nama_skpd ?? '-')
                            ),

                        TextInput::make('tahun')
                            ->label('Tahun Anggaran')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('target_display')
                            ->label('Target Tahunan')
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix(fn ($record) => $record?->indikator?->satuan?->nama_satuan ?? '')
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $target = IndikatorTarget::where('indikator_id', $record->indikator_id)
                                        ->where('tahun', $record->tahun)
                                        ->value('nilai_target');
                                    $component->state($target ?? '(belum diset)');
                                }
                            }),
                    ])->columns(2),

                Section::make('Isian Realisasi & Bukti (SKPD)')
                    ->schema([
                        TextInput::make('nilai_realisasi')
                            ->label('Nilai Realisasi')
                            ->required()
                            ->suffix(fn ($record) => $record?->indikator?->satuan?->nama_satuan ?? '')
                            ->helperText('Masukkan angka capaian/realisasi. Isi "-" jika belum ada data.'),

                        FileUpload::make('bukti_dokument')
                            ->label('Bukti / Dokumen Pendukung (PDF atau Gambar)')
                            ->directory('bukti_penyelenggaraan')
                            ->disk('public')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'])
                            ->maxSize(10240)
                            ->openable()
                            ->downloadable()
                            ->helperText('Format file: PDF, JPG, PNG. Ukuran maksimal 10MB.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Status Ketercapaian & Verifikasi')
                    ->schema([
                        Select::make('is_tercapai')
                            ->label('Status Ketercapaian')
                            ->options([
                                1 => 'Tercapai',
                                0 => 'Tidak Tercapai',
                            ])
                            ->default(0)
                            ->disabled($isSkpd)
                            ->helperText($isSkpd ? 'Diisi oleh Admin / Verifikator' : 'Pilih Tercapai atau Tidak Tercapai')
                            ->required(!$isSkpd),

                        Select::make('status')
                            ->label('Status Verifikasi')
                            ->options(StatusRealisasi::class)
                            ->disabled($isSkpd)
                            ->required()
                            ->default(StatusRealisasi::DRAF)
                            ->helperText($isSkpd ? 'Status verifikasi oleh Bappeda / Admin' : 'Status verifikasi laporan'),

                        Textarea::make('catatan_bappeda')
                            ->label('Catatan Verifikator / Bappeda')
                            ->disabled($isSkpd)
                            ->visible(fn ($record) => !$isSkpd || ($record && $record->catatan_bappeda))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
