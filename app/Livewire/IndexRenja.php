<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class IndexRenja extends Component
{
    public $tahun;

    public function mount()
    {
        $this->tahun = date('Y');
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $skpds = $repository->getAllSkpdRenjaSummary($this->tahun);

        // Sort for Keuangan
        $topKeuangan = $skpds->sortByDesc('persentase')->take(5)->values();
        $bottomKeuangan = $skpds->sortBy('persentase')->take(5)->values();

        // Sort for Kinerja (Deviasi)
        $topKinerja = $skpds->sortByDesc('deviasi')->take(5)->values();
        $bottomKinerja = $skpds->sortBy('deviasi')->take(5)->values();

        // Sort for Fisik
        $topFisik = $skpds->sortByDesc('persentase_fisik')->take(5)->values();
        $bottomFisik = $skpds->sortBy('persentase_fisik')->take(5)->values();

        // Default sort for table
        $skpds = $skpds->sortByDesc('persentase')->values();

        return view('livewire.index-renja', [
            'skpds' => $skpds,
            'topKeuangan' => $topKeuangan,
            'bottomKeuangan' => $bottomKeuangan,
            'topKinerja' => $topKinerja,
            'bottomKinerja' => $bottomKinerja,
            'topFisik' => $topFisik,
            'bottomFisik' => $bottomFisik,
        ]);
    }
}
