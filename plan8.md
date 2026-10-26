# Plan 8 — Realisasi Renja SKPD Berbasis Agregat Triwulan

## Audit Kondisi Saat Ini

### ✅ Yang Sudah Ada (Tidak Perlu Diubah)

#### Tombol "Isi Anggaran" di `RenjaSkpdsTable.php`
Sudah ada `Action::make('isiAnggaran')` yang memungkinkan pengisian:
- `pagu_anggaran` (Rp)
- `target_keuangan` (Rp)
- `target_fisik` (%)
- `target_kinerja` (teks)

> **Ini sudah menjawab kebutuhan "saat buat renja baru, ada target fisik dan keuangan".**
> Alur yang berlaku: buat renja (nomenklatur saja) → lalu klik tombol "Isi Anggaran" untuk mengisi target.

#### Kolom di Tabel Listing Renja (`RenjaSkpdsTable.php`)
Sudah ditampilkan: `pagu_anggaran`, `target_keuangan`, `target_fisik`, `target_kinerja`.

#### Struktur Database (`realisasi_kegiatans`)
Sudah ada per-triwulan: `target_keuangan`, `target_fisik`, `realisasi_keuangan`, `realisasi_fisik`.

#### `LaporanRealisasi.php`
Sudah menggunakan data triwulan dengan benar: ada kalkulasi kumulatif (TW 1 s.d. TW terpilih) dan per-triwulan terpilih.

---

### ❌ Yang Belum Ada / Perlu Diperbaiki

#### 1. Realisasi di Level Renja Belum Dihitung dengan Benar

**Masalah di `DashboardRepository.php` — method `getAllSkpdRenjaSummary`:**

```php
// Saat ini (SALAH untuk fisik):
$sumRealisasiFisik += (float) $renja->realisasi_kegiatans->sum('realisasi_fisik');
```

Ini menjumlahkan `realisasi_fisik` dari semua triwulan, padahal setiap triwulan punya targetnya sendiri. Jika ada 4 triwulan masing-masing 25%, totalnya 100% — tapi itu bukan realisasi, itu penjumlahan target!

**Masalah di `getSubKegiatanRenjaBySkpd`:**
```php
// Saat ini:
'realisasi_fisik' => (float) $renja->realisasi_kegiatans->sum('realisasi_fisik'),
```
Sama — sum dari semua triwulan, bukan rata-rata.

#### 2. Model `RenjaSkpd` Tidak Punya Accessor Realisasi
Tidak ada method/accessor untuk `realisasi_fisik` dan `realisasi_keuangan` yang diturunkan dari triwulan.

---

## Logika Perhitungan yang Benar

### Konsep

> **Realisasi di level Renja = turunan dari data Triwulan, bukan diisi manual.**

| Data | Cara Hitung |
|---|---|
| `realisasi_fisik_renja` | Rata-rata `realisasi_fisik` dari semua triwulan yang ada |
| `realisasi_keuangan_renja` | Total/sum `realisasi_keuangan` dari semua triwulan yang ada |
| `persentase_fisik` | `realisasi_fisik_renja / target_fisik_renja × 100` |
| `persentase_keuangan` | `realisasi_keuangan_renja / target_keuangan_renja × 100` |

### Formula

```
$triwulans = $renja->realisasi_kegiatans;  // semua triwulan yang ada
$count     = $triwulans->count();

// Realisasi fisik = rata-rata dari semua triwulan
$realisasi_fisik_renja    = $count > 0 ? round($triwulans->sum('realisasi_fisik') / $count, 2) : 0;

// Realisasi keuangan = total akumulasi dari semua triwulan
$realisasi_keuangan_renja = (float) $triwulans->sum('realisasi_keuangan');
```

### Ilustrasi Kasus

**Skenario A — Hanya ada 1 Triwulan (TW I):**
- Renja: `target_fisik = 100%`, `target_keuangan = Rp 500jt`
- TW I: `target_fisik = 100%`, `realisasi_fisik = 100%`, `realisasi_keuangan = Rp 500jt`

```
realisasi_fisik_renja    = 100 / 1 = 100%
realisasi_keuangan_renja = Rp 500jt
persentase_fisik         = 100 / 100 × 100 = 100% ✅
persentase_keuangan      = 500jt / 500jt × 100 = 100% ✅
```

**Skenario B — 2 Triwulan sudah diinput (TW I & TW II):**
- Renja: `target_fisik = 100%`, `target_keuangan = Rp 500jt`
- TW I: `realisasi_fisik = 50%`, `realisasi_keuangan = Rp 250jt`
- TW II: `realisasi_fisik = 50%`, `realisasi_keuangan = Rp 250jt`

```
realisasi_fisik_renja    = (50 + 50) / 2 = 50%
realisasi_keuangan_renja = Rp 500jt
persentase_fisik         = 50 / 100 × 100 = 50%  (karena baru TW I & II)
persentase_keuangan      = 500jt / 500jt × 100 = 100% ✅
```

**Skenario C — 4 Triwulan lengkap:**
- TW I–IV: masing-masing `realisasi_fisik = 25%`, `realisasi_keuangan = Rp 125jt`

```
realisasi_fisik_renja    = (25+25+25+25) / 4 = 25%
```

> **Catatan:** Ini bisa jadi kurang tepat kalau target per TW berbeda-beda. Lihat alternatif di bagian bawah.

---

## Perubahan yang Diperlukan

### 1. Model `RenjaSkpd` — Tambah Eloquent Accessor

**File:** [`app/Models/RenjaSkpd.php`](file:///Users/mac/Herd/diskominfo_plan/app/Models/RenjaSkpd.php)

Tambahkan accessor berikut:

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Realisasi fisik renja = rata-rata realisasi_fisik dari semua triwulan.
 */
protected function realisasiFisik(): Attribute
{
    return Attribute::make(
        get: function () {
            $triwulans = $this->realisasi_kegiatans;
            $count = $triwulans->count();
            if ($count === 0) return 0.0;
            return round($triwulans->sum('realisasi_fisik') / $count, 2);
        }
    );
}

/**
 * Realisasi keuangan renja = total akumulasi dari semua triwulan.
 */
protected function realisasiKeuangan(): Attribute
{
    return Attribute::make(
        get: fn() => (float) $this->realisasi_kegiatans->sum('realisasi_keuangan')
    );
}

/**
 * Persentase capaian fisik terhadap target renja.
 */
protected function persentaseFisik(): Attribute
{
    return Attribute::make(
        get: function () {
            $target = (float) ($this->target_fisik ?? 0);
            return $target > 0 ? round(($this->realisasi_fisik / $target) * 100, 2) : 0.0;
        }
    );
}

/**
 * Persentase capaian keuangan terhadap target renja.
 */
protected function persentaseKeuangan(): Attribute
{
    return Attribute::make(
        get: function () {
            $target = (float) ($this->target_keuangan ?? 0);
            return $target > 0 ? round(($this->realisasi_keuangan / $target) * 100, 2) : 0.0;
        }
    );
}
```

> **Penting:** Accessor ini hanya bekerja jika relasi `realisasi_kegiatans` sudah di-load (`with('realisasi_kegiatans')`). Pastikan eager load dilakukan di query yang memanggil renja.

---

### 2. `DashboardRepository` — Perbaiki Kalkulasi Fisik

**File:** [`app/Repositories/Eloquent/DashboardRepository.php`](file:///Users/mac/Herd/diskominfo_plan/app/Repositories/Eloquent/DashboardRepository.php)

#### Method `getAllSkpdRenjaSummary` (sekitar baris 210–216)

**Sebelum:**
```php
foreach ($renjas as $renja) {
    $target += (float) $renja->target_keuangan;
    $realisasi += (float) $renja->realisasi_kegiatans->sum('realisasi_keuangan');

    $sumTargetFisik += (float) $renja->target_fisik;
    $sumRealisasiFisik += (float) $renja->realisasi_kegiatans->sum('realisasi_fisik');
}
```

**Sesudah:**
```php
foreach ($renjas as $renja) {
    $target    += (float) $renja->target_keuangan;
    $realisasi += (float) $renja->realisasi_kegiatans->sum('realisasi_keuangan');

    $sumTargetFisik += (float) $renja->target_fisik;

    // Realisasi fisik per renja = RATA-RATA dari semua triwulan (bukan sum)
    $countTw = $renja->realisasi_kegiatans->count();
    $sumRealisasiFisik += $countTw > 0
        ? round($renja->realisasi_kegiatans->sum('realisasi_fisik') / $countTw, 2)
        : 0;
}
```

#### Method `getSubKegiatanRenjaBySkpd` (sekitar baris 264–278)

**Sebelum:**
```php
->map(function ($renja) use ($skpdId) {
    $realisasi = (float) $renja->realisasi_kegiatans->sum('realisasi_keuangan');
    $target    = (float) $renja->target_keuangan;
    $persentase = $target > 0 ? round(($realisasi / $target) * 100, 2) : 0;

    return [
        // ...
        'target_fisik'      => (float) $renja->target_fisik,
        'realisasi_fisik'   => (float) $renja->realisasi_kegiatans->sum('realisasi_fisik'),
    ];
})
```

**Sesudah:**
```php
->map(function ($renja) use ($skpdId) {
    $realisasiKeuangan = (float) $renja->realisasi_kegiatans->sum('realisasi_keuangan');
    $target            = (float) $renja->target_keuangan;
    $persentase        = $target > 0 ? round(($realisasiKeuangan / $target) * 100, 2) : 0;

    // Realisasi fisik = rata-rata dari semua triwulan
    $countTw       = $renja->realisasi_kegiatans->count();
    $realisasiFisik = $countTw > 0
        ? round($renja->realisasi_kegiatans->sum('realisasi_fisik') / $countTw, 2)
        : 0.0;

    return [
        'kode_rekening'      => \App\Models\Skpd::formatDisplayKode($renja->subKegiatan->kode_lengkap ?? '-', $skpdId),
        'nama_rekening'      => \App\Models\Skpd::formatDisplayNomenklatur($renja->subKegiatan->nomenklatur ?? '-', $skpdId),
        'target'             => $target,
        'realisasi'          => $realisasiKeuangan,
        'selisih'            => $target - $realisasiKeuangan,
        'persentase'         => $persentase,
        'target_fisik'       => (float) $renja->target_fisik,
        'realisasi_fisik'    => $realisasiFisik,
        'persentase_fisik'   => $renja->target_fisik > 0
            ? round(($realisasiFisik / $renja->target_fisik) * 100, 2)
            : 0,
    ];
})
```

---

## Alur Data Lengkap (Setelah Perubahan)

```
[1. BUAT RENJA]
   Pilih: SKPD, Tahun, Sub Kegiatan
   → Simpan ke renja_skpds (target_fisik & target_keuangan kosong/0)

[2. ISI ANGGARAN] ← Tombol sudah ada ✅
   Input: pagu_anggaran, target_keuangan, target_fisik (%), target_kinerja
   → Update renja_skpds

[3. INPUT REALISASI TRIWULAN] ← Sudah ada di RelationManager ✅
   Pilih triwulan (1/2/3/4)
   Input: target_keuangan, target_fisik (%) untuk triwulan ini
   Input: realisasi_keuangan
   realisasi_fisik ← otomatis dari bukti fisik yang disetujui ✅
   → Simpan ke realisasi_kegiatans

[4. TAMPILAN/LAPORAN RENJA] ← Yang perlu diperbaiki ❌→✅
   realisasi_fisik_renja   = SUM(realisasi_fisik tiap TW) / COUNT(TW)
   realisasi_keuangan_renja = SUM(realisasi_keuangan tiap TW)
   persentase_fisik         = realisasi_fisik_renja / target_fisik_renja × 100
   persentase_keuangan      = realisasi_keuangan_renja / target_keuangan_renja × 100
```

---

## File yang Perlu Diubah

| File | Perubahan | Status |
|---|---|---|
| [`app/Models/RenjaSkpd.php`](file:///Users/mac/Herd/diskominfo_plan/app/Models/RenjaSkpd.php) | Tambah Eloquent accessor `realisasi_fisik`, `realisasi_keuangan`, `persentase_fisik`, `persentase_keuangan` | ❌ Belum |
| [`app/Repositories/Eloquent/DashboardRepository.php`](file:///Users/mac/Herd/diskominfo_plan/app/Repositories/Eloquent/DashboardRepository.php) | Ubah kalkulasi fisik dari `sum` → `rata-rata` di dua method | ❌ Belum |

## File yang TIDAK Perlu Diubah

| File | Alasan |
|---|---|
| [`RenjaSkpdsTable.php`](file:///Users/mac/Herd/diskominfo_plan/app/Filament/Resources/RenjaSkpds/Tables/RenjaSkpdsTable.php) | Tombol "Isi Anggaran" sudah ada dan berfungsi ✅ |
| [`RenjaSkpdForm.php`](file:///Users/mac/Herd/diskominfo_plan/app/Filament/Resources/RenjaSkpds/Schemas/RenjaSkpdForm.php) | Form create cukup untuk nomenklatur dulu ✅ |
| [`RealisasiKegiatanRelationManager.php`](file:///Users/mac/Herd/diskominfo_plan/app/Filament/Resources/RenjaSkpds/RelationManagers/RealisasiKegiatanRelationManager.php) | Sudah ada target & realisasi per triwulan ✅ |
| [`RealisasiKegiatan.php`](file:///Users/mac/Herd/diskominfo_plan/app/Models/RealisasiKegiatan.php) | `recalculateRealisasiFisik()` sudah benar ✅ |
| [`LaporanRealisasi.php`](file:///Users/mac/Herd/diskominfo_plan/app/Filament/Pages/LaporanRealisasi.php) | Sudah pakai data triwulan langsung ✅ |
| Semua migration | Tidak perlu migration baru ✅ |

---

## Update Tambahan: Verifikasi Triwulan & Bukti Fisik Opsional

### 1. Hanya Data Triwulan yang Terverifikasi (`is_verified = true`) yang Masuk ke Renja
- Pada level Renja (`RenjaSkpd` & `DashboardRepository`), data triwulan hanya diperhitungkan jika berstatus **`is_verified = true`**.
- Jika triwulan belum diverifikasi (`is_verified = false`), data tersebut dianggap draf dan belum menaikkan realisasi fisik maupun keuangan Renja.
- Verifikator dapat melakukan verifikasi (`is_verified = true`) atau pembatalan verifikasi (`is_verified = false`) melalui tombol aksi pada tabel maupun form di `RealisasiKegiatanRelationManager`.

### 2. Bukti Fisik Bersifat Opsional
- Upload dokumen PDF bukti fisik pada `BuktiFisikRelationManager` kini bersifat **opsional** (`nullable()`).
- Data realisasi triwulan (`realisasi_fisik`) naik berdasarkan status verifikasi dari verifikator (`disetujui`).
- Apabila belum/tidak ada rincian indikator bukti fisik yang diunggah, nilai realisasi fisik pada triwulan dapat diinput langsung secara manual tanpa di-reset paksa ke 0.


### Pilihan Alternatif Formula Fisik (Diskusi)

Rata-rata sederhana cocok jika **target fisik per triwulan relatif sama (merata)**. Jika target fisik per triwulan tidak merata (misal TW IV lebih besar), pertimbangkan **weighted average**:

```
// Weighted average berdasarkan target fisik per triwulan
$totalTargetTw   = $triwulans->sum('target_fisik');
$weightedReal    = $triwulans->sum(fn($tw) => $tw->realisasi_fisik * ($tw->target_fisik / max($totalTargetTw, 1)));
$realisasiFisik  = round($weightedReal, 2);
```

Namun berdasarkan pembicaraan dengan user, **rata-rata sederhana** (SUM / COUNT) sudah cukup dan sesuai dengan ekspektasi.
