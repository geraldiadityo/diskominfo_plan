# RENCANA IMPLEMENTASI (PLAN): Otomatisasi Perhitungan Realisasi Fisik Triwulan Berdasarkan Indikator Fisik & Bukti di Renja SKPD

Dokumen ini ditujukan sebagai **panduan instruksi teknis lengkap** bagi AI Agent / Software Engineer untuk mengimplementasikan fitur perhitungan otomatis Realisasi Fisik di Admin Panel Filament.

---

## 1. Ringkasan Eksekutif & Tujuan

### 1.1. Latar Belakang Masalah
Saat ini pada modul **Renja SKPD** (`RenjaSkpdResource`), di dalam Relation Manager **Realisasi Triwulan** (`RealisasiKegiatanRelationManager`), input field **`realisasi_fisik`** masih diisi secara manual oleh pengguna (SKPD/Admin) melalui form input biasa. Hal ini membuka celah ketidaksesuaian data antara capaian fisik yang diklaim dengan dokumen bukti riil yang diunggah dan diverifikasi.

### 1.2. Sasaran Implementasi
1. Field **`realisasi_fisik`** pada tingkat triwulan **TIDAK BISA lagi diisi manual** (menjadi *read-only* / terkunci otomatis).
2. Nilai persentase **`realisasi_fisik`** dihitung **secara otomatis oleh sistem** berdasarkan status persetujuan dari data **Indikator Fisik & Bukti** (`BuktiFisikRealisasi`).
3. Perhitungan bersifat **reaktif dan tersinkronisasi otomatis** saat:
   - Verifikator menyetujui (*disetujui*) atau menolak (*ditolak*) dokumen bukti fisik.
   - Indikator fisik baru ditambahkan atau dihapus.
   - Nilai `target_fisik` pada triwulan tersebut diperbarui.

---

## 2. Logika & Formula Perhitungan

### 2.1. Definisi Variabel
- $T_{\text{fisik}}$ = **Target Fisik Triwulan** (kolom `target_fisik` pada tabel `realisasi_kegiatans`, misal: `20.00%`).
- $N_{\text{total}}$ = **Total Indikator Fisik** yang ada pada triwulan tersebut (jumlah baris pada `bukti_fisik_realisasis`).
- $N_{\text{disetujui}}$ = **Jumlah Indikator Fisik yang Disetujui** (`status_verifikasi == 'disetujui'`).
- $R_{\text{fisik}}$ = **Realisasi Fisik Triwulan** (kolom `realisasi_fisik` pada `realisasi_kegiatans`).

---

### 2.2. Formula Perhitungan (Dua Pendekatan)

#### Pendekatan A: Bobot Rata / Berdasarkan Jumlah Indikator (Standar Proporsional)
Setiap indikator memiliki bobot yang sama ($1/N$).
$$\text{Persentase Capaian Indikator (\%)} = \frac{N_{\text{disetujui}}}{N_{\text{total}}} \times 100\%$$
$$R_{\text{fisik}} = \left( \frac{N_{\text{disetujui}}}{N_{\text{total}}} \right) \times T_{\text{fisik}}$$

**Simulasi:**
- Target Triwulan 1 = **20%**
- Total ada 5 Indikator:
  - Jika **5 indikator disetujui** (100%): $\frac{5}{5} \times 20\% = \mathbf{20.00\%}$.
  - Jika **4 indikator disetujui** (80%): $\frac{4}{5} \times 20\% = \mathbf{16.00\%}$ (atau 80% dari target 20%).
  - Jika **0 indikator disetujui**: $\mathbf{0.00\%}$.

---

#### Pendekatan B: Indikator Berbobot Dinamis (Menjelaskan Kasus "4 Disetujui = 90% dari Target")
Jika dalam kasus bisnis tertentu indikator memiliki tingkat kepentingan/bobot berbeda (misalnya salah satu indikator kecil berbobot 10%, sedangkan 4 indikator lainnya menyumbang 90% dari total):
- Sistem dapat menyediakan kolom opsional `bobot` (desimal/persen) pada tabel `bukti_fisik_realisasis`.
- Formula:
  $$\text{Total Bobot Disetujui (\%)} = \sum \text{bobot indikator yang berstatus 'disetujui'}$$
  $$R_{\text{fisik}} = \frac{\text{Total Bobot Disetujui}}{\text{Total Bobot Semua Indikator}} \times T_{\text{fisik}}$$
- Jika kolom `bobot` tidak diisi atau null, sistem otomatis melakukan *fallback* ke **Pendekatan A (Bobot Rata)**.

---

### 2.3. Aturan Kondisi Khusus (Edge Cases)
1. **Belum Ada Indikator ($N_{\text{total}} = 0$):**
   - Nilai $R_{\text{fisik}} = 0.00\%$.
2. **Target Fisik Triwulan Kosong atau 0 ($T_{\text{fisik}} \le 0$):**
   - Nilai $R_{\text{fisik}} = 0.00\%$.
3. **Pembulatan Nilai:**
   - Gunakan pembulatan standar 2 angka desimal (`round($value, 2)`), sesuai tipe kolom database `decimal(5, 2)`.
4. **Batas Nilai (Clamping):**
   - Nilai realisasi tidak boleh negatif ($R_{\text{fisik}} \ge 0$) dan tidak melebihi 100.00%.

---

## 3. Arsitektur Teknis & Komponen yang Terlibat

### 3.1. Tech Stack
- **Framework:** Laravel 12
- **Admin Panel:** Filament PHP 4.x / 3.x
- **Database:** MySQL / MariaDB / PostgreSQL

### 3.2. Struktur Relasi & File Terkait
```
RenjaSkpd (Model)
  └── hasMany: RealisasiKegiatan (Model) [Tabel: realisasi_kegiatans]
        └── hasMany: BuktiFisikRealisasi (Model) [Tabel: bukti_fisik_realisasis]
```

**Daftar File Proyek yang Terkena Modifikasi:**
1. `app/Models/RealisasiKegiatan.php` $\rightarrow$ Tambahkan logika kalkulasi terpusat `recalculateRealisasiFisik()`.
2. `app/Models/BuktiFisikRealisasi.php` $\rightarrow$ Daftarkan Eloquent Observer / Model Events (`saved`, `deleted`).
3. `app/Filament/Resources/RenjaSkpds/RelationManagers/RealisasiKegiatanRelationManager.php` $\rightarrow$ Kunci input `realisasi_fisik` (*read-only*), perjelas tampilan UI, dan tambahkan aksi hitung ulang manual.
4. `app/Filament/Resources/RealisasiKegiatans/RelationManagers/BuktiFisikRelationManager.php` $\rightarrow$ Panggil trigger kalkulasi saat aksi verifikasi, tambah, atau hapus indikator.
5. `app/Filament/Resources/RealisasiKegiatans/Schemas/RealisasiKegiatanForm.php` $\rightarrow$ Pastikan form edit realisasi kegiatan juga mengunci input `realisasi_fisik`.
6. *(Opsional)* `database/migrations/xxxx_xx_xx_xxxxxx_add_bobot_to_bukti_fisik_realisasis_table.php` $\rightarrow$ Jika memilih Pendekatan B.

---

## 4. Langkah-Langkah Eksekusi (Implementation Steps)

### Langkah 1: Tambahkan Method Kalkulasi di Model `RealisasiKegiatan`
Buka file `app/Models/RealisasiKegiatan.php`. Tambahkan fungsi terpusat untuk menghitung persentase realisasi fisik berdasarkan relasi `bukti_fisik`:

```php
// app/Models/RealisasiKegiatan.php

/**
 * Hitung dan sinkronkan realisasi fisik berdasarkan status persetujuan bukti fisik.
 */
public function recalculateRealisasiFisik(): void
{
    $totalIndikator = $this->bukti_fisik()->count();
    $targetFisik = (float) ($this->target_fisik ?? 0);

    if ($totalIndikator === 0 || $targetFisik <= 0) {
        $this->updateQuietly(['realisasi_fisik' => 0]);
        return;
    }

    // Periksa apakah terdapat kolom bobot (Pendekatan B) atau gunakan hitungan standar (Pendekatan A)
    $hasBobot = \Illuminate\Support\Facades\Schema::hasColumn('bukti_fisik_realisasis', 'bobot');

    if ($hasBobot && $this->bukti_fisik()->whereNotNull('bobot')->exists()) {
        $totalBobot = (float) $this->bukti_fisik()->sum('bobot');
        $bobotDisetujui = (float) $this->bukti_fisik()
            ->where('status_verifikasi', 'disetujui')
            ->sum('bobot');

        $persentaseCapaian = $totalBobot > 0 ? ($bobotDisetujui / $totalBobot) : 0;
    } else {
        // Pendekatan A: Bobot rata per jumlah indikator
        $indikatorDisetujui = $this->bukti_fisik()
            ->where('status_verifikasi', 'disetujui')
            ->count();

        $persentaseCapaian = $indikatorDisetujui / $totalIndikator;
    }

    $realisasiFisik = round($persentaseCapaian * $targetFisik, 2);

    // Pastikan tidak melebihi batas 100%
    $realisasiFisik = min(100.00, max(0.00, $realisasiFisik));

    $this->updateQuietly(['realisasi_fisik' => $realisasiFisik]);
}
```

---

### Langkah 2: Buat Model Hook / Observer pada `BuktiFisikRealisasi`
Buka file `app/Models/BuktiFisikRealisasi.php`. Tambahkan lifecycle event agar setiap kali bukti fisik dibuat, diperbarui (misalnya diverifikasi), atau dihapus, model induk otomatis melakukan perhitungan ulang:

```php
// app/Models/BuktiFisikRealisasi.php

protected static function booted()
{
    parent::booted();

    // Hapus file saat record dihapus
    static::deleting(function ($model) {
        if ($model->file_bukti && \Illuminate\Support\Facades\Storage::disk('public')->exists($model->file_bukti)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($model->file_bukti);
        }
    });

    // Otomatis sinkronkan realisasi fisik pada RealisasiKegiatan
    static::saved(function ($model) {
        $model->realisasi_kegiatan?->recalculateRealisasiFisik();
    });

    static::deleted(function ($model) {
        $model->realisasi_kegiatan?->recalculateRealisasiFisik();
    });
}
```

---

### Langkah 3: Modifikasi Form & Tabel di `RealisasiKegiatanRelationManager`
Buka file `app/Filament/Resources/RenjaSkpds/RelationManagers/RealisasiKegiatanRelationManager.php`.

#### 3.1. Kunci Input `realisasi_fisik` di Form Modal
Ubah definisi komponen `realisasi_fisik`:
```php
TextInput::make('realisasi_fisik')
    ->label('Realisasi Fisik (%)')
    ->numeric()
    ->default(0)
    ->suffix('%')
    ->disabled() // TIDAK BISA DIISI MANUAL
    ->dehydrated() // Tetap simpan nilai ke database jika ada mutasi
    ->helperText('Nilai ini terhitung otomatis berdasarkan indikator fisik & bukti yang telah berstatus Disetujui.')
    ->hint(function (?RealisasiKegiatan $record) {
        if (!$record) return null;
        $total = $record->bukti_fisik()->count();
        $approved = $record->bukti_fisik()->where('status_verifikasi', 'disetujui')->count();
        return "Disetujui: {$approved}/{$total} Indikator";
    }),
```

#### 3.2. Perbarui `target_fisik` agar Memicu Recalculate saat Diedit
Pastikan saat `target_fisik` triwulan diubah dan disimpan, nilai realisasi fisik disesuaikan dengan target baru:
```php
TextInput::make('target_fisik')
    ->label('Target Fisik (%)')
    ->numeric()
    ->default(0)
    ->minValue(0)
    ->maxValue(100)
    ->suffix('%')
    ->required()
    ->live(onBlur: true),
```
Tambahkan hook pada saat form modal disimpan (`saved` / `afterSave`) atau di Relation Manager untuk memanggil `$record->recalculateRealisasiFisik()`.

#### 3.3. Tampilkan Informasi Detail di Tabel Triwulan
Pada `table(Table $table)`:
```php
TextColumn::make('realisasi_fisik')
    ->label('Realisasi Fisik')
    ->suffix('%')
    ->sortable()
    ->description(function (RealisasiKegiatan $record): string {
        $total = $record->bukti_fisik()->count();
        $approved = $record->bukti_fisik()->where('status_verifikasi', 'disetujui')->count();
        if ($total === 0) return 'Belum ada indikator';
        $capaian = round(($approved / $total) * 100);
        return "Capaian: {$capaian}% ({$approved}/{$total} Disetujui)";
    }),
```

#### 3.4. Tambahkan Action Manual Sync di Tabel Triwulan (Opsional tapi Direkomendasikan)
Tambahkan aksi di dalam `recordActions`:
```php
\Filament\Actions\Action::make('hitung_ulang')
    ->label('Hitung Ulang')
    ->icon('heroicon-o-calculator')
    ->color('secondary')
    ->action(function (RealisasiKegiatan $record) {
        $record->recalculateRealisasiFisik();
        \Filament\Notifications\Notification::make()
            ->title('Realisasi fisik berhasil dihitung ulang')
            ->success()
            ->send();
    }),
```

---

### Langkah 4: Sinkronisasi Reaktif pada `BuktiFisikRelationManager`
Buka file `app/Filament/Resources/RealisasiKegiatans/RelationManagers/BuktiFisikRelationManager.php`.

Pada aksi `verifikasi`:
```php
Action::make('verifikasi')
    ->label('Verifikasi')
    ->icon('heroicon-o-check-circle')
    ->color('success')
    ->form([
        Select::make('status_verifikasi')
            ->label('Keputusan')
            ->options([
                'disetujui' => 'Setujui',
                'ditolak' => 'Tolak',
            ])
            ->required(),
        Textarea::make('catatan_verifikasi')
            ->label('Catatan Verifikasi (Wajib jika ditolak)')
            ->required(fn (callable $get) => $get('status_verifikasi') === 'ditolak'),
    ])
    ->action(function (BuktiFisikRealisasi $record, array $data): void {
        $record->update([
            'status_verifikasi' => $data['status_verifikasi'],
            'catatan_verifikasi' => $data['catatan_verifikasi'],
        ]);

        // Trigger sinkronisasi nilai ke model induk
        $parent = $record->realisasi_kegiatan;
        if ($parent) {
            $parent->recalculateRealisasiFisik();
        }

        \Filament\Notifications\Notification::make()
            ->title('Status verifikasi berhasil disimpan dan realisasi fisik telah diperbarui.')
            ->success()
            ->send();
    })
    ->visible(fn (BuktiFisikRealisasi $record): bool => Auth::user()->role !== 'SKPD'),
```

---

### Langkah 5: Sinkronisasi pada Form Edit Standalone (`RealisasiKegiatanForm`)
Buka file `app/Filament/Resources/RealisasiKegiatans/Schemas/RealisasiKegiatanForm.php`.
Jika halaman `RealisasiKegiatanResource` memiliki schema form sendiri, pastikan field `realisasi_fisik` juga diterapkan dengan properti `disabled()` dan deskripsi panduan yang sama, agar tidak dapat dimanipulasi dari halaman detail kegiatan.

---

## 5. Rencana Pengujian (Testing & Verification Checklist)

Model lain atau developer yang mengeksekusi rencana ini harus memvalidasi skenario berikut:

| No | Skenario Uji | Tindakan | Ekspektasi Hasil |
|---|---|---|---|
| 1 | **Kunci Input Manual** | Buka form Create/Edit Realisasi Triwulan di Renja SKPD. | Input `realisasi_fisik` terkunci (disabled/read-only), tidak bisa diketik angka manual. |
| 2 | **Kondisi Tanpa Indikator** | Buat Triwulan baru dengan target fisik 25%, belum ada indikator bukti. | `realisasi_fisik` bernilai `0.00%`. |
| 3 | **Persetujuan Penuh (100%)** | Input 5 indikator bukti pada Triwulan 1 (Target 20%). Verifikasi kelimanya menjadi `disetujui`. | `realisasi_fisik` otomatis berubah menjadi `20.00%`. |
| 4 | **Persetujuan Sebagian (80%)** | Dari 5 indikator, 4 `disetujui` dan 1 `ditolak` / `menunggu`. | `realisasi_fisik` otomatis menjadi `16.00%` (80% dari target 20%). |
| 5 | **Perubahan Status (Reaktif)** | Ubah status salah satu indikator dari `disetujui` menjadi `ditolak`. | `realisasi_fisik` langsung berkurang secara proporsional seketika. |
| 6 | **Penghapusan Indikator** | Hapus salah satu indikator. | Sistem otomatis menghitung ulang berdasarkan total sisa indikator yang ada. |
| 7 | **Perubahan Target Fisik** | Ubah `target_fisik` triwulan dari 20% menjadi 30% saat 4 dari 5 indikator disetujui. | `realisasi_fisik` otomatis menyesuaikan: $\frac{4}{5} \times 30\% = 24.00\%$. |

---

## 6. Catatan Khusus untuk Developer / AI Agent Pelaksana
1. **Preserve Compatibility:** Jangan merusak fitur yang sudah ada di `RenjaSkpdResource` maupun `RealisasiKegiatanResource`.
2. **Hindari Infinite Loops:** Saat menggunakan model observer pada `BuktiFisikRealisasi`, gunakan `$this->updateQuietly(['realisasi_fisik' => ...])` pada model `RealisasiKegiatan` agar tidak memicu event observer yang berulang-ulang tanpa henti.
3. **Migrasi Data Lama (Data Backfill):** Buat artisan command atau tinker script sederhana untuk menghitung ulang data `realisasi_fisik` yang sudah ada sebelumnya agar sinkron dengan formula baru:
   ```php
   App\Models\RealisasiKegiatan::with('bukti_fisik')->get()->each->recalculateRealisasiFisik();
   ```