<?php

namespace App\Filament\Resources\RealisasiIkus\Pages;

use App\Enums\StatusRealisasi;
use App\Filament\Resources\RealisasiIkus\RealisasiIkuResource;
use App\Models\IndikatorRealisasi;
use App\Models\KategoriIndikator;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListRealisasiIkus extends ListRecords
{
    protected static string $resource = RealisasiIkuResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $user       = Auth::user();
        $isSkpd     = $user?->role === 'SKPD';
        $kategoriId = KategoriIndikator::where('nama_kategori', 'IKU')->value('id');

        $base = fn () => IndikatorRealisasi::whereHas('indikator', fn ($query) => $query->where('kategori_id', $kategoriId))
            ->when($isSkpd, fn ($query) => $query->where('skpd_id', $user->skpd_id));

        return [
            'semua' => Tab::make('Semua')
                ->badge($base()->count()),

            'draf' => Tab::make('Belum Diisi / Draft')
                ->badge($base()->where('status', StatusRealisasi::DRAF)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusRealisasi::DRAF)),

            'submitted' => Tab::make('Menunggu Verifikasi')
                ->badge($base()->where('status', StatusRealisasi::SUBMITTED)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusRealisasi::SUBMITTED)),

            'approved' => Tab::make('Disetujui')
                ->badge($base()->where('status', StatusRealisasi::APPROVED)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusRealisasi::APPROVED)),

            'rejected' => Tab::make('Ditolak')
                ->badge($base()->where('status', StatusRealisasi::REJECTED)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusRealisasi::REJECTED)),
        ];
    }
}

