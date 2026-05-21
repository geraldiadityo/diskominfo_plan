<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class DetailPendapatan extends Component
{
    public $skpdId;
    public $tahun;

    public function mount($skpdId)
    {
        $this->skpdId = $skpdId;
        $this->tahun = date('Y');
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $skpd = $repository->getSkpdById($this->skpdId);
        
        if (!$skpd) {
            abort(404, 'SKPD tidak ditemukan');
        }

        $detail = $repository->getPendapatanDetailBySkpd($this->skpdId, $this->tahun);
        $rekeningData = $repository->getRekeningPendapatanBySkpd($this->skpdId, $this->tahun);

        return view('livewire.detail-pendapatan', [
            'skpd' => $skpd,
            'detail' => $detail,
            'rekeningData' => $rekeningData,
        ]);
    }
}
