<?php

namespace App\Filament\Resources\IndikatorRealisasis\Pages;

use App\Filament\Resources\IndikatorRealisasis\IndikatorRealisasiResource;
use App\Models\KategoriIndikator;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListIndikatorRealisasis extends ListRecords
{
    protected static string $resource = IndikatorRealisasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'Semua' => Tab::make(),
        ];

        $kategoris = KategoriIndikator::all();

        foreach ($kategoris as $kategori) {
            $tabs[$kategori->nama_kategori] = Tab::make()->label($kategori->nama_kategori)->modifyQueryUsing(function (Builder $query) use ($kategori) {
                return $query->whereHas('indikator', function ($q) use ($kategori) {
                    $q->where('kategori_id', $kategori->id);
                });
            });
        }

        return $tabs;
    }
}
