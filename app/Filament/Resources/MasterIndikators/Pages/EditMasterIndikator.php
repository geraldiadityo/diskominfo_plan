<?php

namespace App\Filament\Resources\MasterIndikators\Pages;

use App\Enums\StatusRealisasi;
use App\Filament\Resources\MasterIndikators\MasterIndikatorResource;
use App\Models\IndikatorRealisasi;
use App\Models\IndikatorTarget;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;

class EditMasterIndikator extends EditRecord
{
    protected static string $resource = MasterIndikatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('isi_target_tahunan')
                ->label('Isi Target Tahunan')
                ->icon('heroicon-o-chart-bar')
                ->color('success')
                ->visible(fn($record) => $record->is_measurable)
                ->fillForm(function ($record): array {
                    $existingTargets = $record->target()->pluck('nilai_target', 'tahun')->toArray();
                    $data = [];

                    foreach (range(2025, 2029) as $year) {
                        $data['target_' . $year] = $existingTargets[$year] ?? null;
                    }

                    return $data;
                })
                ->form(function ($record) {
                    $schema = [];
                    foreach (range(2025, 2029) as $year) {
                        $schema[] = TextInput::make('target_' . $year)
                            ->label("Target Tahun {$year}")
                            ->suffix(fn($record) => $record?->satuan?->nama_satuan ?? '');
                    }

                    return [
                        Grid::make(3)->schema($schema)
                    ];
                })
                ->modalWidth('2xl')
                ->modalHeading('Input Target Tahunan')
                ->modalDescription('Masukan nilai target untuk indikator ini secara serentak')
                ->action(function (array $data, $record): void {
                    foreach (range(2025, 2029) as $year) {
                        $nilai = $data['target_' . $year];

                        if ($nilai !== null && $nilai !== '') {
                            IndikatorTarget::updateOrCreate(
                                [
                                    'indikator_id' => $record->id,
                                    'tahun' => $year
                                ],
                                [
                                    'nilai_target' => $nilai
                                ],
                            );
                            if ($record->skpd_id) {
                                IndikatorRealisasi::firstOrCreate(
                                    [
                                        'indikator_id' => $record->id,
                                        'skpd_id' => $record->skpd_id,
                                        'tahun' => $year,
                                    ],
                                    [
                                        'nilai_realisasi' => '-',
                                        'status' => StatusRealisasi::DRAF,
                                    ]
                                );
                            }
                        }
                    }

                    Notification::make()
                        ->title('Berhasil')
                        ->body('Target Tahunan berhasil disimpan')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
}
