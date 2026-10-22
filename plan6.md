# Plan 6: Rekening Pendapatan — Admin Panel s/d Level 6 & Public Tampil s/d Level 4

## Konteks & Tujuan

Menyempurnakan modul **PAD / Pendapatan** agar:

1. **Admin panel (form pilih rekening)** hanya menampilkan rekening yang **berasal dari kelompok "Pendapatan Daerah" (kode mulai `4`)**, dan pilihan rekening mencakup **level 1 s/d level 6**.
2. **Tampilan publik** (`PublicPendapatan`) tetap menampilkan hierarki **level 1 s/d level 4** saja.
3. **Target & realisasi** dihitung dari data `target_pendapatans` yang rekeningnya ada di **level 5 atau 6** (rekening paling dasar / leaf), lalu di-rollup ke atas secara bottom-up hingga level 1.
4. **CSV seeder** (`akun_pendapatan_belanja_cleaned.csv`) ditambah baris **Level 1: `4,Pendapatan Daerah`** agar seeder bisa membentuk hierarki penuh dari level 1.

---

## Analisis Kondisi Saat Ini

### Struktur Tabel

| Tabel | Kolom penting |
|---|---|
| `rekenings` | `id`, `parent_id`, `kode`, `uraian`, `level` |
| `target_pendapatans` | `id`, `skpd_id`, `rekening_id`, `tahun`, `target_anggaran` |
| `transaksi_pendapatans` | `id`, `target_pendapatan_id`, `tanggal_setor`, `jumlah_setor` |

### Kondisi CSV saat ini
- Baris pertama data (setelah header): `4.1,PENDAPATAN ASLI DAERAH (PAD)` → ini level 2
- **Tidak ada baris level 1** `4,Pendapatan Daerah` → menyebabkan hierarki putus di paling atas

### Kondisi Form Saat Ini
```php
// TargetPendapatanForm.php — hanya level 4 yang muncul
->where('level', 4)
->where(function (Builder $q) {
    $q->where('kode', 'like', '4%')
        ->orWhere('kode', 'like', ' 4%');
})
```
→ Harus diubah: level yang bisa dipilih adalah **1–6**, tapi tetap hanya rekening dengan `kode` berawalan `4`.

### Kondisi `getPendapatanTree()` saat ini
- Filter akhir: `$node['level'] <= 4` → sudah benar untuk tampilan publik
- Kalkulasi rollup: bottom-up dari level tertinggi ke atas — sudah benar logikanya
- Masalah potensial: jika admin memasukkan target di level 1–4, angka akan dijumlah dua kali (sekali dari data langsung, sekali dari rollup anak)

---

## Perubahan yang Diperlukan

### 1. Update CSV: Tambah Level 1 Pendapatan Daerah

**File:** `database/seeders/data/akun_pendapatan_belanja_cleaned.csv`

Tambahkan **satu baris** tepat setelah header, sebelum baris `4.1,PENDAPATAN ASLI DAERAH (PAD)`:

```csv
kode,uraian
4,Pendapatan Daerah
4.1,PENDAPATAN ASLI DAERAH (PAD)
4.1.01,Pajak Daerah
...
```

> Dengan adanya baris `4,Pendapatan Daerah`, `RekeningSeeder` akan otomatis membuat parent level-1 ini dan semua level 2 (`4.1`, `4.2`, `4.3`) akan ter-link ke parent yang benar melalui mekanisme `parentCode` di seeder.

---

### 2. Update Form Admin Panel: Izinkan Pilih Rekening Level 1–6

**File:** `app/Filament/Resources/TargetPendapatans/Schemas/TargetPendapatanForm.php`

**Ubah** blok `Select::make('rekening_id')`:

```php
// SEBELUM
Select::make('rekening_id')
    ->label('Rekening Pendapatan')
    ->required()
    ->relationship(
        name: 'rekening',
        modifyQueryUsing: fn(Builder $query) => $query
            ->where('level', 4)
            ->where(function (Builder $q) {
                $q->where('kode', 'like', '4%')
                    ->orWhere('kode', 'like', ' 4%');
            })
            ->orderBy('kode', 'asc')
    )
    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} - {$record->uraian}")
    ->searchable(['kode', 'uraian'])
    ->optionsLimit(1000)
    ->preload()
    ->columnSpanFull(),
```

```php
// SESUDAH
Select::make('rekening_id')
    ->label('Rekening Pendapatan')
    ->required()
    ->relationship(
        name: 'rekening',
        modifyQueryUsing: fn(Builder $query) => $query
            ->whereBetween('level', [1, 6])        // level 1–6
            ->where('kode', 'like', '4%')           // hanya kelompok pendapatan (kode berawalan 4)
            ->orderBy('kode', 'asc')
    )
    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} - {$record->uraian}")
    ->searchable(['kode', 'uraian'])
    ->optionsLimit(2000)
    ->preload()
    ->columnSpanFull(),
```

**Penjelasan perubahan:**
- `->where('level', 4)` → `->whereBetween('level', [1, 6])` : membuka pilihan sampai level 6
- `->orWhere('kode', 'like', ' 4%')` dihapus — bersihkan kondisi redundan dengan spasi
- `->optionsLimit(1000)` → `->optionsLimit(2000)` — antisipasi jumlah rekening yang lebih banyak

---

### 3. Update `getPendapatanTree()`: Rollup dari Level 5 & 6

**File:** `app/Repositories/Eloquent/DashboardRepository.php`

Logika baru yang diperlukan:

- Data **target & realisasi** yang diinput admin berada di rekening mana pun (level 1–6).
- Untuk kalkulasi pohon publik, kita harus:
  1. Ambil semua `target_pendapatans` beserta `rekening_id`-nya.
  2. Bangun `nodeMap` dari semua rekening.
  3. Lakukan **rollup bottom-up**: hanya rekening yang **tidak punya anak di `target_pendapatans`** yang dihitung sebagai sumber data asli; ancestor mereka dijumlahkan dari bawah ke atas.
  4. Filter tampilan: hanya tampilkan `level <= 4` dan `kode` berawalan `4`.

**Strategi rollup yang benar (menghindari double-count):**

```php
public function getPendapatanTree(?int $tahun = null): array
{
    $tahun = $tahun ?? date('Y');

    // 1. Ambil semua target & realisasi per rekening_id
    $targets = \App\Models\TargetPendapatan::with('transaksi_pendapatan')
        ->where('tahun', $tahun)
        ->get();

    // Map: rekening_id => [target, realisasi]
    $rawMap = [];
    foreach ($targets as $target) {
        $rekId = $target->rekening_id;
        if (!isset($rawMap[$rekId])) {
            $rawMap[$rekId] = ['target' => 0, 'realisasi' => 0];
        }
        $rawMap[$rekId]['target']    += (float) $target->target_anggaran;
        $rawMap[$rekId]['realisasi'] += (float) $target->transaksi_pendapatan->sum('jumlah_setor');
    }

    // 2. Ambil semua rekening pendapatan (kode berawalan '4')
    $rekenings = \App\Models\Rekening::where('kode', 'like', '4%')
        ->orderBy('level', 'desc') // mulai dari level terdalam
        ->orderBy('kode', 'asc')
        ->get();

    // 3. Bangun nodeMap awal (hanya angka yang diinput langsung, belum rollup)
    $nodeMap = [];
    foreach ($rekenings as $rek) {
        $nodeMap[$rek->id] = [
            'id'        => $rek->id,
            'parent_id' => $rek->parent_id,
            'kode'      => $rek->kode,
            'uraian'    => $rek->uraian,
            'level'     => $rek->level,
            'target'    => $rawMap[$rek->id]['target'] ?? 0,
            'realisasi' => $rawMap[$rek->id]['realisasi'] ?? 0,
        ];
    }

    // 4. Bottom-up rollup: propagate ke parent
    // Karena $rekenings diurutkan level DESC, kita proses dari level terdalam ke atas
    foreach ($rekenings as $rek) {
        $parentId = $rek->parent_id;
        if ($parentId && isset($nodeMap[$parentId])) {
            $nodeMap[$parentId]['target']    += $nodeMap[$rek->id]['target'];
            $nodeMap[$parentId]['realisasi'] += $nodeMap[$rek->id]['realisasi'];
        }
    }

    // 5. Urutkan berdasarkan kode ASC untuk output yang rapi
    $sorted = collect($nodeMap)->sortBy('kode')->values()->toArray();

    // 6. Filter: hanya level 1–4 untuk tampilan publik
    $result = [];
    foreach ($sorted as $node) {
        if ($node['level'] > 4) continue;

        $target     = (float) $node['target'];
        $realisasi  = (float) $node['realisasi'];
        $persentase = $target > 0 ? round(($realisasi / $target) * 100, 2) : 0;

        $result[] = [
            'kode'      => trim($node['kode']),
            'uraian'    => $node['uraian'],
            'level'     => $node['level'],
            'target'    => $target,
            'realisasi' => $realisasi,
            'selisih'   => max($target - $realisasi, 0),
            'persentase'=> $persentase,
        ];
    }

    return $result;
}
```

> **Catatan penting:** Jika admin menginput target di level 4 sekaligus level 5 yang merupakan anaknya, maka angka akan terhitung dua kali (sekali dari data langsung di level 4, sekali dari rollup level 5 ke level 4). Hindari situasi ini dengan **konvensi**: selalu input target di rekening level terdalam yang tersedia (preferensi level 5 atau 6). Dokumentasikan konvensi ini di UI sebagai tooltip/helper text pada form.

---

### 4. Tambah Helper Text di Form (Opsional tapi Direkomendasikan)

**File:** `app/Filament/Resources/TargetPendapatans/Schemas/TargetPendapatanForm.php`

Tambahkan `->helperText()` pada `Select::make('rekening_id')`:

```php
->helperText('Pilih rekening sampai level 6 (paling detail). Hindari memilih rekening induk jika sudah ada rekening anak yang lebih spesifik.')
```

---

### 5. Update `RekeningSeeder` — Gunakan File CSV yang Benar

**File:** `database/seeders/RekeningSeeder.php`

Saat ini seeder membaca dari `akun_belanja_seeder_fixed.csv`. Pastikan diubah atau seeder baru dibuat yang membaca dari `akun_pendapatan_belanja_cleaned.csv`:

```php
// UBAH baris ini:
$csvPath = database_path('seeders/data/akun_belanja_seeder_fixed.csv');

// MENJADI:
$csvPath = database_path('seeders/data/akun_pendapatan_belanja_cleaned.csv');
```

> Atau buat seeder terpisah `RekeningPendapatanSeeder.php` jika file CSV belanja tidak boleh diganggu.

---

## Ringkasan Perubahan File

| File | Jenis Perubahan | Detail |
|------|----------------|--------|
| `database/seeders/data/akun_pendapatan_belanja_cleaned.csv` | **Edit** | Tambah baris `4,Pendapatan Daerah` setelah header |
| `database/seeders/RekeningSeeder.php` | **Edit** | Ubah path CSV ke `akun_pendapatan_belanja_cleaned.csv` |
| `app/Filament/Resources/TargetPendapatans/Schemas/TargetPendapatanForm.php` | **Edit** | Ubah filter rekening: level 1–6, hanya kode `4%` |
| `app/Repositories/Eloquent/DashboardRepository.php` | **Edit** | Rewrite `getPendapatanTree()` dengan rollup bottom-up yang benar & filter `is_verif = true` |
| `database/migrations/2026_10_22_091500_update_transaksi_pendapatans_table.php` | **Baru** | Hapus `nomor_bukti`, tambah `bukti_dukung` & `is_verif` |
| `app/Models/TransaksiPendapatan.php` | **Edit** | Tambah casts untuk `is_verif` |
| `app/Filament/Resources/TargetPendapatans/RelationManager/TransaksiPendapatanRelationManager.php` | **Edit** | Upload `bukti_dukung` (gambar/PDF opsional), aksi lihat/unduh, aksi verifikasi per role |
| `app/Filament/Resources/TargetPendapatans/Tables/TargetPendapatansTable.php` | **Edit** | Hitung realisasi verified & badge pending verifikasi |

---

## Urutan Pengerjaan

```
1. Edit CSV → tambah baris `4,Pendapatan Daerah`
2. Edit RekeningSeeder → pastikan baca file CSV yang benar
3. Jalankan seeder: php artisan db:seed --class=RekeningSeeder
4. Edit TargetPendapatanForm → buka level 1–6
5. Edit DashboardRepository → rewrite getPendapatanTree()
6. Test: input target di level 5/6, cek tampilan publik (hanya sampai level 4)
```

---

## Contoh Alur Data (Ilustrasi)

```
Level 1: 4           Pendapatan Daerah           ← Hanya tampil (rollup)
Level 2: 4.1         PAD                         ← Hanya tampil (rollup)
Level 3: 4.1.01      Pajak Daerah                ← Hanya tampil (rollup)
Level 4: 4.1.01.09   Pajak Reklame               ← Hanya tampil (rollup)
Level 5: 4.1.01.09.001 Pajak Reklame Papan       ← Bisa diinput target (tidak tampil publik)
Level 6: 4.1.01.09.001.00001 Pajak Reklame ...   ← Bisa diinput target (tidak tampil publik)
```

**Kalkulasi target & realisasi di tampilan publik:**
- Level 4 (`4.1.01.09`) = SUM dari semua target/realisasi di level 5 & 6 yang merupakan turunannya
- Level 3 (`4.1.01`) = SUM dari semua level 4 di bawahnya
- Level 2 (`4.1`) = SUM dari semua level 3 di bawahnya
- Level 1 (`4`) = SUM dari semua level 2 di bawahnya

---

## Pembaruan Fitur Bukti Dukung & Verifikasi Realisasi

1. **Penggantian Kolom:**
   - Kolom `nomor_bukti` dihapus dari tabel `transaksi_pendapatans`.
   - Ditambahkan kolom `bukti_dukung` (tipe `string`, nullable) untuk menyimpan berkas lampiran setoran.
   - Ditambahkan kolom `is_verif` (tipe `boolean`, default `false`) sebagai penanda status verifikasi setoran.

2. **Upload & Download Bukti Dukung:**
   - Komponen input berkas `FileUpload` di admin panel mendukung berkas **PDF dan Gambar** (JPG, JPEG, PNG, WEBP) hingga 10MB.
   - Sifat berkas saat ini **opsional** (tidak wajib diisi).
   - Terdapat kolom & tombol tautan langsung untuk **melihat / mengunduh** berkas bukti dukung yang sudah diunggah.

3. **Hak Akses Verifikasi (`is_verif`):**
   - Role **Admin SKPD / SKPD** tidak dapat memverifikasi sendiri (toggle dinonaktifkan/disembunyikan untuk SKPD).
   - Jika Admin SKPD membuat atau mengedit setoran, `is_verif` otomatis di-set `false`.
   - Role **selain Admin SKPD** (Admin / Verifikator) memiliki aksi cepat tabel untuk **Verifikasi** dan **Batal Verif** dengan dialog konfirmasi.

4. **Syarat Perhitungan di Halaman Publik:**
   - Semua kalkulasi realisasi di publik (`getPendapatanTree()`, `getAllSkpdPendapatanSummary()`, `getPendapatanDetailBySkpd()`, grafik keseluruhan) **hanya menghitung transaksi dengan `is_verif = true`**.
   - Transaksi yang belum diverifikasi tidak akan masuk ke perhitungan capaian publik.

- **Idempotent seeder** — `RekeningSeeder` sudah menggunakan `updateOrCreate`, aman dijalankan ulang.
- **Konvensi input** — targetkan bahwa admin selalu mengisi di rekening level 5 atau 6. Level 1–4 bersifat summary/rollup saja. Jika ada kebutuhan input di level lebih tinggi (misal level 3 atau 4 tanpa ada anak spesifik), sistem tetap mendukungnya — hanya perlu disosialisasikan ke admin.
- **Double-count prevention** — jika khawatir admin salah input di parent dan child sekaligus, bisa ditambahkan validasi di form: cek apakah rekening yang dipilih sudah punya anak yang juga ada target-nya (lebih kompleks, bisa dikerjakan di plan berikutnya jika diperlukan).

