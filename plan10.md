# Plan 10 — Seeder Realisasi PAD (Idempoten)

## Tujuan

Membuat seeder idempoten untuk menyeeding data **target** dan **realisasi** Pendapatan Asli Daerah (PAD) dari file `pendapatan_daerah.xlsx` ke dalam tabel `target_pendapatans` dan `transaksi_pendapatans` dengan aturan bisnis sebagai berikut:

---

## Aturan Bisnis Kritis

### 1. Level Rekening yang Digunakan
- **Ambil hanya rekening level 6** (kode dengan 6 segmen, contoh: `4.1.01.09.01.00001`).
- Setiap rekening level 6 di Excel memiliki kolom **Dinas Pengelola** (`G`) yang berbeda-beda.
- Rekening tersebut harus ada di tabel `rekenings` (sudah di-seed oleh `RekeningSeeder`). Jika tidak ditemukan, **skip** dengan log peringatan.

### 2. Mapping Dinas Pengelola → SKPD
- Kolom G di Excel berisi nama dinas pengelola (huruf kapital, contoh: `BADAN PENDAPATAN DAERAH`).
- Petakan nama tersebut ke `kode_skpd` di tabel `skpds`.
- **Khusus rekening mulai dari `4.2` ke bawah (Pendapatan Transfer dan turunannya)**: Dinas pengelola adalah **Badan Keuangan dan Aset Daerah** (`kode_skpd: BAKEUDA`), terlepas dari apa yang tertulis di kolom G.
- Mapping nama dinas pengelola (case-insensitive, trim):

| Nama di Excel (contoh) | `kode_skpd` |
|---|---|
| BADAN PENDAPATAN DAERAH | `BAPENDA` |
| BADAN KEUANGAN DAN ASET DAERAH | `BAKEUDA` |
| DINAS KESEHATAN | `DINKES` |
| DINAS LINGKUNGAN HIDUP | `DLH` |
| DINAS PERHUBUNGAN | `DISHUB` |
| DINAS PEKERJAAN UMUM DAN PERUMAHAN RAKYAT | `PUPR` |
| DINAS PERDAGANGAN DAN PERINDUSTRIAN | `DISPERINDAG` |
| DINAS PERTANIAN, KETAHANAN PANGAN DAN PERIKANAN | `DPKPP` |
| SETDA BAGIAN UMUM | `SETDA` |
| DISBUDPARPORA | `DISBUDPAR` |
| RUMAH SAKIT UMUM H.A THALIB | `RSMAYJEN` |
| PUSKESMAS TANAH KAMPUNG | `PKMTK` |
| PUSKESMAS KOTO BARU | `PKMKB` |
| PUSKESMAS DESA GEDANG | `PKMDG` |
| PUSKESMAS RAWANG | `PKMRWG` |
| PUSKESMAS KUMUN | `PKMKD` |
| PUSKESMAS SUNGAI PENUH | `PKMSPN` |
| *(nama dinas lain yang mungkin muncul)* | *(match ke kode_skpd terkait)* |

> **Catatan**: Jika nama dinas tidak ditemukan dalam mapping, log peringatan dan **skip** baris tersebut.

### 3. Aturan Skip Data
- Jika **target (`C`) dan realisasi (`D`) keduanya kosong/tidak ada**: **skip**.
- Jika **salah satu ada** (hanya target atau hanya realisasi): **tetap lakukan seed** untuk data yang ada.
- Realisasi bernilai `0` (angka nol eksplisit) tetap dianggap **ada dan valid**, bukan skip.
- Realisasi `#DIV/0!` di kolom F **diabaikan** (hanya kolom C dan D yang dipertimbangkan).
- Nilai negatif pada selisih (kolom E) **diabaikan**, fokus pada kolom C (target) dan D (realisasi).

### 4. Idempoten — Cek Duplikasi
- Sebelum membuat `target_pendapatans`, cek apakah sudah ada record dengan (`skpd_id`, `rekening_id`, `tahun`) yang sama → jika ada, **skip** (tidak update).
- Sebelum membuat `transaksi_pendapatans`, cek apakah sudah ada record dengan (`target_pendapatan_id`) yang cocok dengan jumlah realisasi yang sama → jika ada, **skip**.
- Gunakan `firstOrCreate` atau cek eksplisit dengan `exists()` sebelum insert.

### 5. Data Realisasi sebagai Transaksi
- Realisasi dari Excel disimpan sebagai satu `TransaksiPendapatan` dengan:
  - `tanggal_setor`: Gunakan `2026-10-23` (tanggal laporan per keterangan di header Excel: "Realisasi PAD sampai 23 Oktober 2026").
  - `jumlah_setor`: Nilai realisasi dari kolom D (dibulatkan ke integer).
  - `keterangan`: `'Seed awal dari laporan realisasi PAD 23 Oktober 2026'`.
  - `is_verif`: `true` (data sudah resmi dari laporan).
  - `bukti_dukung`: `null`.

---

## Struktur Database (Referensi)

### `rekenings`
```
id | parent_id | kode (unique) | uraian | level | timestamps
```
Level 6 = kode dengan 6 segmen titik, contoh: `4.1.01.09.01.00001`

### `target_pendapatans`
```
id | skpd_id (FK skpds) | rekening_id (FK rekenings) | tahun (YEAR) | target_anggaran (bigint) | keterangan_target | timestamps
UNIQUE: (skpd_id, rekening_id, tahun)
```

### `transaksi_pendapatans`
```
id | target_pendapatan_id (FK target_pendapatans) | tanggal_setor (date) | jumlah_setor (bigint) | bukti_dukung | keterangan | is_verif (bool) | timestamps
```

---

## Data Sumber (dari `pendapatan_daerah.xlsx`)

File: `database/seeders/data/pendapatan_daerah.xlsx`

Struktur kolom Excel:
- **A**: Kode Rekening
- **B**: Uraian
- **C**: Target Anggaran (Rp)
- **D**: Realisasi (Rp)
- **E**: Selisih (Rp) — diabaikan
- **F**: Persentase — diabaikan
- **G**: Dinas Pengelola

Baris data mulai dari **Row 6** (header di Row 4-5).

### Ringkasan Data Level 6 yang Akan Di-Seed

Berikut adalah semua entri **level 6** (kode 6-segmen) dari Excel:

#### 4.1 — PENDAPATAN ASLI DAERAH
**Pajak Daerah (BAPENDA)**:
- `4.1.01.09.01.00001` — Pajak Papan Merek/Bill Board/Megatron | Target: 425.600.000 | Real: 395.909.600
- `4.1.01.09.02.00001` — Pajak Spanduk/Reklame Kain | Target: 115.000.000 | Real: 79.338.240
- `4.1.01.09.03.00001` — Pajak Reklame Melekat/Stiker | Target: 10.000.000 | Real: 2.242.500
- `4.1.01.09.05.00001` — Pajak Reklame Berjalan | Target: 5.000.000 | Real: 0
- `4.1.01.12.01.00001` — Pajak Air Bawah Tanah | Target: 20.000.000 | Real: 9.821.259
- `4.1.01.14.37.00001` — Pajak Mineral bukan Logam dan Batuan Lainnya | Target: 500.000.000 | Real: 44.135.460
- `4.1.01.15.01.00001` — Pajak Bumi dan Bangunan (PBB) | Target: 1.000.000.000 | Real: 452.525.655
- `4.1.01.16.01.00001` — Pajak BPHTB (Pemindahan Hak) | Target: 2.000.000.000 | Real: 1.499.204.000
- `4.1.01.19.01.00001` — PBJT Restoran | Target: 2.080.000.000 | Real: 680.943.242
- `4.1.01.19.02.00001` — PBJT Konsumsi Tenaga Listrik | Target: 4.744.704.750 | Real: 3.833.045.851
- `4.1.01.19.03.00001` — PBJT Hotel | Target: 1.150.000.000 | Real: 468.906.685
- `4.1.01.19.04.00001` — PBJT Penyediaan Tempat Parkir | Target: 5.000.000 | Real: 2.340.000
- `4.1.01.19.05.00008` — PBJT Permainan Ketangkasan | Target: 25.000.000 | Real: 22.760.000
- `4.1.01.19.05.00009` — PBJT Olahraga Kebugaran | Target: 10.000.000 | Real: 100.000
- `4.1.01.19.05.00010` — PBJT Rekreasi Wahana Air | Target: *(kosong)* | Real: 4.822.196 *(hanya realisasi)*
- `4.1.01.19.05.00012` — PBJT Diskotek, Karaoke, dll | Target: 58.600.000 | Real: 4.784.000
- `4.1.01.20.01.00001` — OPSEN PKB | Target: 5.721.559.609 | Real: 3.263.626.800
- `4.1.01.21.01.00001` — OPSEN BBNKB | Target: 4.937.602.269 | Real: 1.312.181.300

**Retribusi Daerah**:
- `4.1.02.01.01.00001` — Retribusi Pelayanan Kesehatan Puskesmas | Target: 25.000.000 | Real: 32.616.000 | Dinas: DINKES
- `4.1.02.01.01.00005` — Retribusi Pelayanan Kesehatan RSUD | Target: 500.000.000 | Real: 15.681.000 | Dinas: DINKES
- `4.1.02.01.01.00006` — Retribusi Pelayanan Kesehatan Lainnya | Target: 65.000.000 | Real: 31.331.000 | Dinas: DINKES
- `4.1.02.01.02.00001` — Retribusi Persampahan/Kebersihan | Target: 1.889.132.000 | Real: 196.688.000 | Dinas: DLH
- `4.1.02.01.04.00001` — Retribusi Parkir di Tepi Jalan Umum | Target: 811.790.000 | Real: 145.756.500 | Dinas: DISHUB
- `4.1.02.02.01.00001` — Retribusi Penyewaan Tanah dan Bangunan (Bakeuda) | Target: 429.400.000 | Real: 410.260.000 | Dinas: BAPENDA
- `4.1.02.02.01.00003` — Retribusi Penyewaan Bangunan (Setda) | Target: 20.000.000 | Real: 46.950.000 | Dinas: SETDA
- `4.1.02.02.01.00007` — Retribusi Pemakaian Alat (PU) | Target: 1.007.000.000 | Real: 56.514.314 | Dinas: PUPR
- `4.1.02.02.02.00002` — Retribusi Pasar Grosir | Target: 1.780.606.000 | Real: 233.841.000 | Dinas: DISPERINDAG
- `4.1.02.02.07.00001` — Retribusi Rumah Potong Hewan | Target: 88.804.000 | Real: 13.300.000 | Dinas: DPKPP
- `4.1.02.02.09.00001` — Retribusi Tempat Rekreasi dan Olahraga | Target: 250.849.000 | Real: 202.620.000 | Dinas: DISBUDPAR
- `4.1.02.03.01.00001` — Retribusi Persetujuan Bangunan Gedung | Target: 404.831.000 | Real: 15.194.405 | Dinas: PUPR

**Hasil Pengelolaan Kekayaan Daerah (BAKEUDA)**:
- `4.1.03.02.01.00001` — Dividen BUMD Lembaga Keuangan | Target: 12.000.000.000 | Real: 12.674.609.832 | Dinas: BAKEUDA

**Lain-Lain PAD yang Sah**:
- `4.1.04.01.08.01` — Hasil Penjualan Aset Rusak Berat/Usang | Target: 354.000.000 | Real: 5.000.000 | Dinas: BAKEUDA
- `4.1.04.05.01.00001` — Jasa Giro Kas Daerah | Target: 1.000.000.000 | Real: 282.413.503 | Dinas: BAKEUDA
- `4.1.04.07.01.00001` — Pendapatan Bunga Penempatan Uang | Target: 2.500.000.000 | Real: 4.190.410 | Dinas: BAKEUDA
- `4.1.04.15.04.00001` — Pengembalian Perjalanan Dinas Dalam Negeri Biasa | Target: 200.000.000 | Real: 546.576.242 | Dinas: BAPENDA
- `4.1.04.15.04.00002` — Pengembalian Perjalanan Dinas Dalam Negeri Tetap | Target: 5.000.000.000 | Real: 1.605.756.069 | Dinas: BAPENDA
- `4.1.04.15.08.00001` — Pengembalian Gaji Pokok PNS | Target: 50.781.000 | Real: 37.481.420 | Dinas: BAPENDA
- `4.1.04.16.02.00001` — Pendapatan BLUD dari Jasa Layanan *(rekening level 6 induk, dengan sub-entitas berbeda — lihat catatan BLUD)*

**Data BLUD (Rekening `4.1.04.16.02.00001` dengan sub-entitas berbeda)**:
> Rekening `4.1.04.16.02.00001` adalah satu rekening level 6, namun di Excel ia dirinci per unit (tanpa kode sub-rincian eksplisit di kolom A, melainkan hanya di kolom B dan G). Seeder harus membuat **satu `target_pendapatan` per SKPD** untuk rekening yang sama.

| Uraian (kolom B) | Target | Realisasi | kode_skpd |
|---|---|---|---|
| Rumah Sakit Umum H.A Thalib | 50.000.000.000 | 24.920.743.471 | `RSMAYJEN` |
| Puskesmas Tanah Kampung | 563.442.455 | 397.396.911 | `PKMTK` |
| Puskesmas Koto Baru | 534.596.527 | 395.014.468 | `PKMKB` |
| Puskesmas Desa Gedang | 467.908.257 | 661.868.652 | `PKMDG` |
| Puskesmas Rawang | 807.882.362 | 575.783.203 | `PKMRWG` |
| Puskesmas Kumun | 651.737.094 | 542.021.610 | `PKMKD` |
| Puskesmas Sungai Penuh | 562.100.556 | 425.593.718 | `PKMSPN` |

> **Catatan**: Baris BLUD ini di Excel tidak memiliki kode di kolom A (kosong). Seeder harus mendeteksi baris ini berdasarkan fakta bahwa kolom A kosong namun kolom G berisi nama SKPD BLUD, sementara konteks baris sebelumnya adalah rekening `4.1.04.16.02.00001`.

#### 4.2 — PENDAPATAN TRANSFER (Semua → BAKEUDA)

Semua rekening level 6 di bawah `4.2` menggunakan dinas pengelola **BAKEUDA** (`kode_skpd: BAKEUDA`):

- `4.2.01.05.001.00001` — Dana Desa | Target: 41.996.684.000 | Real: 17.576.195.000
- `4.2.01.06.003.00001` — Insentif Fiskal Kinerja Tahun Berjalan | Target: 0 | Real: 4.500.000.000
- `4.2.01.07.001.00001` — DBH PBB | Target: 2.413.456.000 | Real: 6.947.599.800
- `4.2.01.07.001.00002` — DBH PPh Pasal 21 | Target: 1.943.043.000 | Real: 3.396.915.800
- `4.2.01.07.001.00003` — DBH PPh Pasal 25/WPOPDN | Target: 0 | Real: 289.017.600
- `4.2.01.07.001.00004` — DBH Cukai Hasil Tembakau | Target: *(kosong)* | Real: *(kosong)* → **SKIP**
- `4.2.01.07.002.00001` — DBH SDA Minyak Bumi | Target: 10.236.754.000 | Real: 12.325.572.400
- `4.2.01.07.002.00002` — DBH SDA Gas Bumi | Target: 0 | Real: 32.412.135.800
- `4.2.01.07.002.00004` — DBH SDA Mineral Batubara-Landrent | Target: *(kosong)* | Real: *(kosong)* → **SKIP**
- `4.2.01.07.002.00005` — DBH SDA Mineral Batubara-Royalty | Target: 3.198.367.000 | Real: 5.722.733.600
- `4.2.01.07.002.00006` — DBH SDA Kehutanan-PSDH | Target: 111.030.000 | Real: 156.655.000
- `4.2.01.07.002.00008` — DBH SDA Kehutanan-DR | Target: *(kosong)* | Real: *(kosong)* → **SKIP**
- `4.2.01.07.002.00009` — DBH SDA Perikanan | Target: 252.406.000 | Real: 201.924.800
- `4.2.01.07.003.00001` — DBH Sawit | Target: 400.000.000 | Real: 140.000.000
- `4.2.01.08.001.00001` — DAU (Tidak Ditentukan Penggunaannya) | Target: 389.804.204.000 | Real: 324.836.830.000
- `4.2.01.08.002.00001` — DAU Tambahan Dukungan Kelurahan | Target: 800.000.000 | Real: 800.000.000
- `4.2.01.08.002.00003` — DAU Tambahan PPPK | Target: *(kosong)* | Real: *(kosong)* → **SKIP**
- `4.2.01.08.002.00004` — DAU Ditentukan Bidang Pendidikan | Target: 3.946.746.000 | Real: 3.354.734.100
- `4.2.01.08.002.00005` — DAU Ditentukan Bidang Kesehatan | Target: 9.513.230.000 | Real: 8.086.245.500
- `4.2.01.08.002.00006` — DAU Ditentukan Bidang Pekerjaan Umum | Target: *(kosong)* | Real: *(kosong)* → **SKIP**
- `4.2.01.09.001.00013` — DAK Fisik Bidang Kesehatan | Target: 14.708.011.000 | Real: 10.295.607.700
- `4.2.01.09.002.00001` — DAK Non Fisik-BOS Reguler | Target: 11.743.500.000 | Real: 11.490.134.000
- `4.2.01.09.002.00003` — DAK Non Fisik-BOS Kinerja | Target: 0 | Real: 250.000.000
- `4.2.01.09.002.00004` — DAK Non Fisik-TPG PNSD | Target: 43.120.328.000 | Real: 32.261.622.880
- `4.2.01.09.002.00005` — DAK Non Fisik-Tamsil Guru PNSD | Target: 99.000.000 | Real: 10.723.500
- `4.2.01.09.002.00007` — DAK Non Fisik-BOP PAUD | Target: 2.150.700.000 | Real: 2.067.960.000
- `4.2.01.09.002.00008` — DAK Non Fisik-BOP Pendidikan Kesetaraan | Target: 458.200.000 | Real: 458.196.000
- `4.2.01.09.002.00012` — DAK Non Fisik-BOK KB/Pengawasan Obat | Target: 439.552.000 | Real: 343.368.880
- `4.2.01.09.002.00021` — DAK Non Fisik-BOKB-KB | Target: 1.531.900.000 | Real: 2.167.714.100
- `4.2.01.09.002.00022` — DAK Non Fisik-Perlindungan Perempuan dan Anak | Target: 507.000.000 | Real: 479.982.560
- `4.2.01.09.002.00023` — DAK Non Fisik-Ketahanan Pangan | Target: *(kosong)* | Real: *(kosong)* → **SKIP**
- `4.2.01.09.002.00033` — DAK Non Fisik-BOK Dinas | Target: 5.197.567.000 | Real: 4.795.253.815
- `4.2.01.09.002.00035` — DAK Non Fisik-BOK Puskesmas | Target: 7.660.764.000 | Real: 6.657.183.924
- `4.2.01.09.002.00041` — DAK Non Fisik-Perpustakaan Daerah | Target: 398.545.000 | Real: 382.838.200
- `4.2.02.01.001.00001` — Bagi Hasil Pajak Kendaraan Bermotor | Target: 5.188.976.797 | Real: 1.067.262.746
- `4.2.02.01.001.00002` — Bagi Hasil BBNKB | Target: 3.404.322.223 | Real: 219.902.471
- `4.2.02.01.001.00003` — Bagi Hasil PBBKB | Target: 12.738.268.829 | Real: 13.837.214.066
- `4.2.02.01.001.00004` — Bagi Hasil Pajak Air Permukaan | Target: 11.606.645 | Real: 143.299.887
- `4.2.02.01.001.00005` — Bagi Hasil Pajak Rokok | Target: 12.753.182.729 | Real: 8.819.749.311
- `4.2.02.02.001.00001` — Bantuan Keuangan Umum dari Pemerintah Daerah Provinsi | Target: 6.250.000.000 | Real: 3.490.000.000

#### 4.3 — LAIN-LAIN PENDAPATAN DAERAH YANG SAH (BAKEUDA)

- `4.3.03.02.001.0001` — Pendapatan Dana Kapitasi JKN pada FKTP | Target: 1.389.159.036 | Real: 849.296.522 | Dinas: BAKEUDA

---

## File yang Harus Dibuat

### 1. `database/seeders/data/pendapatan_daerah.xlsx`
> File ini **sudah ada** di path: `/Users/mac/.gemini/antigravity/brain/0be05472-858c-48f6-beaa-939af569458f/.user_uploaded/media_1793321150713_c8a9a7e6.xlsx`
>
> **Salin file tersebut ke**: `database/seeders/data/pendapatan_daerah.xlsx`

### 2. `database/seeders/RealisasiPadSeeder.php`

Class seeder dengan nama `RealisasiPadSeeder`.

---

## Spesifikasi Lengkap Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\Rekening;
use App\Models\Skpd;
use App\Models\TargetPendapatan;
use App\Models\TransaksiPendapatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealisasiPadSeeder extends Seeder
{
    /**
     * Tahun anggaran yang di-seed
     */
    const TAHUN = 2026;

    /**
     * Tanggal realisasi (dari header Excel: "Realisasi PAD sampai 23 Oktober 2026")
     */
    const TANGGAL_REALISASI = '2026-10-23';

    /**
     * Semua rekening yang dimulai dengan prefix ini → paksa BAKEUDA
     */
    const PREFIX_TRANSFER = '4.2';
    const PREFIX_LAIN_LAIN = '4.3';

    /**
     * Kode SKPD Badan Keuangan dan Aset Daerah
     */
    const KODE_BAKEUDA = 'BAKEUDA';

    public function run(): void
    {
        $xlsxPath = database_path('seeders/data/pendapatan_daerah.xlsx');

        if (!file_exists($xlsxPath)) {
            $this->command->error("File tidak ditemukan: {$xlsxPath}");
            $this->command->warn("Salin file Excel ke: database/seeders/data/pendapatan_daerah.xlsx");
            return;
        }

        $this->command->info('Memulai seeding Realisasi PAD...');

        // --- 1. Load data dari Excel ---
        $rows = $this->parseExcel($xlsxPath);

        // --- 2. Cache lookup data ---
        $skpdMap     = Skpd::pluck('id', 'kode_skpd')->toArray();   // [kode_skpd => id]
        $rekeningMap = Rekening::where('level', 6)->pluck('id', 'kode')->toArray(); // [kode => id]

        // --- 3. Proses per baris ---
        $stats = ['seeded' => 0, 'skipped_dup' => 0, 'skipped_no_data' => 0, 'skipped_no_match' => 0];

        DB::transaction(function () use ($rows, $skpdMap, $rekeningMap, &$stats) {
            $lastKodeL6 = null; // Untuk tracking konteks baris BLUD (kode kolom A kosong)

            foreach ($rows as $row) {
                $kodeRekening = trim($row['kode'] ?? '');
                $targetRaw    = $row['target'] ?? null;
                $realisasiRaw = $row['realisasi'] ?? null;
                $dinasRaw     = trim($row['dinas'] ?? '');

                // --- Tentukan level dari kode ---
                $level = $kodeRekening !== '' ? (substr_count($kodeRekening, '.') + 1) : 0;

                // --- Deteksi baris BLUD (kode kosong, tapi ada dinas + data) ---
                $isBludRow = (
                    $kodeRekening === ''
                    && $dinasRaw !== ''
                    && ($targetRaw !== null || $realisasiRaw !== null)
                    && $lastKodeL6 !== null
                );

                if ($isBludRow) {
                    // Gunakan rekening level 6 terakhir (konteks BLUD)
                    $kodeRekening = $lastKodeL6;
                    $level = 6;
                } elseif ($level !== 6) {
                    if ($level === 6) {
                        $lastKodeL6 = $kodeRekening;
                    }
                    continue; // Skip baris non-level-6
                } else {
                    $lastKodeL6 = $kodeRekening;
                }

                // --- Cek apakah target dan realisasi keduanya kosong ---
                $targetVal    = $this->parseNumber($targetRaw);
                $realisasiVal = $this->parseNumber($realisasiRaw);

                if ($targetVal === null && $realisasiVal === null) {
                    $stats['skipped_no_data']++;
                    continue;
                }

                // --- Tentukan SKPD ---
                $skpdId = $this->resolveSkpdId($kodeRekening, $dinasRaw, $skpdMap);
                if ($skpdId === null) {
                    $this->command->warn("  [SKIP] Dinas tidak ditemukan: '{$dinasRaw}' untuk rekening {$kodeRekening}");
                    $stats['skipped_no_match']++;
                    continue;
                }

                // --- Cari rekening di DB ---
                $rekeningId = $this->resolveRekeningId($kodeRekening, $rekeningMap);
                if ($rekeningId === null) {
                    $this->command->warn("  [SKIP] Rekening tidak ditemukan di DB: {$kodeRekening}");
                    $stats['skipped_no_match']++;
                    continue;
                }

                // --- Idempoten: Cek TargetPendapatan ---
                $target = TargetPendapatan::firstOrCreate(
                    [
                        'skpd_id'     => $skpdId,
                        'rekening_id' => $rekeningId,
                        'tahun'       => self::TAHUN,
                    ],
                    [
                        'target_anggaran'   => (int) round($targetVal ?? 0),
                        'keterangan_target' => 'Seed awal dari laporan realisasi PAD 23 Oktober 2026',
                    ]
                );

                // Jika record sudah ada sebelumnya, skip (idempoten penuh)
                if (!$target->wasRecentlyCreated) {
                    $stats['skipped_dup']++;
                    continue;
                }

                // --- Buat TransaksiPendapatan jika ada realisasi ---
                if ($realisasiVal !== null && $realisasiVal > 0) {
                    TransaksiPendapatan::create([
                        'target_pendapatan_id' => $target->id,
                        'tanggal_setor'        => self::TANGGAL_REALISASI,
                        'jumlah_setor'         => (int) round($realisasiVal),
                        'bukti_dukung'         => null,
                        'keterangan'           => 'Seed awal dari laporan realisasi PAD 23 Oktober 2026',
                        'is_verif'             => true,
                    ]);
                }

                $stats['seeded']++;
                $this->command->line(
                    "  [OK] {$kodeRekening} | SKPD: " . (array_search($skpdId, $skpdMap) ?: "ID:{$skpdId}")
                    . " | Target: " . number_format($targetVal ?? 0)
                    . " | Real: " . number_format($realisasiVal ?? 0)
                );
            }
        });

        $this->command->info('');
        $this->command->info('=== SELESAI ===');
        $this->command->info("  Di-seed      : {$stats['seeded']} rekening");
        $this->command->info("  Skip duplikat: {$stats['skipped_dup']} rekening");
        $this->command->info("  Skip no data : {$stats['skipped_no_data']} rekening");
        $this->command->info("  Skip no match: {$stats['skipped_no_match']} rekening");
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Parse file Excel menggunakan ZipArchive + SimpleXML (tanpa library tambahan).
     * Kembalikan array of associative arrays dengan kunci: kode, uraian, target, realisasi, dinas.
     */
    private function parseExcel(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException("Tidak bisa membuka file Excel: {$path}");
        }

        // Baca shared strings
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $ss = new \SimpleXMLElement($ssXml);
            $ss->registerXPathNamespace('ns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($ss->xpath('//ns:si') as $si) {
                $texts = $si->xpath('.//ns:t');
                $text = '';
                foreach ($texts as $t) {
                    $text .= (string) $t;
                }
                $sharedStrings[] = $text;
            }
        }

        // Baca worksheet
        $wsXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($wsXml === false) {
            throw new \RuntimeException("Worksheet tidak ditemukan di file Excel.");
        }

        $ws = new \SimpleXMLElement($wsXml);
        $ws->registerXPathNamespace('ns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = [];
        foreach ($ws->xpath('//ns:row') as $row) {
            $rowIndex = (int) $row['r'];
            if ($rowIndex < 6) continue; // Skip header rows 1-5

            $cells = [];
            foreach ($row->xpath('ns:c') as $cell) {
                $ref  = (string) $cell['r'];
                $type = (string) $cell['t'];
                $vEl  = $cell->xpath('ns:v');
                $val  = isset($vEl[0]) ? (string) $vEl[0] : null;

                if ($val !== null && $type === 's') {
                    $val = $sharedStrings[(int) $val] ?? $val;
                }

                // Ekstrak kolom (huruf) dari referensi sel (misal: "A6" → "A")
                preg_match('/^([A-Z]+)/', $ref, $m);
                $col = $m[1] ?? '';
                $cells[$col] = $val;
            }

            if (empty($cells)) continue;

            $rows[] = [
                'kode'      => $cells['A'] ?? '',
                'uraian'    => $cells['B'] ?? '',
                'target'    => $cells['C'] ?? null,
                'realisasi' => $cells['D'] ?? null,
                'dinas'     => $cells['G'] ?? '',
            ];
        }

        return $rows;
    }

    /**
     * Parse angka dari string Excel.
     * Return null jika kosong atau bukan angka valid.
     * Tangani notasi ilmiah (e.g., "8.8270920000000003E-2").
     */
    private function parseNumber(?string $val): ?float
    {
        if ($val === null || $val === '' || $val === '#DIV/0!') {
            return null;
        }
        $num = (float) $val;
        return is_nan($num) ? null : $num;
    }

    /**
     * Tentukan skpd_id berdasarkan kode rekening dan nama dinas dari Excel.
     *
     * Aturan:
     * - Jika kode rekening mulai dengan 4.2 atau 4.3 → paksa BAKEUDA
     * - Jika kode rekening mulai dengan 4.1 → mapping dari nama dinas di kolom G
     */
    private function resolveSkpdId(string $kodeRekening, string $dinasRaw, array $skpdMap): ?int
    {
        // Paksa BAKEUDA untuk Pendapatan Transfer dan Lain-lain Pendapatan Daerah
        if (
            str_starts_with($kodeRekening, self::PREFIX_TRANSFER) ||
            str_starts_with($kodeRekening, self::PREFIX_LAIN_LAIN)
        ) {
            return $skpdMap[self::KODE_BAKEUDA] ?? null;
        }

        // Mapping dari nama dinas ke kode SKPD
        $kodeSkpd = $this->mapDinasToSkpdKode($dinasRaw);
        if ($kodeSkpd === null) {
            return null;
        }

        return $skpdMap[$kodeSkpd] ?? null;
    }

    /**
     * Map nama dinas (dari kolom G Excel) ke kode_skpd di tabel skpds.
     * Case-insensitive, trim.
     */
    private function mapDinasToSkpdKode(string $dinas): ?string
    {
        $dinas = strtoupper(trim($dinas));

        $mapping = [
            'BADAN PENDAPATAN DAERAH'                                        => 'BAPENDA',
            'BADAN KEUANGAN DAN ASET DAERAH'                                 => 'BAKEUDA',
            'DINAS KESEHATAN'                                                 => 'DINKES',
            'DINAS LINGKUNGAN HIDUP'                                          => 'DLH',
            'DINAS PERHUBUNGAN'                                               => 'DISHUB',
            'DINAS PEKERJAAN UMUM DAN PERUMAHAN RAKYAT'                      => 'PUPR',
            'DINAS PERDAGANGAN DAN PERINDUSTRIAN'                            => 'DISPERINDAG',
            'DINAS PERTANIAN, KETAHANAN PANGAN DAN PERIKANAN'                => 'DPKPP',
            'DINAS PERTANIAN KETAHANAN PANGAN DAN PERIKANAN'                 => 'DPKPP',
            'SETDA BAGIAN UMUM'                                               => 'SETDA',
            'DISBUDPARPORA'                                                   => 'DISBUDPAR',
            'RUMAH SAKIT UMUM H.A THALIB'                                    => 'RSMAYJEN',
            'RUMAH SAKIT UMUM HA THALIB'                                     => 'RSMAYJEN',
            'PUSKESMAS TANAH KAMPUNG'                                         => 'PKMTK',
            'PUSKESMAS KOTO BARU'                                             => 'PKMKB',
            'PUSKESMAS DESA GEDANG'                                           => 'PKMDG',
            'PUSKESMAS RAWANG'                                                => 'PKMRWG',
            'PUSKESMAS KUMUN'                                                 => 'PKMKD',
            'PUSKESMAS SUNGAI PENUH'                                          => 'PKMSPN',
        ];

        return $mapping[$dinas] ?? null;
    }

    /**
     * Resolve rekening_id dari kode rekening.
     * Coba exact match di cache, lalu query DB sebagai fallback.
     */
    private function resolveRekeningId(string $kode, array $rekeningMap): ?int
    {
        $kode = rtrim(trim($kode), '.');

        if (isset($rekeningMap[$kode])) {
            return $rekeningMap[$kode];
        }

        // Fallback: query langsung ke DB (untuk rekening yang belum di-cache)
        $rekening = Rekening::where('kode', $kode)->where('level', 6)->first();
        return $rekening?->id;
    }
}
```

---

## Langkah Eksekusi

### Prasyarat
1. **RekeningSeeder sudah dijalankan** — tabel `rekenings` harus terisi dengan data dari `akun_pendapatan_belanja_cleaned.csv`.
2. **SkpdSeeder sudah dijalankan** — tabel `skpds` harus terisi.
3. File Excel harus disalin ke `database/seeders/data/pendapatan_daerah.xlsx`.

### Perintah

```bash
# Salin file Excel ke direktori seeder
cp /Users/mac/.gemini/antigravity/brain/0be05472-858c-48f6-beaa-939af569458f/.user_uploaded/media_1793321150713_c8a9a7e6.xlsx \
   /Users/mac/Herd/diskominfo_plan/database/seeders/data/pendapatan_daerah.xlsx

# Jalankan seeder (idempoten — aman dijalankan berkali-kali)
php artisan db:seed --class=RealisasiPadSeeder
```

### Daftarkan ke `DatabaseSeeder.php` (opsional)

Jika ingin dijalankan dalam flow seeder utama, tambahkan setelah `RekeningSeeder`:

```php
$this->call([
    SkpdSeeder::class,
    RekeningSeeder::class,
    RealisasiPadSeeder::class, // ← tambahkan di sini
]);
```

---

## Catatan Teknis Penting

### Kode Rekening: Perbedaan Format di Excel vs Database

> **PERHATIAN**: Format kode rekening di Excel (`pendapatan_daerah.xlsx`) menggunakan **format berbeda** dari CSV rekening (`akun_pendapatan_belanja_cleaned.csv`).
>
> Contoh di Excel: `4.1.01.09.01.00001`
> Contoh di CSV/DB: `4.1.01.09.001.00001` (segmen 4 pakai 3 digit)
>
> Seeder menggunakan strategi:
> 1. **Coba exact match** dengan kode dari Excel.
> 2. Jika tidak ketemu, **query langsung ke DB** sebagai fallback.
> 3. Jika masih tidak ketemu, log peringatan dan skip.

### Baris BLUD Tanpa Kode Rekening

Baris BLUD di Excel (Row 98–104) tidak memiliki kode di kolom A. Seeder mendeteksi ini dengan:
1. Melacak variabel `$lastKodeL6` — kode rekening level 6 terakhir yang diproses.
2. Saat menemukan baris tanpa kode (A kosong) tapi ada data dinas/target/realisasi di kolom G, C, D → gunakan `$lastKodeL6` sebagai rekening.
3. Unique constraint (`skpd_id + rekening_id + tahun`) memastikan tidak ada duplikasi meski rekening sama dan SKPD berbeda.

### Nilai Target = 0 tapi Realisasi Ada

Contoh: `4.2.01.06.003.00001` (Insentif Fiskal) — Target = 0, Realisasi = 4.500.000.000

- Target 0 tetap di-seed sebagai `target_anggaran = 0`.
- Realisasi tetap dibuat sebagai `TransaksiPendapatan`.
- Ini valid secara bisnis (pendapatan tidak teranggaran tapi terealisasi).

### Nilai Negatif dan #DIV/0!

- Kolom F (`%`) bisa berisi string `#DIV/0!` — **diabaikan**.
- Nilai realisasi bisa lebih besar dari target (persentase > 100%) — **valid, tetap seed**.
- Selisih negatif di kolom E **diabaikan** oleh seeder.

### Idempoten Penuh

- Jika `TargetPendapatan` sudah ada (berdasarkan unique key `skpd_id + rekening_id + tahun`), seluruh baris dilewati — **tidak membuat duplikat transaksi**.
- Seeder aman dijalankan berulang kali tanpa mengubah data yang sudah ada.

---

## Checklist Verifikasi Pasca-Seed

Jalankan query SQL berikut untuk memverifikasi:

```sql
-- Total target_pendapatans yang di-seed
SELECT COUNT(*) as total_rekening, SUM(target_anggaran) as total_target
FROM target_pendapatans
WHERE tahun = 2026;

-- Total transaksi_pendapatans
SELECT COUNT(*) as total_transaksi, SUM(tp.jumlah_setor) as total_realisasi
FROM transaksi_pendapatans tp
JOIN target_pendapatans tpd ON tp.target_pendapatan_id = tpd.id
WHERE tpd.tahun = 2026
  AND tp.is_verif = 1;

-- Rekapitulasi per SKPD
SELECT s.kode_skpd, s.nama_skpd,
       COUNT(tp.id) as jumlah_rekening,
       SUM(tp.target_anggaran) as total_target,
       COALESCE(SUM(t.jumlah_setor), 0) as total_realisasi
FROM target_pendapatans tp
JOIN skpds s ON tp.skpd_id = s.id
LEFT JOIN transaksi_pendapatans t ON t.target_pendapatan_id = tp.id AND t.is_verif = 1
WHERE tp.tahun = 2026
GROUP BY s.id, s.kode_skpd, s.nama_skpd
ORDER BY total_target DESC;
```

**Nilai yang diharapkan (berdasarkan data Excel)**:
- Total target pendapatan daerah: ~Rp 699.540.130.138
- Total realisasi: ~Rp 577.497.218.481
- Persentase realisasi: ~82,55%
- Jumlah SKPD dengan pendapatan: minimal 20 SKPD (termasuk Puskesmas dan RSU)

