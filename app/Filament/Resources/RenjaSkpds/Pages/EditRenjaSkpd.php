<?php

namespace App\Filament\Resources\RenjaSkpds\Pages;

use App\Filament\Resources\RenjaSkpds\RenjaSkpdResource;
use App\Models\ProgramSubKegiatan;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditRenjaSkpd extends EditRecord
{
    protected static string $resource = RenjaSkpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $sub = ProgramSubKegiatan::with('kegiatan.program.bidang')->find($data['sub_kegiatan_id']);

        if ($sub) {
            $data['kegiatan_id'] = $sub->kegiatan_id;

            if ($sub->kegiatan) {
                $data['program_id'] = $sub->kegiatan->program_id;

                if ($sub->kegiatan->program) {
                    $data['bidang_id'] = $sub->kegiatan->program->bidang_id;

                    if ($sub->kegiatan->program->bidang) {
                        $data['urusan_id'] = $sub->kegiatan->program->bidang->urusan_id;
                    }
                }
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
}
