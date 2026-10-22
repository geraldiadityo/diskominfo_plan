<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class IndexPendapatan extends Component
{
    #[Url(as: 'tahun')]
    public $tahun;
    
    public function mount()
    {
        if (!$this->tahun) {
            $this->tahun = (int) request()->query('tahun', date('Y'));
        }
    }

    public function updatedTahun()
    {
        $repository     = app(DashboardRepositoryInterface::class);
        $pendapatanData = $repository->getPendapatanTree($this->tahun);
        $totalTarget    = collect($pendapatanData)->where('level', 1)->sum('target');
        $totalRealisasi = collect($pendapatanData)->where('level', 1)->sum('realisasi');

        $this->dispatch('chart-data-updated', [
            'target'    => $totalTarget,
            'realisasi' => $totalRealisasi,
        ]);
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $pendapatanData = $repository->getPendapatanTree($this->tahun);
        $summaryLevel2  = $repository->getPendapatanSummaryByLevel2($this->tahun);
        $availableYears = $repository->getAvailablePendapatanYears();

        // Untuk grafik donut keseluruhan, ambil node level 1 (kode '4') sebagai total
        $totalTarget    = collect($pendapatanData)->where('level', 1)->sum('target');
        $totalRealisasi = collect($pendapatanData)->where('level', 1)->sum('realisasi');

        return view('livewire.index-pendapatan', [
            'pendapatanData' => $pendapatanData,
            'summaryLevel2'  => $summaryLevel2,
            'availableYears' => $availableYears,
            'totalTarget'    => $totalTarget,
            'totalRealisasi' => $totalRealisasi,
        ]);
    }
}
