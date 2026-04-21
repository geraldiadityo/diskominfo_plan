<?php

namespace App\Filament\Resources\IndikatorRealisasis\Tables;

use App\Enums\StatusRealisasi;
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
use Illuminate\Support\Facades\Auth;

use function Symfony\Component\Clock\now;

class IndikatorRealisasisTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        $isSkpd = $user->role === 'SKPD';
        return $table
            ->columns([
                //
                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable(),

                TextColumn::make('indikator.nama_indikator')
                    ->label('Indikator')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('nilai_realisasi')
                    ->label('Realisasi')
                    ->alignRight()
                    ->color(fn($record) => $record->nilai_realisasi === '-' ? 'danger' : 'default'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(StatusRealisasi $state): string => match ($state) {
                        StatusRealisasi::DRAF => 'gray',
                        StatusRealisasi::SUBMITTED => 'warning',
                        StatusRealisasi::APPROVED => 'success',
                        StatusRealisasi::REJECTED => 'danger',
                    }),

                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD')
                    ->visible(!$isSkpd)
                    ->wrap(),
            ])
            ->filters([
                //
                SelectFilter::make('tahun')
                    ->label('Tahun Anggaran')
                    ->options([
                        2025 => '2025',
                        2026 => '2026',
                        2027 => '2027',
                        2028 => '2028',
                        2029 => '2029',
                        2030 => '2030'
                    ])
                    ->default(date('Y')),

                SelectFilter::make('status')
                    ->label('Status Laporan')
                    ->options(StatusRealisasi::class),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->recordActions([
                Action::make('submit')
                    ->label('Submit')
                    ->icon(Heroicon::PaperAirplane)
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $isSkpd && $record->status === StatusRealisasi::DRAF)
                    ->action(fn($record) => $record->update([
                        'status' => StatusRealisasi::SUBMITTED,
                        'submitted_at' => now(),
                    ])),

                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => !$isSkpd && $record->status === StatusRealisasi::SUBMITTED)
                    ->action(fn($record) => $record->update([
                        'status' => StatusRealisasi::APPROVED,
                        'verified_at' => now(),
                    ])),

                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->visible(fn($record) => !$isSkpd && $record->status === StatusRealisasi::SUBMITTED)
                    ->form([
                        Textarea::make('catatan_bappeda')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'status' => StatusRealisasi::REJECTED,
                            'catatan_bappeda' => $data['catatan_bappeda'],
                            'verified_at' => now(),
                        ]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
