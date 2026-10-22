<?php

namespace App\Filament\Resources\TargetPendapatans\RelationManager;

use App\Models\TransaksiPendapatan;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Override;

class TransaksiPendapatanRelationManager extends RelationManager
{
    protected static string $relationship = 'transaksi_pendapatan';
    protected static ?string $modelLabel = 'Setoran';
    protected static ?string $pluralModelLabel = 'Riwayat Setoran / Realisasi';

    #[Override]
    public function form(Schema $schema): Schema
    {
        $isAdminSkpd = in_array(strtoupper(Auth::user()?->role ?? ''), ['SKPD', 'ADMIN_SKPD', 'ADMIN SKPD']);

        return $schema
            ->columns(2)
            ->components([
                DatePicker::make('tanggal_setor')
                    ->label('Tanggal Setor')
                    ->default(now())
                    ->required()
                    ->columnSpan(1),

                TextInput::make('jumlah_setor')
                    ->label('Jumlah Setor (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->columnSpan(1),

                FileUpload::make('bukti_dukung')
                    ->label('Bukti Dukung (Gambar / PDF)')
                    ->disk('public')
                    ->directory('bukti-dukung-pendapatan')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                    ->maxSize(10240)
                    ->openable()
                    ->downloadable()
                    ->nullable()
                    ->helperText('Format file: PDF atau Gambar (JPG, PNG, WEBP). Maksimal 10MB. (Opsional)')
                    ->columnSpanFull(),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('Catatan atau keterangan setoran...')
                    ->rows(2)
                    ->columnSpanFull(),

                Toggle::make('is_verif')
                    ->label('Status Verifikasi')
                    ->default(false)
                    ->disabled($isAdminSkpd)
                    ->visible(!$isAdminSkpd)
                    ->helperText('Hanya dapat diverifikasi oleh admin selain Admin SKPD.')
                    ->columnSpanFull(),
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        $isAdminSkpd = in_array(strtoupper(Auth::user()?->role ?? ''), ['SKPD', 'ADMIN_SKPD', 'ADMIN SKPD']);

        return $table
            ->recordTitleAttribute('tanggal_setor')
            ->columns([
                TextColumn::make('tanggal_setor')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('jumlah_setor')
                    ->label('Jumlah (Rp)')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('bukti_dukung')
                    ->label('Bukti Dukung')
                    ->formatStateUsing(fn(?string $state) => $state ? 'Lihat / Unduh' : '-')
                    ->url(fn(TransaksiPendapatan $record): ?string => $record->bukti_dukung ? url('storage/' . $record->bukti_dukung) : null)
                    ->openUrlInNewTab()
                    ->icon(fn(?string $state): ?Heroicon => $state ? Heroicon::ArrowDownTray : null)
                    ->color(fn(?string $state): string => $state ? 'info' : 'gray')
                    ->alignCenter(),

                TextColumn::make('is_verif')
                    ->label('Status Verif')
                    ->badge()
                    ->color(fn(bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Terverifikasi' : 'Menunggu Verif')
                    ->alignCenter(),

                TextColumn::make('keterangan')
                    ->wrap()
                    ->limit(50),
            ])
            ->defaultSort('tanggal_setor', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Setoran Baru')
                    ->icon(Heroicon::PlusCircle)
                    ->mutateFormDataUsing(function (array $data) use ($isAdminSkpd): array {
                        if ($isAdminSkpd) {
                            $data['is_verif'] = false;
                        }
                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('verifikasi')
                    ->label('Verifikasi')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Realisasi Setoran')
                    ->modalDescription('Apakah Anda yakin ingin memverifikasi setoran ini? Setelah diverifikasi, data akan masuk dalam hitungan publik.')
                    ->visible(fn(TransaksiPendapatan $record): bool => !$isAdminSkpd && !$record->is_verif)
                    ->action(function (TransaksiPendapatan $record): void {
                        $record->update(['is_verif' => true]);
                        Notification::make()
                            ->title('Setoran berhasil diverifikasi')
                            ->success()
                            ->send();
                    }),

                Action::make('batal_verifikasi')
                    ->label('Batal Verif')
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Verifikasi Setoran')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan verifikasi? Data ini tidak akan masuk dalam hitungan publik.')
                    ->visible(fn(TransaksiPendapatan $record): bool => !$isAdminSkpd && $record->is_verif)
                    ->action(function (TransaksiPendapatan $record): void {
                        $record->update(['is_verif' => false]);
                        Notification::make()
                            ->title('Verifikasi setoran dibatalkan')
                            ->warning()
                            ->send();
                    }),

                Action::make('unduh_bukti')
                    ->label('Unduh Bukti')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->visible(fn(TransaksiPendapatan $record): bool => !empty($record->bukti_dukung))
                    ->url(fn(TransaksiPendapatan $record): string => url('storage/' . $record->bukti_dukung))
                    ->openUrlInNewTab(),

                EditAction::make()
                    ->mutateFormDataUsing(function (array $data) use ($isAdminSkpd): array {
                        if ($isAdminSkpd) {
                            $data['is_verif'] = false;
                        }
                        return $data;
                    }),

                DeleteAction::make(),
            ]);
    }
}
