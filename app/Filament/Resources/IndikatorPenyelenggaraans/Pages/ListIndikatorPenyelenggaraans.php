<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\Pages;

use App\Filament\Resources\IndikatorPenyelenggaraans\IndikatorPenyelenggaraanResource;
use App\Models\MasterIndikator;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListIndikatorPenyelenggaraans extends ListRecords
{
    protected static string $resource = IndikatorPenyelenggaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'aspek' => Tab::make('Aspek (Parent)')
                ->badge(
                    MasterIndikator::whereHas('kategori', fn ($q) => $q->where('nama_kategori', 'Penyelenggaraan'))
                        ->whereNull('parent_id')
                        ->count()
                )
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('parent_id')),

            'turunan' => Tab::make('Indikator Turunan (Child)')
                ->badge(
                    MasterIndikator::whereHas('kategori', fn ($q) => $q->where('nama_kategori', 'Penyelenggaraan'))
                        ->whereNotNull('parent_id')
                        ->count()
                )
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('parent_id')),

            'semua' => Tab::make('Semua')
                ->badge(
                    MasterIndikator::whereHas('kategori', fn ($q) => $q->where('nama_kategori', 'Penyelenggaraan'))
                        ->count()
                ),
        ];
    }
}

