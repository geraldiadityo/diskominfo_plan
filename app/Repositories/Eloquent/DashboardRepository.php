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
        return (float) RealisasiKegiatan::where('is_verified', true)->sum('realisasi_keuangan');
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
        $realisasiTotal = (float) TransaksiPendapatan::where('is_verif', true)->sum('jumlah_setor');

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

            $realisasi = (float) TransaksiPendapatan::where('is_verif', true)->whereHas('target_pendapatan', function ($query) use ($skpd, $tahun) {
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

    public function getAllSkpdPadSummary(?int $tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        // Ambil SKPD yang memiliki target pada rekening kode 4.1.*
        $skpdIds = TargetPendapatan::where('tahun', $tahun)
            ->whereHas('rekening', function ($q) {
                $q->where('kode', 'like', '4.1%');
            })
            ->pluck('skpd_id')
            ->unique();

        return Skpd::whereIn('id', $skpdIds)->orderBy('nama_skpd')->get()->map(function ($skpd) use ($tahun) {
            // Target hanya dari rekening 4.1.*
            $target = (float) TargetPendapatan::where('skpd_id', $skpd->id)
                ->where('tahun', $tahun)
                ->whereHas('rekening', fn($q) => $q->where('kode', 'like', '4.1%'))
                ->sum('target_anggaran');

            // Realisasi hanya dari transaksi yang is_verif=true dan rekening 4.1.*
            $realisasi = (float) TransaksiPendapatan::where('is_verif', true)
                ->whereHas('target_pendapatan', function ($q) use ($skpd, $tahun) {
                    $q->where('skpd_id', $skpd->id)
                      ->where('tahun', $tahun)
                      ->whereHas('rekening', fn($r) => $r->where('kode', 'like', '4.1%'));
                })
                ->sum('jumlah_setor');

            $persentase = $target > 0 ? round(($realisasi / $target) * 100, 2) : 0;

            return [
                'id'        => $skpd->id,
                'nama_skpd' => $skpd->nama_skpd,
                'target'    => $target,
                'realisasi' => $realisasi,
                'sisa'      => max($target - $realisasi, 0),
                'persentase'=> $persentase,
            ];
        })->filter(fn($s) => $s['target'] > 0 || $s['realisasi'] > 0)->values();
    }

    public function getPendapatanDetailBySkpd(int $skpdId, ?int $tahun = null): array
    {
        $tahun = $tahun ?? date('Y');

        $targetTotal = (float) TargetPendapatan::where('skpd_id', $skpdId)
            ->where('tahun', $tahun)
            ->sum('target_anggaran');

        $realisasiTotal = (float) TransaksiPendapatan::where('is_verif', true)->whereHas('target_pendapatan', function ($query) use ($skpdId, $tahun) {
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

        return TargetPendapatan::with([
                'rekening',
                'transaksi_pendapatan' => fn($q) => $q->where('is_verif', true)
            ])
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
        $realisasiTotal = (float) \App\Models\RealisasiKegiatan::where('is_verified', true)->sum('realisasi_keuangan');

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
            $renjas = \App\Models\RenjaSkpd::with([
                    'realisasi_kegiatans' => fn($q) => $q->where('is_verified', true)
                ])
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

                // Realisasi fisik per renja = RATA-RATA dari semua triwulan yang terverifikasi (bukan sum)
                $countTw = $renja->realisasi_kegiatans->count();
                $sumRealisasiFisik += $countTw > 0
                    ? round($renja->realisasi_kegiatans->sum('realisasi_fisik') / $countTw, 2)
                    : 0;
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

        $realisasiTotal = (float) \App\Models\RealisasiKegiatan::where('is_verified', true)
            ->whereHas('renja_skpd', function ($query) use ($skpdId, $tahun) {
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

        return \App\Models\RenjaSkpd::with([
                'subKegiatan',
                'realisasi_kegiatans' => fn($q) => $q->where('is_verified', true)
            ])
            ->where('skpd_id', $skpdId)
            ->where('tahun', $tahun)
            ->get()
            ->map(function ($renja) use ($skpdId) {
                $realisasiKeuangan = (float) $renja->realisasi_kegiatans->sum('realisasi_keuangan');
                $target = (float) $renja->target_keuangan;
                $persentase = $target > 0 ? round(($realisasiKeuangan / $target) * 100, 2) : 0;
                
                // Realisasi fisik = rata-rata dari semua triwulan
                $countTw = $renja->realisasi_kegiatans->count();
                $realisasiFisik = $countTw > 0
                    ? round($renja->realisasi_kegiatans->sum('realisasi_fisik') / $countTw, 2)
                    : 0.0;

                return [
                    'kode_rekening' => Skpd::formatDisplayKode($renja->subKegiatan->kode_lengkap ?? '-', $skpdId),
                    'nama_rekening' => Skpd::formatDisplayNomenklatur($renja->subKegiatan->nomenklatur ?? '-', $skpdId),
                    'target' => $target,
                    'realisasi' => $realisasiKeuangan,
                    'selisih' => $target - $realisasiKeuangan,
                    'persentase' => $persentase,
                    'target_fisik' => (float) $renja->target_fisik,
                    'realisasi_fisik' => $realisasiFisik,
                    'persentase_fisik' => $renja->target_fisik > 0
                        ? round(($realisasiFisik / $renja->target_fisik) * 100, 2)
                        : 0,
                ];
            });
    }

    public function getRenjaTableDataBySkpd(int $skpdId, ?int $tahun = null): array
    {
        $tahun = $tahun ?? date('Y');

        $renjaData = \App\Models\RenjaSkpd::with([
            'subKegiatan.kegiatan.program',
            'realisasi_kegiatans' => fn($q) => $q->where('is_verified', true)
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
            $countTw = $renja->realisasi_kegiatans->count();
            $realisasiFisik = $countTw > 0
                ? round($renja->realisasi_kegiatans->sum('realisasi_fisik') / $countTw, 2)
                : 0.0;

            $kodeProg = Skpd::formatDisplayKode($program->kode_lengkap ?? '', $skpdId);
            $namaProg = Skpd::formatDisplayNomenklatur($program->nomenklatur ?? '', $skpdId);
            $namaProgram = ($kodeProg !== '' ? $kodeProg . ' - ' : '') . $namaProg;

            $kodeKeg = Skpd::formatDisplayKode($kegiatan->kode_lengkap ?? '', $skpdId);
            $namaKeg = Skpd::formatDisplayNomenklatur($kegiatan->nomenklatur ?? '', $skpdId);
            $namaKegiatan = ($kodeKeg !== '' ? $kodeKeg . ' - ' : '') . $namaKeg;

            $kodeSub = Skpd::formatDisplayKode($subKegiatan->kode_lengkap ?? '', $skpdId);
            $namaSub = Skpd::formatDisplayNomenklatur($subKegiatan->nomenklatur ?? '', $skpdId);
            $namaSubKegiatan = ($kodeSub !== '' ? $kodeSub . ' - ' : '') . $namaSub;

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
        $tree = $this->getPendapatanTree($tahun);
        $treeByKode = collect($tree)->keyBy('kode');

        $categories = [
            'pajak' => ['kode' => '4.1.01', 'nama' => 'Pajak Daerah'],
            'retribusi' => ['kode' => '4.1.02', 'nama' => 'Retribusi Daerah'],
            'kekayaan' => ['kode' => '4.1.03', 'nama' => 'Hasil Pengelolaan Kekayaan'],
            'lain_lain' => ['kode' => '4.1.04', 'nama' => 'Lain-lain PAD Sah'],
        ];

        $result = [];

        foreach ($categories as $key => $cat) {
            $node = $treeByKode->get($cat['kode']);
            $targetTotal = (float) ($node['target'] ?? 0);
            $realisasiTotal = (float) ($node['realisasi'] ?? 0);
            $persentase = $targetTotal > 0 ? round(($realisasiTotal / $targetTotal) * 100, 2) : 0;

            $result[$key] = [
                'nama' => $cat['nama'],
                'target' => $targetTotal,
                'realisasi' => $realisasiTotal,
                'persentase' => $persentase,
            ];
        }

        return $result;
    }

    public function getPendapatanSummaryByLevel2(?int $tahun = null): array
    {
        $tahun = $tahun ?? date('Y');
        $tree  = $this->getPendapatanTree($tahun);

        // Ambil hanya node level 2 dari tree
        $level2Nodes = collect($tree)->filter(fn($row) => $row['level'] === 2)->values();

        return $level2Nodes->map(function ($node) {
            return [
                'kode'       => $node['kode'],
                'nama'       => $node['uraian'],
                'target'     => (float) $node['target'],
                'realisasi'  => (float) $node['realisasi'],
                'persentase' => (float) $node['persentase'],
            ];
        })->toArray();
    }

    public function getDetailRekeningByJenis(string $kodePrefix, ?int $tahun = null): array
    {
        $tahun = $tahun ?? date('Y');
        $tree = $this->getPendapatanTree($tahun);

        $list = [];
        foreach ($tree as $item) {
            // Tampilkan rekening level 4 yang berada di bawah kategori kodePrefix (contoh: 4.1.01)
            if ($item['level'] === 4 && str_starts_with($item['kode'], $kodePrefix . '.')) {
                $list[] = [
                    'kode' => $item['kode'],
                    'uraian' => $item['uraian'],
                    'target' => $item['target'],
                    'realisasi' => $item['realisasi'],
                    'persentase' => $item['persentase'],
                ];
            }
        }

        return $list;
    }

    public function getPendapatanTree(?int $tahun = null): array
    {
        $tahun = $tahun ?? date('Y');

        // 1. Ambil semua target & realisasi per rekening (hanya transaksi yang sudah diverifikasi: is_verif = true)
        $targets = \App\Models\TargetPendapatan::with(['transaksi_pendapatan' => fn($q) => $q->where('is_verif', true)])
            ->where('tahun', $tahun)
            ->get();

        $targetMap = [];
        $realisasiMap = [];
        $recordsMap = [];
        foreach ($targets as $target) {
            $rekId = $target->rekening_id;
            $targetMap[$rekId] = ($targetMap[$rekId] ?? 0) + (float) $target->target_anggaran;
            $realisasiMap[$rekId] = ($realisasiMap[$rekId] ?? 0) + (float) $target->transaksi_pendapatan->sum('jumlah_setor');
            $recordsMap[$rekId] = true;
        }

        // 2. Ambil semua Rekening Pendapatan (kode diawali '4')
        $rekenings = \App\Models\Rekening::where(function ($q) {
                $q->where('kode', 'like', '4%')
                  ->orWhere('kode', 'like', ' 4%');
            })
            ->get();

        // 3. Bangun struktur node
        $nodeMap = [];
        $childrenMap = [];
        $nodeByKode = [];

        foreach ($rekenings as $rek) {
            $cleanKode = trim($rek->kode);
            $nodeMap[$rek->id] = [
                'id' => $rek->id,
                'parent_id' => $rek->parent_id,
                'kode' => $cleanKode,
                'uraian' => $rek->uraian,
                'level' => (int) $rek->level,
                'direct_target' => $targetMap[$rek->id] ?? 0,
                'direct_realisasi' => $realisasiMap[$rek->id] ?? 0,
                'has_direct_record' => isset($recordsMap[$rek->id]),
                'target' => 0,
                'realisasi' => 0,
                'has_descendant_records' => false,
            ];
            $nodeByKode[$cleanKode] = $rek->id;
        }

        // Pastikan parent_id terisi jika level > 1
        foreach ($nodeMap as $id => $node) {
            if (!$node['parent_id'] && $node['level'] > 1) {
                $segments = explode('.', $node['kode']);
                if (count($segments) > 1) {
                    array_pop($segments);
                    $parentKode = implode('.', $segments);
                    if (isset($nodeByKode[$parentKode])) {
                        $nodeMap[$id]['parent_id'] = $nodeByKode[$parentKode];
                    }
                }
            }
        }

        // Bangun relasi anak (children)
        foreach ($nodeMap as $id => $node) {
            $pId = $node['parent_id'];
            if ($pId && isset($nodeMap[$pId])) {
                $childrenMap[$pId][] = $id;
            }
        }

        // 4. Hitung Bottom-up dari level terbesar (Level 6) ke level 1
        $sortedIds = array_keys($nodeMap);
        usort($sortedIds, function ($a, $b) use ($nodeMap) {
            return $nodeMap[$b]['level'] <=> $nodeMap[$a]['level'];
        });

        foreach ($sortedIds as $id) {
            $hasChildren = !empty($childrenMap[$id]);

            if (!$hasChildren) {
                // Leaf node
                $nodeMap[$id]['target'] = $nodeMap[$id]['direct_target'];
                $nodeMap[$id]['realisasi'] = $nodeMap[$id]['direct_realisasi'];
                $nodeMap[$id]['has_descendant_records'] = $nodeMap[$id]['has_direct_record'];
            } else {
                $childrenIds = $childrenMap[$id];
                $sumChildTarget = 0;
                $sumChildRealisasi = 0;
                $childrenHaveData = false;

                foreach ($childrenIds as $childId) {
                    $sumChildTarget += $nodeMap[$childId]['target'];
                    $sumChildRealisasi += $nodeMap[$childId]['realisasi'];
                    if ($nodeMap[$childId]['has_descendant_records'] || $nodeMap[$childId]['target'] > 0 || $nodeMap[$childId]['realisasi'] > 0) {
                        $childrenHaveData = true;
                    }
                }

                // Jika ada anak (level 5/6) yang memiliki data, rollup dari anak
                // Untuk level 1, 2, 3: selalu rollup dari anak
                if ($nodeMap[$id]['level'] <= 3 || $childrenHaveData) {
                    $nodeMap[$id]['target'] = $sumChildTarget;
                    $nodeMap[$id]['realisasi'] = $sumChildRealisasi;
                    $nodeMap[$id]['has_descendant_records'] = $childrenHaveData || $nodeMap[$id]['has_direct_record'];
                } else {
                    $nodeMap[$id]['target'] = $nodeMap[$id]['direct_target'];
                    $nodeMap[$id]['realisasi'] = $nodeMap[$id]['direct_realisasi'];
                    $nodeMap[$id]['has_descendant_records'] = $nodeMap[$id]['has_direct_record'];
                }
            }
        }

        // 5. Filter: Hanya tampilkan Level 1 sampai Level 4 untuk tampilan publik
        $result = [];
        foreach ($nodeMap as $node) {
            if ($node['level'] <= 4) {
                $target = (float) $node['target'];
                $realisasi = (float) $node['realisasi'];
                $persentase = $target > 0 ? round(($realisasi / $target) * 100, 2) : 0;

                $result[] = [
                    'kode' => $node['kode'],
                    'uraian' => $node['uraian'],
                    'level' => $node['level'],
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'selisih' => max($target - $realisasi, 0),
                    'persentase' => $persentase,
                ];
            }
        }

        // 6. Urutkan secara hierarkis berdasarkan kode (Natural Sort: 4, 4.1, 4.1.01, ...)
        usort($result, function ($a, $b) {
            return strnatcmp($a['kode'], $b['kode']);
        });

        return $result;
    }

    public function getLastUpdatePendapatan(?int $tahun = null): ?string
    {
        $tahun = $tahun ?? date('Y');

        $targetUpdate = TargetPendapatan::where('tahun', $tahun)->max('updated_at');
        $transaksiUpdate = TransaksiPendapatan::where('is_verif', true)->whereHas('target_pendapatan', function ($q) use ($tahun) {
            $q->where('tahun', $tahun);
        })->max('updated_at');

        $latest = collect([$targetUpdate, $transaksiUpdate])->filter()->max();

        if (!$latest) {
            $targetCreated = TargetPendapatan::where('tahun', $tahun)->max('created_at');
            $transaksiCreated = TransaksiPendapatan::where('is_verif', true)->whereHas('target_pendapatan', function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })->max('created_at');
            $latest = collect([$targetCreated, $transaksiCreated])->filter()->max();
        }

        if (!$latest) {
            $transaksiTanggal = TransaksiPendapatan::where('is_verif', true)->whereHas('target_pendapatan', function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })->max('tanggal_setor');
            if ($transaksiTanggal) {
                $latest = $transaksiTanggal . ' 00:00:00';
            }
        }

        return $latest ? \Carbon\Carbon::parse($latest)->format('Y-m-d H:i:s') : null;
    }

    public function getAvailablePendapatanYears(): array
    {
        $targetYears = TargetPendapatan::select('tahun')->distinct()->pluck('tahun')->map(fn($y) => (int)$y)->toArray();
        $currentYear = (int) date('Y');
        $rangeYears = range($currentYear - 2, $currentYear + 4);

        $years = array_unique(array_merge($targetYears, $rangeYears));
        sort($years);

        return $years;
    }

    public function getIkuData(array $years = [2025, 2026, 2027, 2028, 2029, 2030])
    {
        $kategoris = \App\Models\KategoriIndikator::where('nama_kategori', 'IKU')->with(['indikators.satuan', 'indikators.skpd', 'indikators.target', 'indikators.realisasi'])->get();

        return $kategoris->map(function ($kategori) use ($years) {
            $indikators = $kategori->indikators->map(function ($indikator) use ($years) {
                $dataYears = [];
                foreach ($years as $year) {
                    $target = $indikator->target->where('tahun', $year)->first();
                    $realisasi = $indikator->realisasi
                        ->where('tahun', $year)
                        ->where('status', \App\Enums\StatusRealisasi::APPROVED)
                        ->first();
                    
                    $dataYears[$year] = [
                        'target' => $target ? $target->nilai_target : null,
                        'realisasi' => ($realisasi && $realisasi->nilai_realisasi !== '-') ? $realisasi->nilai_realisasi : null,
                    ];
                }

                return [
                    'id' => $indikator->id,
                    'nama_indikator' => $indikator->nama_indikator,
                    'satuan' => $indikator->satuan ? $indikator->satuan->nama_satuan : '-',
                    'skpd' => $indikator->skpd ? $indikator->skpd->nama_skpd : '-',
                    'skpd_id' => $indikator->skpd_id,
                    'years' => $dataYears,
                ];
            });

            return [
                'kategori_id' => $kategori->id,
                'nama_kategori' => $kategori->nama_kategori,
                'indikators' => $indikators,
            ];
        });
    }

    public function getKinerjaProgramData(array $years = [2021, 2022, 2023, 2024, 2025, 2026])
    {
        $urusans = \App\Models\ProgramUrusan::with([
            'bidang.program.indikators.satuan',
            'bidang.program.indikators.skpd',
            'bidang.program.indikators.target',
            'bidang.program.indikators.realisasi'
        ])->get();

        return $urusans->map(function ($urusan) use ($years) {
            $bidangs = $urusan->bidang->map(function ($bidang) use ($years) {
                $programs = $bidang->program->map(function ($program) use ($years) {
                    $indikators = $program->indikators->map(function ($indikator) use ($years) {
                        $dataYears = [];
                        foreach ($years as $year) {
                            $target = $indikator->target->where('tahun', $year)->first();
                            $realisasi = $indikator->realisasi->where('tahun', $year)->first();
                            
                            $dataYears[$year] = [
                                'target' => $target ? $target->nilai_target : null,
                                'realisasi' => $realisasi ? $realisasi->nilai_realisasi : null,
                            ];
                        }

                        return [
                            'id' => $indikator->id,
                            'nama_indikator' => $indikator->nama_indikator,
                            'satuan' => $indikator->satuan ? $indikator->satuan->nama_satuan : '-',
                            'kondisi_awal' => $indikator->kondisi_awal,
                            'kondisi_akhir' => $indikator->kondisi_akhir,
                            'perangkat_daerah' => $indikator->skpd ? $indikator->skpd->nama_skpd : '-',
                            'years' => $dataYears,
                        ];
                    });

                    return [
                        'id' => $program->id,
                        'nama_program' => $program->nomenklatur,
                        'indikators' => $indikators,
                    ];
                });

                return [
                    'id' => $bidang->id,
                    'nama_bidang' => $bidang->nomenklatur,
                    'programs' => $programs,
                ];
            });

            return [
                'id' => $urusan->id,
                'nama_urusan' => $urusan->nomenklatur,
                'bidangs' => $bidangs,
            ];
        });
    }

    public function getKinerjaPenyelenggaraanData(array $years): \Illuminate\Support\Collection
    {
        $kategoriPenyelenggaraan = \App\Models\KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');

        $parents = \App\Models\MasterIndikator::with([
            'satuan',
            'skpd',
            'target',
            'realisasi',
            'childIndikators.satuan',
            'childIndikators.skpd',
            'childIndikators.target',
            'childIndikators.realisasi',
        ])
        ->where('kategori_id', $kategoriPenyelenggaraan)
        ->whereNull('parent_id')
        ->orderBy('id')
        ->get();

        $baselineYear = $years[0] ?? 2024;
        $remainingYears = array_slice($years, 1);

        return $parents->map(function ($parent) use ($baselineYear, $remainingYears) {
            // Hanya ambil indikator yang memiliki penanggung jawab (SKPD)
            $children = $parent->childIndikators->filter(function ($child) {
                return !empty($child->skpd_id) && $child->skpd !== null;
            });

            if ($children->isEmpty() && $parent->is_measurable && !empty($parent->skpd_id) && $parent->skpd !== null) {
                $indikators = collect([$this->formatIndikatorPenyelenggaraanRow($parent, $baselineYear, $remainingYears)]);
            } else {
                $indikators = $children->map(function ($child) use ($baselineYear, $remainingYears) {
                    return $this->formatIndikatorPenyelenggaraanRow($child, $baselineYear, $remainingYears);
                })->values();
            }

            return [
                'id' => $parent->id,
                'nama_aspek' => $parent->nama_indikator,
                'indikators' => $indikators,
            ];
        })->filter(function ($aspect) {
            return $aspect['indikators']->isNotEmpty();
        })->values();
    }

    private function formatIndikatorPenyelenggaraanRow(\App\Models\MasterIndikator $indikator, int $baselineYear, array $remainingYears): array
    {
        $baselineRealisasi = $indikator->realisasi->where('tahun', $baselineYear)->first();
        $baseline = null;

        if ($baselineRealisasi && $baselineRealisasi->nilai_realisasi !== '-' && $baselineRealisasi->nilai_realisasi !== '' && $baselineRealisasi->nilai_realisasi !== null) {
            $baseline = $baselineRealisasi->nilai_realisasi;
        } elseif (!empty($indikator->kondisi_awal) && $indikator->kondisi_awal !== '-') {
            $baseline = $indikator->kondisi_awal;
        } else {
            $baselineTarget = $indikator->target->where('tahun', $baselineYear)->first();
            if ($baselineTarget && $baselineTarget->nilai_target !== '-' && $baselineTarget->nilai_target !== '') {
                $baseline = $baselineTarget->nilai_target;
            }
        }

        $dataYears = [];
        foreach ($remainingYears as $year) {
            $target = $indikator->target->where('tahun', $year)->first();
            $realisasi = $indikator->realisasi->where('tahun', $year)->first();

            $dataYears[$year] = [
                'target' => $target ? $target->nilai_target : null,
                'realisasi' => ($realisasi && $realisasi->nilai_realisasi !== '-') ? $realisasi->nilai_realisasi : null,
            ];
        }

        return [
            'id' => $indikator->id,
            'nama_indikator' => $indikator->nama_indikator,
            'satuan' => $indikator->satuan ? $indikator->satuan->nama_satuan : '-',
            'baseline' => $baseline,
            'years' => $dataYears,
            'skpd' => $indikator->skpd ? $indikator->skpd->nama_skpd : '-',
        ];
    }
}
