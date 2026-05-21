<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class PublicDashboard extends Component
{
    public $skpdCount;
    public $realisasiKeuangan;
    public $targetPendapatan;
    public $chartDataPendapatan;
    public $chartDataRenja;

    public function mount(DashboardRepositoryInterface $repository)
    {
        $this->skpdCount = $repository->getSkpdCount();
        $this->realisasiKeuangan = $repository->getRealisasiKeuanganTotal();
        $this->targetPendapatan = $repository->getTargetPendapatanTotal();
        $this->chartDataPendapatan = $repository->getChartDataPendapatan();
        $this->chartDataRenja = $repository->getChartDataRenja();
    }

    public function render()
    {
        return view('livewire.public-dashboard');
    }
}
