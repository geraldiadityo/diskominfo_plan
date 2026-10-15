<?php

namespace App\Filament\Resources\RealisasiPenyelenggaraans\Pages;

use App\Enums\StatusRealisasi;
use App\Filament\Resources\RealisasiPenyelenggaraans\RealisasiPenyelenggaraanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditRealisasiPenyelenggaraan extends EditRecord
{
    protected static string $resource = RealisasiPenyelenggaraanResource::class;

    protected function getHeaderActions(): array
    {
        $user   = Auth::user();
        $isSkpd = $user?->role === 'SKPD';

        return $isSkpd ? [] : [
            DeleteAction::make(),
        ];
    }

    /**
     * Pastikan role SKPD hanya dapat memperbarui nilai_realisasi dan bukti_dokument.
     * Status verifikasi dan status ketercapaian hanya dapat diubah oleh Admin/Verifikator.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user   = Auth::user();
        $isSkpd = $user?->role === 'SKPD';
        $record = $this->getRecord();

        if ($isSkpd) {
            $data['indikator_id']    = $record->indikator_id;
            $data['skpd_id']         = $record->skpd_id;
            $data['tahun']           = $record->tahun;
            $data['is_tercapai']     = $record->is_tercapai;
            $data['is_verif']        = $record->is_verif;
            $data['status']          = $record->status->value;
            $data['catatan_bappeda'] = $record->catatan_bappeda;
        } else {
            // Logika verifikasi untuk Admin / Verifikator
            $statusValue = $data['status'] instanceof StatusRealisasi ? $data['status']->value : $data['status'];

            if ($statusValue === StatusRealisasi::APPROVED->value) {
                $data['is_verif']     = true;
                $data['verified_at']  = now();
            } elseif ($statusValue === StatusRealisasi::REJECTED->value) {
                $data['is_verif']     = false;
                $data['verified_at']  = now();
            } elseif ($statusValue === StatusRealisasi::DRAF->value) {
                $data['is_verif']     = false;
                $data['submitted_at'] = null;
                $data['verified_at']  = null;
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
