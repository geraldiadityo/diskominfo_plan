<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class DetailJenisPendapatan extends Component
{
    public $jenis;
    public $tahun;
    public $title;
    public $rekeningData = [];

    public function mount($jenis)
    {
        $this->jenis = $jenis;
        $this->tahun = date('Y');

        $titles = [
            'pajak' => 'Pajak Daerah',
            'retribusi' => 'Retribusi Daerah',
            'kekayaan' => 'Hasil Pengelolaan Kekayaan',
            'lain_lain' => 'Lain-lain PAD Sah',
        ];

        $categories = [
            'pajak' => '4.1.01',
            'retribusi' => '4.1.02',
            'kekayaan' => '4.1.03',
            'lain_lain' => '4.1.04',
        ];

        if (!array_key_exists($jenis, $categories)) {
            abort(404);
        }

        $this->title = $titles[$jenis];
        $repository = app(DashboardRepositoryInterface::class);
        $this->rekeningData = $repository->getDetailRekeningByJenis($categories[$jenis], $this->tahun);
    }

    public function render()
    {
        // Hitung total untuk summary di header/kpi
        $totalTarget = collect($this->rekeningData)->sum('target');
        $totalRealisasi = collect($this->rekeningData)->sum('realisasi');
        $totalSisa = max($totalTarget - $totalRealisasi, 0);
        $persentase = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 2) : 0;

        return view('livewire.detail-jenis-pendapatan', [
            'summary' => [
                'target' => $totalTarget,
                'realisasi' => $totalRealisasi,
                'sisa' => $totalSisa,
                'persentase' => $persentase
            ]
        ]);
    }
}
