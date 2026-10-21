<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class DetailJenisPendapatan extends Component
{
    public $jenis;

    #[Url(as: 'tahun')]
    public $tahun;

    public $title;
    public $rekeningData = [];

    public function mount($jenis)
    {
        $this->jenis = $jenis;
        if (!$this->tahun) {
            $this->tahun = (int) request()->query('tahun', date('Y'));
        }

        $titles = [
            'pajak' => 'Pajak Daerah',
            'retribusi' => 'Retribusi Daerah',
            'kekayaan' => 'Hasil Pengelolaan Kekayaan',
            'lain_lain' => 'Lain-lain PAD Sah',
        ];

        if (!array_key_exists($jenis, $titles)) {
            abort(404);
        }

        $this->title = $titles[$jenis];
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $categories = [
            'pajak' => '4.1.01',
            'retribusi' => '4.1.02',
            'kekayaan' => '4.1.03',
            'lain_lain' => '4.1.04',
        ];

        $this->rekeningData = $repository->getDetailRekeningByJenis($categories[$this->jenis], $this->tahun);
        $availableYears = $repository->getAvailablePendapatanYears();

        // Hitung total untuk summary di header/kpi
        $totalTarget = collect($this->rekeningData)->sum('target');
        $totalRealisasi = collect($this->rekeningData)->sum('realisasi');
        $totalSisa = max($totalTarget - $totalRealisasi, 0);
        $persentase = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 2) : 0;

        return view('livewire.detail-jenis-pendapatan', [
            'availableYears' => $availableYears,
            'summary' => [
                'target' => $totalTarget,
                'realisasi' => $totalRealisasi,
                'sisa' => $totalSisa,
                'persentase' => $persentase
            ]
        ]);
    }
}
