<?php

namespace App\Filament\Resources\RenjaSkpds\RelationManagers;

use App\Models\RealisasiKegiatan;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;
use Override;

class RealisasiKegiatanRelationManager extends RelationManager
{
    protected static string $relationship = 'realisasi_kegiatans';
    protected static ?string $title = 'Realisasi Triwulan';

    protected static ?string $modelLabel = 'Realisasi';
    protected static ?string $pluralModelLabel = 'Realisasi Triwulan';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('triwulan')
                    ->options([
                        1 => 'Triwulan I',
                        2 => 'Triwulan II',
                        3 => 'Triwulan III',
                        4 => 'Triwulan IV',
                    ])
                    ->label('Pilih Triwulan')
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn(Unique $rule, RelationManager $livewire) => $rule->where('renja_skpd_id', $livewire->getOwnerRecord()->id)
                    )
                    ->columnSpanFull(),

                TextInput::make('target_keuangan')
                    ->numeric()
                    ->prefix('Rp')
                    ->maxValue(fn(RelationManager $livewire) => $livewire->getOwnerRecord()->pagu_anggaran)
                    ->label('Target Keuangan (Rp)')
                    ->required()
                    ->hint(fn(RelationManager $livewire) => 'Maksimal: Rp ' . number_format($livewire->getOwnerRecord()->pagu_anggaran, 0, ',', '.'))
                    ->default(0),

                TextInput::make('realisasi_keuangan')
                    ->numeric()
                    ->prefix('Rp')
                    ->maxValue(fn(RelationManager $livewire) => $livewire->getOwnerRecord()->pagu_anggaran)
                    ->label('Realisasi Keuangan (Rp)')
                    ->required()
                    ->hint(fn(RelationManager $livewire) => 'Maksimal: Rp ' . number_format($livewire->getOwnerRecord()->pagu_anggaran, 0, ',', '.'))
                    ->default(0),

                TextInput::make('target_fisik')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->label('Target Fisik (%)')
                    ->required()
                    ->default(0),

                TextInput::make('realisasi_fisik')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->label('Realisasi Fisik (%)')
                    ->readOnly(fn(?RealisasiKegiatan $record): bool => (bool) ($record && $record->bukti_fisik()->count() > 0))
                    ->helperText(fn(?RealisasiKegiatan $record): string => ($record && $record->bukti_fisik()->count() > 0)
                        ? 'Dihitung otomatis dari Indikator Fisik & Bukti yang disetujui.'
                        : 'Dapat diisi langsung jika tidak mengunggah rincian indikator bukti fisik.')
                    ->hint(function (?RealisasiKegiatan $record): ?string {
                        if (!$record || $record->bukti_fisik()->count() === 0) {
                            return null;
                        }
                        $total = $record->bukti_fisik()->count();
                        $approved = $record->bukti_fisik()->where('status_verifikasi', 'disetujui')->count();
                        return "Disetujui: {$approved}/{$total} Indikator";
                    })
                    ->default(0)
                    ->dehydrated(),

                Toggle::make('is_verified')
                    ->label('Verifikasi Triwulan (Data masuk ke perhitungan Renja)')
                    ->helperText('Hanya data triwulan yang telah diverifikasi yang akan dihitung pada capaian Renja SKPD.')
                    ->disabled(fn(): bool => Auth::user()?->role === 'SKPD')
                    ->dehydrated(fn(): bool => Auth::user()?->role !== 'SKPD')
                    ->columnSpanFull(),

                Textarea::make('catatan')
                    ->label('Catatan / Keterangan (Optional)')
                    ->columnSpanFull(),

                Textarea::make('alasan')
                    ->label('Alasan')
                    ->disabled(fn(): bool => !in_array(Auth::user()?->role, ['SKPD', 'ADMIN']))
                    ->columnSpanFull(),

                Textarea::make('solusi')
                    ->label('Solusi')
                    ->disabled(fn(): bool => Auth::user()?->role === 'SKPD')
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('triwulan')
            ->columns([
                TextColumn::make('triwulan')
                    ->formatStateUsing(fn(string $state): string => 'Triwulan ' . ['I', 'II', 'III', 'IV'][$state - 1])
                    ->label('Triwulan')
                    ->sortable(),

                TextColumn::make('target_keuangan')
                    ->money('IDR', locale: 'id')
                    ->label('Target Keuangan')
                    ->sortable(),

                TextColumn::make('realisasi_keuangan')
                    ->money('IDR', locale: 'id')
                    ->label('Serapan Keuangan')
                    ->sortable(),

                TextColumn::make('target_fisik')
                    ->suffix('%')
                    ->label('Target Fisik')
                    ->sortable(),

                TextColumn::make('realisasi_fisik')
                    ->suffix('%')
                    ->description(function (RealisasiKegiatan $record): string {
                        $total = $record->bukti_fisik()->count();
                        $approved = $record->bukti_fisik()->where('status_verifikasi', 'disetujui')->count();
                        if ($total === 0) {
                            return 'Belum ada indikator';
                        }
                        $capaian = round(($approved / $total) * 100);
                        return "Capaian: {$capaian}% ({$approved}/{$total} Disetujui)";
                    })
                    ->label('Realisasi Fisik')
                    ->sortable(),

                TextColumn::make('catatan')
                    ->wrap()
                    ->limit(50),

                IconColumn::make('is_verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Status Verif')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Input Realisasi Baru'),
            ])
            ->recordActions([
                Action::make('verifikasi')
                    ->label('Verif')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(RealisasiKegiatan $record): bool => Auth::user()?->role !== 'SKPD' && !$record->is_verified)
                    ->action(function (RealisasiKegiatan $record): void {
                        $record->update(['is_verified' => true]);
                        Notification::make()
                            ->title('Berhasil diverifikasi')
                            ->body('Data realisasi triwulan ini sekarang masuk ke perhitungan Renja.')
                            ->success()
                            ->send();
                    }),
                Action::make('batal_verifikasi')
                    ->label('Batal Verif')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(RealisasiKegiatan $record): bool => Auth::user()?->role !== 'SKPD' && $record->is_verified)
                    ->action(function (RealisasiKegiatan $record): void {
                        $record->update(['is_verified' => false]);
                        Notification::make()
                            ->title('Verifikasi dibatalkan')
                            ->body('Data realisasi triwulan ini dikeluarkan dari perhitungan Renja.')
                            ->warning()
                            ->send();
                    }),
                Action::make('kelola_bukti_fisik')
                    ->label('Kelola Detail & Bukti')
                    ->icon('heroicon-o-list-bullet')
                    ->color('info')
                    ->url(fn(RealisasiKegiatan $record): string => route('filament.admin.resources.realisasi-kegiatans.edit', $record)),
                Action::make('hitung_ulang')
                    ->label('Hitung Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(function (RealisasiKegiatan $record): void {
                        $record->recalculateRealisasiFisik();
                        Notification::make()
                            ->title('Realisasi fisik berhasil dihitung ulang')
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->after(fn(RealisasiKegiatan $record) => $record->recalculateRealisasiFisik()),
                DeleteAction::make()
            ]);
    }
}
