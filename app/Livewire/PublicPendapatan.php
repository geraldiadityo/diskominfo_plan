<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class PublicPendapatan extends Component
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
        $padTree        = $repository->getPendapatanTree($this->tahun);
        $padNode        = collect($padTree)->firstWhere('kode', '4.1');
        $totalTarget    = (float) ($padNode['target']    ?? 0);
        $totalRealisasi = (float) ($padNode['realisasi'] ?? 0);

        $this->dispatch('chart-data-updated', [
            'target'    => $totalTarget,
            'realisasi' => $totalRealisasi,
        ]);
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        // Ambil data tree, filter hanya node PAD (kode 4.1 dan turunannya)
        $fullTree = $repository->getPendapatanTree($this->tahun);

        // Node 4.1 untuk total PAD
        $padNode        = collect($fullTree)->firstWhere('kode', '4.1');
        $totalTarget    = (float) ($padNode['target']    ?? 0);
        $totalRealisasi = (float) ($padNode['realisasi'] ?? 0);

        // Tabel SKPD — hanya SKPD yang mengisi PAD
        $skpds = $repository->getAllSkpdPadSummary($this->tahun);
        $skpds = $skpds->sortByDesc('persentase')->values();

        // Ambil summary 4 kategori PAD (Pajak, Retribusi, Kekayaan, Lain-lain PAD) untuk 4 panel kartu kanan
        $summaryByJenis = $repository->getPendapatanSummaryByJenisRekening($this->tahun);

        $lastUpdate     = $repository->getLastUpdatePendapatan($this->tahun);
        $availableYears = $repository->getAvailablePendapatanYears();

        return view('livewire.public-pendapatan', [
            'totalTarget'    => $totalTarget,
            'totalRealisasi' => $totalRealisasi,
            'skpds'          => $skpds,
            'summaryByJenis' => $summaryByJenis,
            'tahun'          => $this->tahun,
            'lastUpdate'     => $lastUpdate,
            'availableYears' => $availableYears,
        ]);
    }
}
