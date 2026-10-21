<?php

namespace App\Filament\Resources\ProgulRealisasis\RelationManagers;

use App\Models\ProgulBuktiDukung;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BuktiDukungRelationManager extends RelationManager
{
    protected static string $relationship = 'buktiDukung';
    protected static ?string $title = 'Bukti Dukung Realisasi';
    protected static ?string $modelLabel = 'Bukti Dukung';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('keterangan')
                    ->label('Keterangan / Label Bukti')
                    ->placeholder('Contoh: Laporan Triwulan I, SK Walikota, dll.')
                    ->nullable()
                    ->columnSpanFull(),

                FileUpload::make('file_bukti')
                    ->label('Upload File Bukti')
                    ->disk('public')
                    ->directory('bukti-progul')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'])
                    ->maxSize(5120) // 5 MB
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('keterangan')
            ->columns([
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('Tanpa keterangan')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('status_verifikasi')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'belum_diupload'       => 'gray',
                        'menunggu_verifikasi'  => 'warning',
                        'disetujui'            => 'success',
                        'ditolak'              => 'danger',
                        default                => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'belum_diupload'       => 'Belum Diupload',
                        'menunggu_verifikasi'  => 'Menunggu Verifikasi',
                        'disetujui'            => 'Disetujui',
                        'ditolak'              => 'Ditolak',
                        default                => $state,
                    }),

                TextColumn::make('catatan_verifikasi')
                    ->label('Catatan')
                    ->limit(40)
                    ->placeholder('-'),

                TextColumn::make('file_bukti')
                    ->label('File')
                    ->formatStateUsing(fn (?string $state) => $state ? 'Lihat / Unduh' : '-')
                    ->url(fn (ProgulBuktiDukung $record): ?string => $record->file_bukti
                        ? url('storage/' . $record->file_bukti)
                        : null)
                    ->openUrlInNewTab()
                    ->color(fn (?string $state): ?string => $state ? 'primary' : 'gray')
                    ->icon(fn (?string $state): ?string => $state ? 'heroicon-o-document-arrow-down' : null),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Upload Bukti Dukung')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['status_verifikasi'] = 'menunggu_verifikasi';
                        return $data;
                    })
                    ->visible(fn () => in_array(Auth::user()?->role, ['ADMIN', 'SKPD'])),
            ])
            ->recordActions([
                Action::make('verifikasi')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Select::make('status_verifikasi')
                            ->label('Keputusan')
                            ->options([
                                'disetujui' => 'Setujui',
                                'ditolak'   => 'Tolak',
                            ])
                            ->required(),
                        Textarea::make('catatan_verifikasi')
                            ->label('Catatan Verifikasi')
                            ->required(fn (callable $get) => $get('status_verifikasi') === 'ditolak'),
                    ])
                    ->action(fn (ProgulBuktiDukung $record, array $data) => $record->update([
                        'status_verifikasi'  => $data['status_verifikasi'],
                        'catatan_verifikasi' => $data['catatan_verifikasi'] ?? null,
                    ]))
                    ->visible(fn () => in_array(Auth::user()?->role, ['ADMIN', 'BAPPEDA'])),

                EditAction::make()
                    ->visible(fn () => in_array(Auth::user()?->role, ['ADMIN', 'SKPD'])),

                DeleteAction::make()
                    ->before(function (ProgulBuktiDukung $record) {
                        if ($record->file_bukti && Storage::disk('public')->exists($record->file_bukti)) {
                            Storage::disk('public')->delete($record->file_bukti);
                        }
                    })
                    ->visible(fn () => in_array(Auth::user()?->role, ['ADMIN', 'SKPD'])),
            ]);
    }
}

