<?php

namespace App\Filament\Resources\RenjaSkpds\Pages;

use App\Filament\Pages\LaporanRealisasi;
use App\Filament\Resources\RenjaSkpds\RenjaSkpdResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListRenjaSkpds extends ListRecords
{
    protected static string $resource = RenjaSkpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bukaLaporan')
                ->label('Lihat Laporan')
                ->color('info')
                ->icon(Heroicon::PresentationChartBar)
                ->url(fn(): string => LaporanRealisasi::getUrl()),
            CreateAction::make(),
        ];
    }
}
