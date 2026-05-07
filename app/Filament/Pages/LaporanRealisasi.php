<?php

namespace App\Filament\Pages;

use App\Models\RenjaSkpd;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class LaporanRealisasi extends Page
{
    protected string $view = 'filament.pages.laporan-realisasi';
    protected static ?string $title = 'Laporan Realisasi Fisik Dan Keuangan';
    protected static bool $shouldRegisterNavigation = false;

    public function getTableData()
    {
        $query = RenjaSkpd::with([
            'subKegiatan.kegiatan.program',
            'realisasi_kegiatans'
        ]);

        if (Auth::user()->role === 'SKPD') {
            $query->where('skpd_id', Auth::user()->skpd_id);
        }

        $renjaData = $query->get();

        $laporan = [];

        foreach ($renjaData as $renja) {
            $program = $renja->subKegiatan?->kegiatan?->program;
            $kegiatan = $renja->subKegiatan?->kegiatan;
            $subKegiatan = $renja->subKegiatan;
            if (!$program || !$kegiatan || !$subKegiatan) {
                continue;
            }


            $programId = $program->id;
            $kegiatanId = $kegiatan->id;

            $realisasiKeuangan = $renja->realisasi_kegiatans->sum('realisasi_keuangan');
            $realisasiFisik = $renja->realisasi_kegiatans->sum('realisasi_fisik');

            $namaProgram = ($program->kode_lengkap ?? '') . ' - ' . ($program->nomenklatur ?? '');
            $namaKegiatan = ($kegiatan->kode_lengkap ?? '') . ' - ' . ($kegiatan->nomenklatur ?? '');
            $namaSubKegiatan = ($subKegiatan->kode_lengkap ?? '') . ' - ' . ($subKegiatan->nomenklatur ?? '');

            if (!isset($laporan[$programId])) {
                $laporan[$programId] = [
                    'nama' => $namaProgram,
                    'pagu' => 0,
                    'target_keuangan' => 0,
                    'realisasi_keuangan' => 0,
                    'kegiatans' => []
                ];
            }

            if (!isset($laporan[$programId]['kegiatans'][$kegiatanId])) {
                $laporan[$programId]['kegiatans'][$kegiatanId] = [
                    'nama' => $namaKegiatan,
                    'pagu' => 0,
                    'target_keuangan' => 0,
                    'realisasi_keuangan' => 0,
                    'sub_kegiatans' => []
                ];
            }

            $laporan[$programId]['kegiatans'][$kegiatanId]['sub_kegiatans'][] = [
                'nama' => $namaSubKegiatan,
                'pagu' => $renja->pagu_anggaran,
                'target_fisik' => $renja->target_fisik,
                'realisasi_fisik' => $realisasiFisik,
                'target_keuangan' => $renja->target_keuangan,
                'realisasi_keuangan' => $realisasiKeuangan,
            ];

            $laporan[$programId]['pagu'] += $renja->pagu_anggaran;
            $laporan[$programId]['target_keuangan'] += $renja->target_keuangan;
            $laporan[$programId]['realisasi_keuangan'] += $realisasiKeuangan;

            $laporan[$programId]['kegiatans'][$kegiatanId]['pagu'] += $renja->pagu_anggaran;
            $laporan[$programId]['kegiatans'][$kegiatanId]['target_keuangan'] += $renja->target_keuangan;
            $laporan[$programId]['kegiatans'][$kegiatanId]['realisasi_keuangan'] += $realisasiKeuangan;
        }

        return $laporan;
    }
}
