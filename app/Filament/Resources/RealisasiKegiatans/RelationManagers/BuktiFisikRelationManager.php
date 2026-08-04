<?php

namespace App\Filament\Resources\RealisasiKegiatans\RelationManagers;

use App\Models\BuktiFisikRealisasi;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BuktiFisikRelationManager extends RelationManager
{
    protected static string $relationship = 'bukti_fisik';
    protected static ?string $title = 'Indikator Fisik & Bukti';
    protected static ?string $modelLabel = 'Indikator Fisik';
    protected static ?string $pluralModelLabel = 'Indikator Fisik';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_indikator')
                    ->label('Nama Indikator')
                    ->required()
                    ->maxLength(255),
                TextInput::make('target')
                    ->label('Target')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_indikator')
            ->columns([
                TextColumn::make('nama_indikator')
                    ->label('Nama Indikator')
                    ->searchable(),
                TextColumn::make('target')
                    ->label('Target'),
                TextColumn::make('realisasi')
                    ->label('Hasil Realisasi')
                    ->placeholder('Belum diisi'),
                TextColumn::make('status_verifikasi')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'belum_diupload' => 'gray',
                        'menunggu_verifikasi' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'belum_diupload' => 'Belum Diupload',
                        'menunggu_verifikasi' => 'Menunggu Verifikasi',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        default => $state,
                    }),
                TextColumn::make('catatan_verifikasi')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if ($state === null) return null;
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),
                TextColumn::make('file_bukti')
                    ->label('File Bukti')
                    ->formatStateUsing(fn(?string $state) => $state ? 'Download' : '-')
                    ->url(fn (BuktiFisikRealisasi $record): ?string => $record->file_bukti ? url('storage/' . $record->file_bukti) : null)
                    ->openUrlInNewTab()
                    ->color(fn (?string $state): ?string => $state ? 'primary' : 'gray')
                    ->icon(fn (?string $state): ?string => $state ? 'heroicon-o-document-arrow-down' : null)
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Indikator')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['status_verifikasi'] = 'belum_diupload';
                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('upload_bukti')
                    ->label('Upload Bukti')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->form([
                        TextInput::make('realisasi')
                            ->label('Hasil Realisasi (contoh: 1 Laporan / 100%)')
                            ->required(),
                        FileUpload::make('file_bukti')
                            ->label('Upload Bukti Dokumen (PDF)')
                            ->directory('bukti-realisasi-fisik')
                            ->acceptedFileTypes(['application/pdf'])
                            ->required(),
                    ])
                    ->action(function (BuktiFisikRealisasi $record, array $data): void {
                        $record->update([
                            'realisasi' => $data['realisasi'],
                            'file_bukti' => $data['file_bukti'],
                            'status_verifikasi' => 'menunggu_verifikasi',
                        ]);
                    })
                    ->visible(fn (BuktiFisikRealisasi $record): bool => in_array(Auth::user()->role, ['SKPD', 'ADMIN', 'admin'])),
                
                Action::make('verifikasi')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Select::make('status_verifikasi')
                            ->label('Keputusan')
                            ->options([
                                'disetujui' => 'Setujui',
                                'ditolak' => 'Tolak',
                            ])
                            ->required(),
                        Textarea::make('catatan_verifikasi')
                            ->label('Catatan Verifikasi (Wajib jika ditolak)')
                            ->required(fn (callable $get) => $get('status_verifikasi') === 'ditolak'),
                    ])
                    ->action(function (BuktiFisikRealisasi $record, array $data): void {
                        $record->update([
                            'status_verifikasi' => $data['status_verifikasi'],
                            'catatan_verifikasi' => $data['catatan_verifikasi'],
                        ]);
                    })
                    ->visible(fn (BuktiFisikRealisasi $record): bool => Auth::user()->role !== 'SKPD'),

                EditAction::make()
                    ->visible(fn (BuktiFisikRealisasi $record): bool => in_array(Auth::user()->role, ['SKPD', 'ADMIN', 'admin'])),
                DeleteAction::make()
                    ->visible(fn (BuktiFisikRealisasi $record): bool => in_array(Auth::user()->role, ['SKPD', 'ADMIN', 'admin'])),
            ]);
    }
}
