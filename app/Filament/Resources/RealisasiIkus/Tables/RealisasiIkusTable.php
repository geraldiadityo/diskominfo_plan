<?php

namespace App\Filament\Resources\RealisasiIkus\Tables;

use App\Enums\StatusRealisasi;
use App\Models\IndikatorTarget;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

use function Symfony\Component\Clock\now;

class RealisasiIkusTable
{
    public static function configure(Table $table): Table
    {
        $user   = Auth::user();
        $isSkpd = $user?->role === 'SKPD';

        return $table
            ->columns([
                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('indikator.nama_indikator')
                    ->label('Indikator Kinerja Utama')
                    ->wrap()
                    ->searchable()
                    ->description(fn ($record) => $record->indikator?->satuan?->nama_satuan ? 'Satuan: ' . $record->indikator->satuan->nama_satuan : ''),

                TextColumn::make('target_tahunan')
                    ->label('Target')
                    ->alignRight()
                    ->state(fn ($record) => IndikatorTarget::where('indikator_id', $record->indikator_id)
                        ->where('tahun', $record->tahun)
                        ->value('nilai_target') ?? '-'
                    )
                    ->suffix(fn ($record) => $record->indikator?->satuan?->nama_satuan ? ' ' . $record->indikator->satuan->nama_satuan : ''),

                TextColumn::make('nilai_realisasi')
                    ->label('Realisasi')
                    ->alignRight()
                    ->color(fn ($record) => $record->nilai_realisasi === '-' ? 'gray' : 'success')
                    ->suffix(fn ($record) => ($record->nilai_realisasi !== '-' && $record->indikator?->satuan?->nama_satuan) ? ' ' . $record->indikator->satuan->nama_satuan : ''),

                TextColumn::make('bukti_dokument')
                    ->label('Bukti')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat Bukti' : '-')
                    ->url(fn ($record) => $record->bukti_dokument ? asset('storage/' . $record->bukti_dokument) : null, shouldOpenInNewTab: true)
                    ->color(fn ($state) => $state ? 'primary' : 'gray'),

                TextColumn::make('is_tercapai')
                    ->label('Ketercapaian')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Tercapai' : 'Tidak Tercapai')
                    ->color(fn ($state): string => $state ? 'success' : 'danger')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status Verifikasi')
                    ->badge()
                    ->color(fn (StatusRealisasi $state): string => match ($state) {
                        StatusRealisasi::DRAF      => 'gray',
                        StatusRealisasi::SUBMITTED => 'warning',
                        StatusRealisasi::APPROVED  => 'success',
                        StatusRealisasi::REJECTED  => 'danger',
                    }),

                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD')
                    ->wrap()
                    ->hidden($isSkpd)
                    ->toggleable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Tahun Anggaran')
                    ->options(array_combine(range(2025, 2030), range(2025, 2030)))
                    ->default(date('Y')),

                SelectFilter::make('is_tercapai')
                    ->label('Status Ketercapaian')
                    ->options([
                        '1' => 'Tercapai',
                        '0' => 'Tidak Tercapai',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (($data['value'] ?? null) === '1') {
                            return $query->where('is_tercapai', true);
                        }
                        if (($data['value'] ?? null) === '0') {
                            return $query->where('is_tercapai', false);
                        }

                        return $query;
                    }),

                SelectFilter::make('status')
                    ->label('Status Verifikasi')
                    ->options(StatusRealisasi::class),

                SelectFilter::make('skpd_id')
                    ->label('SKPD')
                    ->relationship('skpd', 'nama_skpd')
                    ->searchable()
                    ->preload()
                    ->hidden($isSkpd),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->recordActions([
                EditAction::make()
                    ->label(fn ($record) => $isSkpd ? 'Isi Realisasi' : 'Edit')
                    ->visible(fn ($record) => $isSkpd
                        ? in_array($record->status, [StatusRealisasi::DRAF, StatusRealisasi::REJECTED])
                        : true
                    ),

                Action::make('submit')
                    ->label('Submit')
                    ->icon(Heroicon::PaperAirplane)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Realisasi IKU ke Bappeda')
                    ->modalDescription('Apakah Anda yakin data realisasi dan dokumen pendukung sudah lengkap?')
                    ->visible(fn ($record) => $isSkpd
                        && in_array($record->status, [StatusRealisasi::DRAF, StatusRealisasi::REJECTED])
                        && $record->nilai_realisasi !== '-'
                    )
                    ->action(fn ($record) => $record->update([
                        'status'       => StatusRealisasi::SUBMITTED,
                        'submitted_at' => now(),
                    ])),

                Action::make('toggle_tercapai')
                    ->label(fn ($record) => $record->is_tercapai ? 'Set Tidak Tercapai' : 'Set Tercapai')
                    ->icon(fn ($record) => $record->is_tercapai ? Heroicon::XMark : Heroicon::Check)
                    ->color(fn ($record) => $record->is_tercapai ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading('Ubah Status Ketercapaian')
                    ->modalDescription(fn ($record) => $record->is_tercapai
                        ? 'Ubah status indikator ini menjadi "Tidak Tercapai"?'
                        : 'Ubah status indikator ini menjadi "Tercapai"?'
                    )
                    ->visible(!$isSkpd)
                    ->action(fn ($record) => $record->update([
                        'is_tercapai' => ! $record->is_tercapai,
                    ])),

                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi & Setujui Realisasi IKU')
                    ->visible(fn ($record) => !$isSkpd && $record->status === StatusRealisasi::SUBMITTED)
                    ->action(fn ($record) => $record->update([
                        'status'      => StatusRealisasi::APPROVED,
                        'is_verif'    => true,
                        'verified_at' => now(),
                    ])),

                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->visible(fn ($record) => !$isSkpd && $record->status === StatusRealisasi::SUBMITTED)
                    ->form([
                        Textarea::make('catatan_bappeda')
                            ->label('Alasan Penolakan / Catatan Evaluasi')
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'status'          => StatusRealisasi::REJECTED,
                            'is_verif'        => false,
                            'catatan_bappeda' => $data['catatan_bappeda'],
                            'verified_at'     => now(),
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->hidden($isSkpd),
            ])
            ->defaultSort('tahun', 'asc');
    }
}

