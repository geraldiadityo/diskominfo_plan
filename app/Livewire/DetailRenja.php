<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class DetailRenja extends Component
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

        $detail = $repository->getRenjaDetailBySkpd($this->skpdId, $this->tahun);
        $kegiatanData = $repository->getSubKegiatanRenjaBySkpd($this->skpdId, $this->tahun);
        $tableData = $repository->getRenjaTableDataBySkpd($this->skpdId, $this->tahun);

        // Sort by biggest target
        $kegiatanData = $kegiatanData->sortByDesc('target')->values();

        return view('livewire.detail-renja', [
            'skpd' => $skpd,
            'detail' => $detail,
            'kegiatanData' => $kegiatanData,
            'tableData' => $tableData,
        ]);
    }
}
