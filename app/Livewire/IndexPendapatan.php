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
        $repository = app(DashboardRepositoryInterface::class);
        $skpds = $repository->getAllSkpdPendapatanSummary($this->tahun);
        $this->dispatch('chart-data-updated', [
            'target' => $skpds->sum('target'),
            'realisasi' => $skpds->sum('realisasi'),
        ]);
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $skpds = $repository->getAllSkpdPendapatanSummary($this->tahun);

        // Sort descending by percentage
        $skpds = $skpds->sortByDesc('persentase')->values();

        $summaryByJenis = $repository->getPendapatanSummaryByJenisRekening($this->tahun);
        $availableYears = $repository->getAvailablePendapatanYears();

        return view('livewire.index-pendapatan', [
            'skpds' => $skpds,
            'summaryByJenis' => $summaryByJenis,
            'availableYears' => $availableYears,
        ]);
    }
}
