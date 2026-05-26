<?php

namespace App\Repositories\Eloquent;

use App\Models\Skpd;
use App\Models\RealisasiKegiatan;
use App\Models\TargetPendapatan;
use App\Models\TransaksiPendapatan;
use App\Repositories\DashboardRepositoryInterface;

class DashboardRepository implements DashboardRepositoryInterface
{
    /**
     * Get the total number of SKPDs.
     *
     * @return int
     */
    public function getSkpdCount(): int
    {
        return Skpd::count();
    }

    /**
     * Get the total realized budget (keuangan).
     *
     * @return float
     */
    public function getRealisasiKeuanganTotal(): float
    {
        return (float) RealisasiKegiatan::sum('realisasi_keuangan');
    }

    /**
     * Get the total target revenue.
     *
     * @return float
     */
    public function getTargetPendapatanTotal(): float
    {
        return (float) TargetPendapatan::sum('target_anggaran');
    }

    /**
     * Get chart data for revenue (target vs realized).
     *
     * @return array
     */
    public function getChartDataPendapatan(): array
    {
        $targetTotal = $this->getTargetPendapatanTotal();
        $realisasiTotal = (float) TransaksiPendapatan::sum('jumlah_setor');

        return [
            'target' => $targetTotal,
            'realisasi' => $realisasiTotal,
            'persentase' => $targetTotal > 0 ? round(($realisasiTotal / $targetTotal) * 100, 2) : 0,
        ];
    }

    public function getSkpdById(int $skpdId)
    {
        return Skpd::find($skpdId);
    }

    public function getAllSkpdPendapatanSummary(?int $tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        return Skpd::whereHas('target_pendapatan', function ($query) use ($tahun) {
            $query->where('tahun', $tahun);
        })->orderBy('nama_skpd')->get()->map(function ($skpd) use ($tahun) {
            $target = (float) TargetPendapatan::where('skpd_id', $skpd->id)
                ->where('tahun', $tahun)
                ->sum('target_anggaran');

            $realisasi = (float) TransaksiPendapatan::whereHas('target_pendapatan', function ($query) use ($skpd, $tahun) {
                $query->where('skpd_id', $skpd->id)->where('tahun', $tahun);
            })->sum('jumlah_setor');

            $persentase = $target > 0 ? round(($realisasi / $target) * 100, 2) : 0;

            return [
                'id' => $skpd->id,
                'nama_skpd' => $skpd->nama_skpd,
                'target' => $target,
                'realisasi' => $realisasi,
                'sisa' => max($target - $realisasi, 0),
                'persentase' => $persentase,
            ];
        });
    }

    public function getPendapatanDetailBySkpd(int $skpdId, ?int $tahun = null): array
    {
        $tahun = $tahun ?? date('Y');

        $targetTotal = (float) TargetPendapatan::where('skpd_id', $skpdId)
            ->where('tahun', $tahun)
            ->sum('target_anggaran');

        $realisasiTotal = (float) TransaksiPendapatan::whereHas('target_pendapatan', function ($query) use ($skpdId, $tahun) {
            $query->where('skpd_id', $skpdId)->where('tahun', $tahun);
        })->sum('jumlah_setor');

        return [
            'target' => $targetTotal,
            'realisasi' => $realisasiTotal,
            'sisa' => max($targetTotal - $realisasiTotal, 0),
            'persentase' => $targetTotal > 0 ? round(($realisasiTotal / $targetTotal) * 100, 2) : 0,
        ];
    }

    public function getRekeningPendapatanBySkpd(int $skpdId, ?int $tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        return TargetPendapatan::with(['rekening', 'transaksi_pendapatan'])
            ->where('skpd_id', $skpdId)
            ->where('tahun', $tahun)
            ->get()
            ->map(function ($target) {
                $realisasi = $target->transaksi_pendapatan->sum('jumlah_setor');
                $persentase = $target->target_anggaran > 0 ? round(($realisasi / $target->target_anggaran) * 100, 2) : 0;
                
                return [
                    'kode_rekening' => $target->rekening->kode ?? '-',
                    'nama_rekening' => $target->rekening->uraian ?? '-',
                    'target' => $target->target_anggaran,
                    'realisasi' => $realisasi,
                    'selisih' => $target->target_anggaran - $realisasi,
                    'persentase' => $persentase,
                ];
            });
    }

    public function getChartDataRenja(): array
    {
        $targetTotal = (float) \App\Models\RenjaSkpd::sum('target_keuangan');
        $realisasiTotal = (float) \App\Models\RealisasiKegiatan::sum('realisasi_keuangan');

        return [
            'target' => $targetTotal,
            'realisasi' => $realisasiTotal,
            'persentase' => $targetTotal > 0 ? round(($realisasiTotal / $targetTotal) * 100, 2) : 0,
        ];
    }

    public function getAllSkpdRenjaSummary(?int $tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        $skpdIds = \App\Models\RenjaSkpd::where('tahun', $tahun)->pluck('skpd_id')->unique();

        return Skpd::whereIn('id', $skpdIds)->orderBy('nama_skpd')->get()->map(function ($skpd) use ($tahun) {
            $renjas = \App\Models\RenjaSkpd::with('realisasi_kegiatans')
                ->where('skpd_id', $skpd->id)
                ->where('tahun', $tahun)
                ->get();

            $target = 0;
            $realisasi = 0;
            $sumTargetFisik = 0;
            $sumRealisasiFisik = 0;
            $count = $renjas->count();

            foreach ($renjas as $renja) {
                $target += (float) $renja->target_keuangan;
                $realisasi += (float) $renja->realisasi_kegiatans->sum('realisasi_keuangan');

                $sumTargetFisik += (float) $renja->target_fisik;
                $sumRealisasiFisik += (float) $renja->realisasi_kegiatans->sum('realisasi_fisik');
            }

            $persentase = $target > 0 ? round(($realisasi / $target) * 100, 2) : 0;
            $persentaseTargetFisik = $count > 0 ? round($sumTargetFisik / $count, 2) : 0;
            $persentaseRealisasiFisik = $count > 0 ? round($sumRealisasiFisik / $count, 2) : 0;
            $deviasi = $persentaseRealisasiFisik - $persentaseTargetFisik;

            return [
                'id' => $skpd->id,
                'nama_skpd' => $skpd->nama_skpd,
                'target' => $target,
                'realisasi' => $realisasi,
                'sisa' => max($target - $realisasi, 0),
                'persentase' => $persentase,
                'persentase_fisik' => $persentaseRealisasiFisik,
                'deviasi' => round($deviasi, 2),
            ];
        });
    }

    public function getRenjaDetailBySkpd(int $skpdId, ?int $tahun = null): array
    {
        $tahun = $tahun ?? date('Y');

        $targetTotal = (float) \App\Models\RenjaSkpd::where('skpd_id', $skpdId)
            ->where('tahun', $tahun)
            ->sum('target_keuangan');

        $realisasiTotal = (float) \App\Models\RealisasiKegiatan::whereHas('renja_skpd', function ($query) use ($skpdId, $tahun) {
            $query->where('skpd_id', $skpdId)->where('tahun', $tahun);
        })->sum('realisasi_keuangan');

        return [
            'target' => $targetTotal,
            'realisasi' => $realisasiTotal,
            'sisa' => max($targetTotal - $realisasiTotal, 0),
            'persentase' => $targetTotal > 0 ? round(($realisasiTotal / $targetTotal) * 100, 2) : 0,
        ];
    }

    public function getSubKegiatanRenjaBySkpd(int $skpdId, ?int $tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        return \App\Models\RenjaSkpd::with(['subKegiatan', 'realisasi_kegiatans'])
            ->where('skpd_id', $skpdId)
            ->where('tahun', $tahun)
            ->get()
            ->map(function ($renja) {
                $realisasi = (float) $renja->realisasi_kegiatans->sum('realisasi_keuangan');
                $target = (float) $renja->target_keuangan;
                $persentase = $target > 0 ? round(($realisasi / $target) * 100, 2) : 0;
                
                return [
                    'kode_rekening' => $renja->subKegiatan->kode_lengkap ?? '-',
                    'nama_rekening' => $renja->subKegiatan->nomenklatur ?? '-',
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'selisih' => $target - $realisasi,
                    'persentase' => $persentase,
                    'target_fisik' => (float) $renja->target_fisik,
                    'realisasi_fisik' => (float) $renja->realisasi_kegiatans->sum('realisasi_fisik'),
                ];
            });
    }

    public function getRenjaTableDataBySkpd(int $skpdId, ?int $tahun = null): array
    {
        $tahun = $tahun ?? date('Y');

        $renjaData = \App\Models\RenjaSkpd::with([
            'subKegiatan.kegiatan.program',
            'realisasi_kegiatans'
        ])
        ->where('skpd_id', $skpdId)
        ->where('tahun', $tahun)
        ->get();

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

            $realisasiKeuangan = (float) $renja->realisasi_kegiatans->sum('realisasi_keuangan');
            $realisasiFisik = (float) $renja->realisasi_kegiatans->sum('realisasi_fisik');

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
                'pagu' => (float) $renja->pagu_anggaran,
                'target_fisik' => (float) $renja->target_fisik,
                'realisasi_fisik' => $realisasiFisik,
                'target_keuangan' => (float) $renja->target_keuangan,
                'realisasi_keuangan' => $realisasiKeuangan,
            ];

            $laporan[$programId]['pagu'] += (float) $renja->pagu_anggaran;
            $laporan[$programId]['target_keuangan'] += (float) $renja->target_keuangan;
            $laporan[$programId]['realisasi_keuangan'] += $realisasiKeuangan;

            $laporan[$programId]['kegiatans'][$kegiatanId]['pagu'] += (float) $renja->pagu_anggaran;
            $laporan[$programId]['kegiatans'][$kegiatanId]['target_keuangan'] += (float) $renja->target_keuangan;
            $laporan[$programId]['kegiatans'][$kegiatanId]['realisasi_keuangan'] += $realisasiKeuangan;
        }

        return $laporan;
    }

    public function getPendapatanSummaryByJenisRekening(?int $tahun = null): array
    {
        $tahun = $tahun ?? date('Y');

        $categories = [
            'pajak' => ['kode' => '4.1.01', 'nama' => 'Pajak Daerah'],
            'retribusi' => ['kode' => '4.1.02', 'nama' => 'Retribusi Daerah'],
            'kekayaan' => ['kode' => '4.1.03', 'nama' => 'Hasil Pengelolaan Kekayaan'],
            'lain_lain' => ['kode' => '4.1.04', 'nama' => 'Lain-lain PAD Sah'],
        ];

        $result = [];

        foreach ($categories as $key => $cat) {
            $kode = $cat['kode'];
            
            $targetTotal = (float) \App\Models\TargetPendapatan::whereHas('rekening', function($query) use ($kode) {
                    $query->where('kode', 'like', $kode . '%');
                })
                ->where('tahun', $tahun)
                ->sum('target_anggaran');

            $realisasiTotal = (float) \App\Models\TransaksiPendapatan::whereHas('target_pendapatan', function($query) use ($kode, $tahun) {
                    $query->where('tahun', $tahun)
                          ->whereHas('rekening', function($q) use ($kode) {
                              $q->where('kode', 'like', $kode . '%');
                          });
                })->sum('jumlah_setor');

            $persentase = $targetTotal > 0 ? round(($realisasiTotal / $targetTotal) * 100, 2) : 0;

            $result[$key] = [
                'nama' => $cat['nama'],
                'target' => $targetTotal,
                'realisasi' => $realisasiTotal,
                'persentase' => $persentase
            ];
        }

        return $result;
    }

    public function getDetailRekeningByJenis(string $kodePrefix, ?int $tahun = null): array
    {
        $tahun = $tahun ?? date('Y');

        // TargetPendapatan with its Rekening, grouped by Rekening
        $targets = \App\Models\TargetPendapatan::with(['rekening', 'skpd'])
            ->whereHas('rekening', function ($query) use ($kodePrefix) {
                $query->where('kode', 'like', $kodePrefix . '%');
            })
            ->where('tahun', $tahun)
            ->get();

        $rekeningMap = [];

        foreach ($targets as $target) {
            if (!$target->rekening) continue;
            
            $rekId = $target->rekening_id;
            
            if (!isset($rekeningMap[$rekId])) {
                $rekeningMap[$rekId] = [
                    'kode' => $target->rekening->kode,
                    'uraian' => $target->rekening->uraian,
                    'target' => 0,
                    'realisasi' => 0,
                ];
            }

            $rekeningMap[$rekId]['target'] += (float) $target->target_anggaran;
            
            $realisasi = (float) \App\Models\TransaksiPendapatan::where('target_pendapatan_id', $target->id)->sum('jumlah_setor');
            $rekeningMap[$rekId]['realisasi'] += $realisasi;
        }

        $list = [];
        foreach ($rekeningMap as $item) {
            $persentase = $item['target'] > 0 ? round(($item['realisasi'] / $item['target']) * 100, 2) : 0;
            $item['persentase'] = $persentase;
            $list[] = $item;
        }

        // Sort by kode ascending
        usort($list, function($a, $b) {
            return strcmp($a['kode'], $b['kode']);
        });

        return $list;
    }

    public function getPendapatanTree(?int $tahun = null): array
    {
        $tahun = $tahun ?? date('Y');

        // 1. Ambil semua target & realisasi per rekening
        $targets = \App\Models\TargetPendapatan::with('transaksi_pendapatan')
            ->where('tahun', $tahun)
            ->get();
            
        $realisasiMap = [];
        foreach ($targets as $target) {
            $rekId = $target->rekening_id;
            if (!isset($realisasiMap[$rekId])) {
                $realisasiMap[$rekId] = ['target' => 0, 'realisasi' => 0];
            }
            
            $realisasiMap[$rekId]['target'] += (float) $target->target_anggaran;
            $realisasiMap[$rekId]['realisasi'] += (float) $target->transaksi_pendapatan->sum('jumlah_setor');
        }

        // 2. Ambil semua Rekening
        $rekenings = \App\Models\Rekening::all();

        // 3. Bangun struktur awal
        $nodeMap = [];
        foreach ($rekenings as $rek) {
            $nodeMap[$rek->id] = [
                'id' => $rek->id,
                'parent_id' => $rek->parent_id,
                'kode' => $rek->kode,
                'uraian' => $rek->uraian,
                'level' => $rek->level,
                'target' => $realisasiMap[$rek->id]['target'] ?? 0,
                'realisasi' => $realisasiMap[$rek->id]['realisasi'] ?? 0,
            ];
        }

        // 4. Bottom-up Rollup: Urutkan berdasarkan level terbesar (paling anak) ke atas
        $levels = array_column($nodeMap, 'level');
        array_multisort($levels, SORT_DESC, $nodeMap);

        $nodeMapById = [];
        foreach ($nodeMap as $node) {
            $nodeMapById[$node['id']] = $node;
        }

        foreach ($nodeMapById as $id => $node) {
            if ($node['parent_id'] && isset($nodeMapById[$node['parent_id']])) {
                $nodeMapById[$node['parent_id']]['target'] += $nodeMapById[$id]['target'];
                $nodeMapById[$node['parent_id']]['realisasi'] += $nodeMapById[$id]['realisasi'];
            }
        }

        // 5. Urutkan secara hierarkis berdasarkan kode ASC
        $kodeList = array_column($nodeMapById, 'kode');
        array_multisort($kodeList, SORT_ASC, $nodeMapById);

        // 6. Filter untuk hanya mengembalikan data dengan awalan kode '4' (Pendapatan) dan level <= 4
        $result = [];
        foreach ($nodeMapById as $node) {
            if (str_starts_with($node['kode'], '4') && $node['level'] <= 4) {
                $persentase = $node['target'] > 0 ? round(($node['realisasi'] / $node['target']) * 100, 2) : 0;
                $result[] = [
                    'kode' => $node['kode'],
                    'uraian' => $node['uraian'],
                    'level' => $node['level'],
                    'target' => $node['target'],
                    'realisasi' => $node['realisasi'],
                    'selisih' => max($node['target'] - $node['realisasi'], 0),
                    'persentase' => $persentase,
                ];
            }
        }

        return $result;
    }
}
