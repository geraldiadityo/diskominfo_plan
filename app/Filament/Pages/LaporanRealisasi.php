<?php

namespace App\Filament\Pages;

use App\Models\RenjaSkpd;
use App\Models\Skpd;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class LaporanRealisasi extends Page
{
    protected string $view = 'filament.pages.laporan-realisasi';
    protected static ?string $title = 'Laporan Realisasi Fisik Dan Keuangan';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::PresentationChartBar;
    protected static string|UnitEnum|null $navigationGroup = 'Evaluasi Dan Pelaporan';
    protected static ?int $navigationSort = 2;
    protected static bool $shouldRegisterNavigation = false;

    public ?int $selectedSkpdId = null;
    public int $selectedTahun;
    public int $selectedTriwulan;

    public function mount(): void
    {
        $this->selectedTahun = (int) request()->query('tahun', date('Y'));

        $currentMonth = (int) date('n');
        $defaultTw = match (true) {
            $currentMonth <= 3 => 1,
            $currentMonth <= 6 => 2,
            $currentMonth <= 9 => 3,
            default => 4,
        };
        $this->selectedTriwulan = (int) request()->query('triwulan', $defaultTw);

        if (Auth::user()?->role === 'SKPD') {
            $this->selectedSkpdId = Auth::user()->skpd_id;
        } else {
            $this->selectedSkpdId = (int) request()->query(
                'skpd_id',
                Auth::user()?->skpd_id ?? Skpd::query()->orderBy('nama_skpd')->value('id')
            );
        }
    }

    /**
     * Ganti triwulan secara instan melalui tab sheet atau dropdown.
     */
    public function selectTriwulan(int $triwulan): void
    {
        if (in_array($triwulan, [1, 2, 3, 4], true)) {
            $this->selectedTriwulan = $triwulan;
        }
    }

    /**
     * Daftar SKPD untuk filter dropdown.
     */
    public function getSkpdListProperty()
    {
        if (Auth::user()?->role === 'SKPD') {
            return Skpd::where('id', Auth::user()->skpd_id)->pluck('nama_skpd', 'id');
        }

        return Skpd::orderBy('nama_skpd')->pluck('nama_skpd', 'id');
    }

    /**
     * Ambil data SKPD yang sedang aktif dipilih.
     */
    public function getActiveSkpdProperty(): ?Skpd
    {
        if (Auth::user()?->role === 'SKPD') {
            return Skpd::find(Auth::user()->skpd_id);
        }

        return Skpd::find($this->selectedSkpdId);
    }

    /**
     * Susun data laporan berjenjang (Program -> Kegiatan -> Sub Kegiatan -> Indikator Bukti Fisik)
     * Hanya mengambil indikator bukti fisik yang ada pada Triwulan terpilih saja.
     */
    public function getLaporanData(): array
    {
        if (Auth::user()?->role === 'SKPD') {
            $this->selectedSkpdId = Auth::user()->skpd_id;
        }

        if (!$this->selectedSkpdId) {
            return [];
        }

        $renjaItems = RenjaSkpd::with([
            'subKegiatan.kegiatan.program',
            'realisasi_kegiatans' => function ($q) {
                $q->orderBy('triwulan')->with('bukti_fisik');
            }
        ])
        ->where('skpd_id', $this->selectedSkpdId)
        ->where('tahun', $this->selectedTahun)
        ->get();

        $laporan = [];

        foreach ($renjaItems as $renja) {
            $sub = $renja->subKegiatan;
            $keg = $sub?->kegiatan;
            $prog = $keg?->program;

            if (!$prog || !$keg || !$sub) {
                continue;
            }

            $progId = $prog->id;
            $kegId = $keg->id;
            $renjaId = $renja->id;

            if (!isset($laporan[$progId])) {
                $laporan[$progId] = [
                    'kode' => $prog->kode_lengkap ?? '',
                    'nama' => strtoupper($prog->nomenklatur ?? ''),
                    'pagu' => 0,
                    'target_keuangan_tw' => 0,
                    'realisasi_keuangan_tw' => 0,
                    'target_keuangan_kum' => 0,
                    'realisasi_keuangan_kum' => 0,
                    'tingkat_capaian_fisik_sum' => 0,
                    'target_fisik_kum_sum' => 0,
                    'realisasi_fisik_kum_sum' => 0,
                    'sub_count' => 0,
                    'kegiatans' => []
                ];
            }

            if (!isset($laporan[$progId]['kegiatans'][$kegId])) {
                $laporan[$progId]['kegiatans'][$kegId] = [
                    'kode' => $keg->kode_lengkap ?? '',
                    'nama' => $keg->nomenklatur ?? '',
                    'pagu' => 0,
                    'target_keuangan_tw' => 0,
                    'realisasi_keuangan_tw' => 0,
                    'target_keuangan_kum' => 0,
                    'realisasi_keuangan_kum' => 0,
                    'tingkat_capaian_fisik_sum' => 0,
                    'target_fisik_kum_sum' => 0,
                    'realisasi_fisik_kum_sum' => 0,
                    'sub_count' => 0,
                    'sub_kegiatans' => []
                ];
            }

            // Data Realisasi khusus pada Triwulan yang dipilih
            $rkTw = $renja->realisasi_kegiatans->firstWhere('triwulan', (int) $this->selectedTriwulan);

            // Data Kumulatif dari Triwulan 1 s.d. Triwulan Terpilih
            $rkKum = $renja->realisasi_kegiatans->filter(fn($r) => (int) $r->triwulan <= (int) $this->selectedTriwulan);

            $paguSub = (float) ($renja->pagu_anggaran ?? 0);
            $targetKeuTw = (float) ($rkTw?->target_keuangan ?? 0);
            $realKeuTw = (float) ($rkTw?->realisasi_keuangan ?? 0);

            $targetKeuKum = (float) $rkKum->sum('target_keuangan');
            $realKeuKum = (float) $rkKum->sum('realisasi_keuangan');

            $targetFisikTw = (float) ($rkTw?->target_fisik ?? 0);
            $realFisikTw = (float) ($rkTw?->realisasi_fisik ?? 0);

            $targetFisikKum = (float) $rkKum->sum('target_fisik');
            $realFisikKum = (float) $rkKum->sum('realisasi_fisik');

            // Hitung Rasio Keuangan
            $tingkatCapaianKeu = $targetKeuTw > 0 ? round(($realKeuTw / $targetKeuTw) * 100, 2) : 0;
            $persenTargetKeuKum = $paguSub > 0 ? round(($targetKeuKum / $paguSub) * 100, 0) : 0;
            $rasioRealKeuKum = $paguSub > 0 ? round($realKeuKum / $paguSub, 2) : 0;

            // Format Satuan + Volume
            $targetKinerja = trim((string) ($renja->target_kinerja ?? ''));
            $satuanMaster = trim((string) ($sub->satuan ?? ''));
            if ($targetKinerja !== '' && $satuanMaster !== '') {
                if (stripos($targetKinerja, $satuanMaster) !== false) {
                    $satuanDisplay = $targetKinerja;
                } else {
                    $satuanDisplay = $targetKinerja . ' ' . $satuanMaster;
                }
            } elseif ($targetKinerja !== '') {
                $satuanDisplay = $targetKinerja;
            } elseif ($satuanMaster !== '') {
                $satuanDisplay = $satuanMaster;
            } else {
                $satuanDisplay = '';
            }

            // Indikator Fisik & Bukti HANYA dari Triwulan yang dipilih ($rkTw)
            // Jika tidak ada di Indikator Fisik & Bukti pada triwulan ini, JANGAN masukkan indikator lain.
            $buktiList = [];
            $capaianIndikatorList = [];

            if ($rkTw && $rkTw->bukti_fisik->isNotEmpty()) {
                foreach ($rkTw->bukti_fisik as $bf) {
                    $isApproved = $bf->status_verifikasi === 'disetujui';
                    $capaianBf = $isApproved ? 100 : 0;
                    $capaianIndikatorList[] = $capaianBf;

                    $buktiList[] = [
                        'nama_indikator' => $bf->nama_indikator,
                        'target' => $bf->target ?? '',
                        'realisasi' => $bf->realisasi !== null && $bf->realisasi !== ''
                            ? (is_numeric($bf->realisasi) ? $bf->realisasi . '%' : $bf->realisasi)
                            : ($isApproved ? '100%' : '0%'),
                        'capaian' => $capaianBf . '%',
                        'target_akumulasi' => round($targetFisikKum, 0) . '%',
                        'realisasi_akumulasi' => round($realFisikKum, 0) . '%',
                        'status_verifikasi' => $bf->status_verifikasi,
                        'file_bukti' => $bf->file_bukti ? url('storage/' . $bf->file_bukti) : null,
                    ];
                }
            } else {
                // Kosongkan kolom indikator bukti fisik jika triwulan ini tidak memiliki data di bukti_fisik
                $buktiList[] = [
                    'nama_indikator' => '',
                    'target' => '',
                    'realisasi' => '',
                    'capaian' => '',
                    'target_akumulasi' => $targetFisikKum > 0 ? round($targetFisikKum, 0) . '%' : '',
                    'realisasi_akumulasi' => $realFisikKum > 0 ? round($realFisikKum, 0) . '%' : '',
                    'status_verifikasi' => null,
                    'file_bukti' => null,
                ];
            }

            // Tingkat Capaian Fisik Sub Kegiatan pada Triwulan Terpilih
            if ($targetFisikTw > 0) {
                $tingkatCapaianFisik = round(($realFisikTw / $targetFisikTw) * 100, 0);
            } elseif (count($capaianIndikatorList) > 0) {
                $tingkatCapaianFisik = round(array_sum($capaianIndikatorList) / count($capaianIndikatorList), 0);
            } else {
                $tingkatCapaianFisik = 0;
            }

            $laporan[$progId]['kegiatans'][$kegId]['sub_kegiatans'][$renjaId] = [
                'kode' => $sub->kode_lengkap ?? '',
                'nama' => $sub->nomenklatur ?? '',
                'indikator_kinerja' => $sub->kinerja ?? $sub->indikator ?? '',
                'satuan' => $satuanDisplay,
                'pagu' => $paguSub,
                'has_bukti' => $rkTw && $rkTw->bukti_fisik->isNotEmpty(),
                'bukti_list' => $buktiList,
                'tingkat_capaian_fisik' => $tingkatCapaianFisik,
                'target_fisik_kum' => round($targetFisikKum, 0),
                'real_fisik_kum' => round($realFisikKum, 0),
                'target_keuangan_tw' => $targetKeuTw,
                'realisasi_keuangan_tw' => $realKeuTw,
                'tingkat_capaian_keu' => $tingkatCapaianKeu,
                'persen_target_keu_kum' => $persenTargetKeuKum,
                'rasio_real_keu_kum' => $rasioRealKeuKum,
                'alasan' => $rkTw?->alasan ?: '',
                'solusi' => $rkTw?->solusi ?: '',
            ];

            // Akumulasi Kegiatan
            $laporan[$progId]['kegiatans'][$kegId]['pagu'] += $paguSub;
            $laporan[$progId]['kegiatans'][$kegId]['target_keuangan_tw'] += $targetKeuTw;
            $laporan[$progId]['kegiatans'][$kegId]['realisasi_keuangan_tw'] += $realKeuTw;
            $laporan[$progId]['kegiatans'][$kegId]['target_keuangan_kum'] += $targetKeuKum;
            $laporan[$progId]['kegiatans'][$kegId]['realisasi_keuangan_kum'] += $realKeuKum;
            $laporan[$progId]['kegiatans'][$kegId]['tingkat_capaian_fisik_sum'] += $tingkatCapaianFisik;
            $laporan[$progId]['kegiatans'][$kegId]['target_fisik_kum_sum'] += $targetFisikKum;
            $laporan[$progId]['kegiatans'][$kegId]['realisasi_fisik_kum_sum'] += $realFisikKum;
            $laporan[$progId]['kegiatans'][$kegId]['sub_count']++;

            // Akumulasi Program
            $laporan[$progId]['pagu'] += $paguSub;
            $laporan[$progId]['target_keuangan_tw'] += $targetKeuTw;
            $laporan[$progId]['realisasi_keuangan_tw'] += $realKeuTw;
            $laporan[$progId]['target_keuangan_kum'] += $targetKeuKum;
            $laporan[$progId]['realisasi_keuangan_kum'] += $realKeuKum;
            $laporan[$progId]['tingkat_capaian_fisik_sum'] += $tingkatCapaianFisik;
            $laporan[$progId]['target_fisik_kum_sum'] += $targetFisikKum;
            $laporan[$progId]['realisasi_fisik_kum_sum'] += $realFisikKum;
            $laporan[$progId]['sub_count']++;
        }

        return $laporan;
    }

    public function getTableData(): array
    {
        return $this->getLaporanData();
    }

    /**
     * Download Laporan dalam format Excel Spreadsheet (.xls).
     */
    public function downloadExcel(): StreamedResponse
    {
        $data = $this->getLaporanData();
        $skpd = $this->activeSkpd;
        $tahun = $this->selectedTahun;
        $triwulan = $this->selectedTriwulan;
        $triwulanRomawi = ['I', 'II', 'III', 'IV'][$triwulan - 1] ?? $triwulan;

        $fileName = sprintf(
            'Evaluasi_Triwulan_%s_%s_%s.xls',
            $triwulanRomawi,
            Str::slug($skpd?->nama_skpd ?? 'skpd'),
            $tahun
        );

        return response()->streamDownload(function () use ($data, $skpd, $tahun, $triwulan, $triwulanRomawi) {
            echo view('filament.pages.laporan-realisasi-excel', [
                'laporan' => $data,
                'skpd' => $skpd,
                'tahun' => $tahun,
                'triwulan' => $triwulan,
                'triwulanRomawi' => $triwulanRomawi,
            ])->render();
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
