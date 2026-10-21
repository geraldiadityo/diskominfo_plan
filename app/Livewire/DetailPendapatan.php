<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class DetailPendapatan extends Component
{
    public $skpdId;

    #[Url(as: 'tahun')]
    public $tahun;

    public function mount($skpdId)
    {
        $this->skpdId = $skpdId;
        if (!$this->tahun) {
            $this->tahun = (int) request()->query('tahun', date('Y'));
        }
    }

    public function updatedTahun()
    {
        $repository = app(DashboardRepositoryInterface::class);
        $detail = $repository->getPendapatanDetailBySkpd($this->skpdId, $this->tahun);
        $rekeningData = $repository->getRekeningPendapatanBySkpd($this->skpdId, $this->tahun);
        $this->dispatch('detail-chart-updated', [
            'detail' => $detail,
            'rekeningData' => $rekeningData,
        ]);
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $skpd = $repository->getSkpdById($this->skpdId);
        
        if (!$skpd) {
            abort(404, 'SKPD tidak ditemukan');
        }

        $detail = $repository->getPendapatanDetailBySkpd($this->skpdId, $this->tahun);
        $rekeningData = $repository->getRekeningPendapatanBySkpd($this->skpdId, $this->tahun);
        $availableYears = $repository->getAvailablePendapatanYears();

        return view('livewire.detail-pendapatan', [
            'skpd' => $skpd,
            'detail' => $detail,
            'rekeningData' => $rekeningData,
            'availableYears' => $availableYears,
        ]);
    }
}
