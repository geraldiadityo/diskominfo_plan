# RENCANA IMPLEMENTASI (PLAN 2 - REVISI PRESISI): Laporan Realisasi Fisik & Keuangan Triwulanan Renja SKPD (Identik Gambar Referensi + Indikator Bukti + Pilih Triwulan + Download & Cetak)

Dokumen ini adalah **panduan instruksi teknis lengkap** bagi AI Agent / Software Engineer untuk mengimplementasikan halaman **Laporan Realisasi Fisik dan Keuangan Triwulan** di Admin Panel Filament (`App\Filament\Pages\LaporanRealisasi`) agar **100% identik secara visual dan struktural** dengan lembar evaluasi Excel referensi (18 kolom), menampilkan daftar **Indikator Bukti Fisik**, mendukung pemilihan **Triwulan (I, II, III, IV)**, terkunci otomatis sesuai **SKPD yang login**, serta dilengkapi tombol **Download Excel (`.xls`)** dan **Cetak / PDF**.

---

## 1. Ringkasan Kebutuhan Utama

1. **Tampilan Tabel 100% Mirip Gambar Referensi (18 Kolom Spreadsheet)**:
   - Kop laporan 2 baris rata tengah huruf kapital tebal:
     - Baris 1: `LAPORAN REALISASI FISIK DAN KEUANGAN TRIWULAN [I / II / III / IV]`
     - Baris 2: `[NAMA SKPD]`
   - Header tabel 5 tingkat (termasuk baris nomor kolom `1` s/d `18`) dengan border hitam tegas (`1px solid #000`) dan latar putih bersih seperti lembar kerja Excel.
   - Warna baris berjenjang:
     - **Baris Program**: Hijau muda Excel (`#d9ead3`), teks **Bold Kapital**.
     - **Baris Kegiatan**: Abu-abu Excel (`#bfbfbf`), teks **Bold**.
     - **Baris Sub Kegiatan & Indikator Bukti**: Putih (`#ffffff`), dengan penggabungan sel vertikal (`rowspan`) pada kolom identitas Sub Kegiatan, Tingkat Capaian Fisik, Keuangan, Permasalahan, dan Upaya.
2. **Menampilkan Indikator Bukti Fisik (`BuktiFisikRealisasi`) di Laporan Triwulan**:
   - Setiap Sub Kegiatan memuat baris-baris **Indikator Fisik & Bukti** yang diinput pada triwulan tersebut (Kolom 5: Nama Indikator + tautan file bukti PDF di admin panel, Kolom 6: Target volume/dokumen, Kolom 7: Realisasi, Kolom 8: Capaian %).
   - Jika pada triwulan terpilih belum diinput rincian indikator bukti fisik, sistem melakukan *smart fallback* dengan mengambil master indikator bukti dari triwulan sebelumnya pada sub kegiatan yang sama (dengan capaian 0%), atau menampilkan indikator sub kegiatan (`-`) agar tabel tidak pernah kosong/rusak.
3. **Pemilihan Triwulan Interaktif (Dropdown Atas & Tab Sheet Bawah ala Excel)**:
   - Pengguna dapat memilih **Triwulan I, II, III, atau IV** baik melalui dropdown filter di bagian atas maupun melalui **Tab Sheet di bagian bawah tabel** (`Evaluasi Triwulan I`, `Evaluasi Triwulan II`, `Evaluasi Triwulan III`, `Evaluasi Triwulan IV`) persis seperti tampilan tab lembar kerja Excel pada gambar.
4. **Filter Berdasarkan SKPD yang Login**:
   - Role `SKPD`: Hanya dapat melihat dan mengunduh laporan milik SKPD-nya sendiri (`Auth::user()->skpd_id`).
   - Role `ADMIN` / `VERIFIKATOR`: Dapat memilih SKPD mana saja melalui dropdown filter.
5. **Tombol Download Excel & Tombol Cetak / PDF**:
   - **Download Excel (`.xls`)**: Mengunduh file spreadsheet dengan struktur *merge cells* (`colspan` & `rowspan`), warna baris, border hitam, dan format angka yang sama persis dengan gambar.
   - **Cetak / Simpan PDF**: Membuka dialog cetak *landscape* A4/F4 yang menyembunyikan elemen navigasi admin.
6. **Halaman Luar (Public Frontend) Dibiarkan Tetap Seperti Semula**:
   - Tidak mengubah kode pada route publik (`/renja`, `/renja/{skpdId}`, `DetailRenja`, dll.).

---

## 2. Struktur Header & Pemetaan 18 Kolom Secara Presisi

### 2.1. Struktur `<thead>` (5 Baris Header)
- **Baris Header 1**:
  - Kolom 1: `Program, Kegiatan dan Sub Kegiatan` (`rowspan="4"`)
  - Kolom 2: `Indikator Kinerja` (`rowspan="4"`)
  - Kolom 3: `Satuan` (`rowspan="4"`)
  - Kolom 4: `Pagu (Rp)` (`rowspan="4"`)
  - Kolom 5–18: `Triwulan {1/2/3/4}` (`colspan="14"`)
- **Baris Header 2**:
  - Kolom 5–11: `Kinerja Fisik` (`colspan="7"`)
  - Kolom 12–16: `Keuangan` (`colspan="5"`)
  - Kolom 17: `Permasalahan` (`rowspan="3"`)
  - Kolom 18: `Upaya mengatasi permasalahan` (`rowspan="3"`)
- **Baris Header 3**:
  - Di bawah `Kinerja Fisik`:
    - Kolom 5: `Indikator` (`rowspan="2"`)
    - Kolom 6: `Target` (`rowspan="2"`)
    - Kolom 7–9: `Realisasi` (`colspan="3"`)
    - Kolom 10–11: `Akumulasi Tahunan` (`colspan="2"`)
  - Di bawah `Keuangan`:
    - Kolom 12: `Target (Rp)` (`rowspan="2"`)
    - Kolom 13: `Realisasi (Rp)` (`rowspan="2"`)
    - Kolom 14: `Tingkat Capaian (%)` (`rowspan="2"`)
    - Kolom 15–16: `Akumulasi Tahunan` (`colspan="2"`)
- **Baris Header 4**:
  - Kolom 7: `Realisasi`
  - Kolom 8: `Capaian (%)`
  - Kolom 9: `Tingkat Capaian (%)`
  - Kolom 10: `Target (%)`
  - Kolom 11: `Realisasi (%)`
  - Kolom 15: `Target (%)`
  - Kolom 16: `Realisasi (%)`
- **Baris Header 5 (Nomor Kolom)**:
  - `1` | `2` | `3` | `4` | `5` | `6` | `7` | `8` | `9` | `10` | `11` | `12` | `13` | `14` | `15` | `16` | `17` | `18`

---

### 2.2. Tabel Pemetaan Data & Rumus Per Kolom

| Kolom | Label Header | Sumber Data Model | Aturan Tampilan & Perhitungan |
|:---:|---|---|---|
| **1** | Program, Kegiatan dan Sub Kegiatan | `ProgramProgram->nomenklatur`, `ProgramKegiatan->nomenklatur`, `ProgramSubKegiatan->nomenklatur` | - Program: Huruf kapital tebal<br>- Kegiatan: Tebal<br>- Sub Kegiatan: Di-merge (`rowspan`) sejumlah baris indikator bukti fisik |
| **2** | Indikator Kinerja | `ProgramSubKegiatan->kinerja` / `indikator` | Ditampilkan pada level Sub Kegiatan (`rowspan` sejumlah indikator bukti) |
| **3** | Satuan | `RenjaSkpd->target_kinerja` + `ProgramSubKegiatan->satuan` | Menampilkan volume & satuan (cth: `2 Dokumen`, `48 Orang`, `1 Paket`) |
| **4** | Pagu (Rp) | `RenjaSkpd->pagu_anggaran` | Format ribuan 2 desimal (cth: `21,344,064.00`). Di-sum ke Kegiatan & Program |
| **5** | Indikator (Fisik) | `BuktiFisikRealisasi->nama_indikator` | Menampilkan setiap rincian indikator fisik pada triwulan terpilih beserta badge/link file bukti PDF jika sudah diunggah |
| **6** | Target (Fisik) | `BuktiFisikRealisasi->target` | Target per indikator bukti (cth: `1 Dokumen`, `1 Laporan`) |
| **7** | Realisasi (Fisik) | `BuktiFisikRealisasi->realisasi` | Nilai realisasi bukti fisik (cth: `100%` atau hasil realisasi yang diinput) |
| **8** | Capaian (%) | `BuktiFisikRealisasi->status_verifikasi` / `realisasi` | `100%` jika disetujui (atau sesuai persentase realisasi bukti), `0%` jika belum |
| **9** | Tingkat Capaian (%) Fisik | `RealisasiKegiatan->realisasi_fisik` & `target_fisik` | $\frac{\text{Realisasi Fisik TW}}{\text{Target Fisik TW}} \times 100\%$ (maks 100%, atau 100% jika semua bukti disetujui). Di-merge (`rowspan`) per Sub Kegiatan |
| **10** | Akumulasi Tahunan Target (%) Fisik | `RealisasiKegiatan` (TW 1 s.d. TW Terpilih) | Akumulasi `target_fisik` s.d. triwulan terpilih (cth: `50%` atau `100%`) |
| **11** | Akumulasi Tahunan Realisasi (%) Fisik | `RealisasiKegiatan` (TW 1 s.d. TW Terpilih) | Akumulasi `realisasi_fisik` s.d. triwulan terpilih (cth: `50%` atau `100%`) |
| **12** | Target (Rp) Keuangan | `RealisasiKegiatan->target_keuangan` | Target keuangan triwulan terpilih (`rowspan` per Sub Kegiatan, di-sum ke Kegiatan & Program) |
| **13** | Realisasi (Rp) Keuangan | `RealisasiKegiatan->realisasi_keuangan` | Realisasi keuangan triwulan terpilih (`rowspan` per Sub Kegiatan, di-sum ke Kegiatan & Program) |
| **14** | Tingkat Capaian (%) Keuangan | Kalkulasi Kolom 13 / Kolom 12 | $\frac{\text{Realisasi Keuangan TW}}{\text{Target Keuangan TW}} \times 100\%$ (cth: `100.77%`, `88.85%`) |
| **15** | Akumulasi Tahunan Target (%) Keuangan | Kalkulasi Kumulatif s.d. TW Terpilih terhadap Pagu | $\frac{\sum_{t=1}^{\text{TW}} \text{Target Keuangan}_t}{\text{Pagu}} \times 100\%$ (cth: `40%`, `45%`, `50%`) |
| **16** | Akumulasi Tahunan Realisasi (%) Keuangan | Kalkulasi Rasio Realisasi Kumulatif | Rasio desimal capaian keuangan tahunan $\frac{\sum_{t=1}^{\text{TW}} \text{Realisasi Keuangan}_t}{\text{Pagu}}$ (cth: `0.52`, `0.38`, `0.44`) |
| **17** | Permasalahan | `RealisasiKegiatan->alasan` | Teks permasalahan pada triwulan terpilih (`rowspan` per Sub Kegiatan) |
| **18** | Upaya mengatasi permasalahan | `RealisasiKegiatan->solusi` | Teks solusi/upaya pada triwulan terpilih (`rowspan` per Sub Kegiatan) |

---

## 3. Langkah-Langkah Implementasi Kode

### Langkah 1: Perbarui Kelas Page `app/Filament/Pages/LaporanRealisasi.php`

Pastikan class `LaporanRealisasi` memuat relasi `realisasi_kegiatans.bukti_fisik`, menyediakan method `selectTriwulan(int $tw)` untuk pergantian triwulan instan, mendukung pengambilan indikator bukti fisik beserta status & file buktinya, serta menyediakan aksi `downloadExcel()`:

```php
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

    public function selectTriwulan(int $triwulan): void
    {
        if (in_array($triwulan, [1, 2, 3, 4], true)) {
            $this->selectedTriwulan = $triwulan;
        }
    }

    public function getSkpdListProperty()
    {
        if (Auth::user()?->role === 'SKPD') {
            return Skpd::where('id', Auth::user()->skpd_id)->pluck('nama_skpd', 'id');
        }

        return Skpd::orderBy('nama_skpd')->pluck('nama_skpd', 'id');
    }

    public function getActiveSkpdProperty(): ?Skpd
    {
        if (Auth::user()?->role === 'SKPD') {
            return Skpd::find(Auth::user()->skpd_id);
        }

        return Skpd::find($this->selectedSkpdId);
    }

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

            // Realisasi pada Triwulan yang dipilih
            $rkTw = $renja->realisasi_kegiatans->firstWhere('triwulan', $this->selectedTriwulan);

            // Realisasi Kumulatif dari Triwulan 1 s.d. Triwulan yang dipilih
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

            // Tingkat Capaian Keuangan Triwulan (%)
            $tingkatCapaianKeu = $targetKeuTw > 0 ? round(($realKeuTw / $targetKeuTw) * 100, 2) : 0;

            // Akumulasi Tahunan Keuangan
            $persenTargetKeuKum = $paguSub > 0 ? round(($targetKeuKum / $paguSub) * 100, 0) : 0;
            $rasioRealKeuKum = $paguSub > 0 ? round($realKeuKum / $paguSub, 2) : 0;

            // Format Satuan + Volume (cth: "2 Dokumen", "48 Orang", "1 Paket")
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
                $satuanDisplay = '-';
            }

            // Indikator Bukti Fisik HANYA pada Triwulan Terpilih ($rkTw)
            // Jika tidak ada di Indikator Fisik & Bukti triwulan ini, jangan masukkan indikator dari triwulan lain/master.
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

            // Tingkat Capaian Fisik Sub Kegiatan
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
                'indikator_kinerja' => $sub->kinerja ?? $sub->indikator ?? '-',
                'satuan' => $satuanDisplay,
                'pagu' => $paguSub,
                'bukti_list' => $buktiList,
                'tingkat_capaian_fisik' => $tingkatCapaianFisik,
                'target_fisik_kum' => $targetFisikKum,
                'real_fisik_kum' => $realFisikKum,
                'target_keuangan_tw' => $targetKeuTw,
                'realisasi_keuangan_tw' => $realKeuTw,
                'tingkat_capaian_keu' => $tingkatCapaianKeu,
                'persen_target_keu_kum' => $persenTargetKeuKum,
                'rasio_real_keu_kum' => $rasioRealKeuKum,
                'alasan' => $rkTw?->alasan ?: '',
                'solusi' => $rkTw?->solusi ?: '',
            ];

            // Akumulasi ke Level Kegiatan
            $laporan[$progId]['kegiatans'][$kegId]['pagu'] += $paguSub;
            $laporan[$progId]['kegiatans'][$kegId]['target_keuangan_tw'] += $targetKeuTw;
            $laporan[$progId]['kegiatans'][$kegId]['realisasi_keuangan_tw'] += $realKeuTw;
            $laporan[$progId]['kegiatans'][$kegId]['target_keuangan_kum'] += $targetKeuKum;
            $laporan[$progId]['kegiatans'][$kegId]['realisasi_keuangan_kum'] += $realKeuKum;
            $laporan[$progId]['kegiatans'][$kegId]['tingkat_capaian_fisik_sum'] += $tingkatCapaianFisik;
            $laporan[$progId]['kegiatans'][$kegId]['target_fisik_kum_sum'] += $targetFisikKum;
            $laporan[$progId]['kegiatans'][$kegId]['realisasi_fisik_kum_sum'] += $realFisikKum;
            $laporan[$progId]['kegiatans'][$kegId]['sub_count']++;

            // Akumulasi ke Level Program
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
```

---

### Langkah 2: Perbarui View Blade `resources/views/filament/pages/laporan-realisasi.blade.php`

Gunakan styling dan tata letak sel yang persis menyerupai gambar tangkapan layar Excel, lengkap dengan pemilih Triwulan (dropdown & tab bawah) serta tombol Download Excel & Cetak:

```html
{{-- Lihat implementasi lengkap di resources/views/filament/pages/laporan-realisasi.blade.php --}}
```

### Langkah 3: Perbarui View Ekspor Excel `resources/views/filament/pages/laporan-realisasi-excel.blade.php`

Pastikan struktur `thead` 5 tingkat dan warna baris (`#d9ead3` untuk Program dan `#bfbfbf` untuk Kegiatan) serta format angka desimal sama persis saat diunduh menjadi `.xls`.
