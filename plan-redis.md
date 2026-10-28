# Plan Implementasi Redis Cache (Cache-Aside Pattern)

> **Konteks:** Project Laravel Filament — Sistem Perencanaan & Evaluasi Diskominfo  
> **Metode:** Cache-Aside (Lazy Loading)  
> **Driver:** Redis (sudah terkonfigurasi di `.env`: `CACHE_STORE=redis`)

---

## 1. Analisis Data yang Direkomendasikan di-Cache

Berikut analisis berdasarkan model dan pola penggunaan di project ini:

### 🟢 Tier 1 — TTL 60 Menit (Data Master / Jarang Berubah)

| Data | Model | Alasan |
|------|-------|--------|
| Daftar Urusan | `ProgramUrusan` | Referensi struktural, hampir tidak pernah berubah setelah seeding |
| Daftar Bidang | `ProgramBidang` | Referensi nomenklatur bidang, statis |
| Daftar Program | `ProgramProgram` | Hierarki program, jarang berubah |
| Daftar Kegiatan | `ProgramKegiatan` | Referensi kegiatan, jarang berubah |
| Daftar Sub Kegiatan | `ProgramSubKegiatan` | Dipakai di banyak dropdown (`RenjaSkpd`, `ProgulIndikator`), statis |
| Daftar SKPD | `Skpd` | Data organisasi, hampir tidak berubah |
| Daftar SKPD + Bidang | `Skpd::with('bidang')` | Dipakai di `Skpd::formatDisplayKode()` & `formatDisplayNomenklatur()` setiap render baris tabel |
| Kategori Indikator | `KategoriIndikator` | Lookup sederhana, statis |
| Satuan Indikator | `SatuanIndikator` | Lookup sederhana, statis |
| Master Indikator | `MasterIndikator` | Data IKU/Penyelenggaraan, hanya berubah saat input baru |
| Rekening | `Rekening` | Data rekening anggaran, sangat statis |
| Program Unggulan (Progul) | `ProgulProgram` + `ProgulIndikator` | Data program unggulan, jarang diubah admin |
| App Setting | `AppSetting` | **Sudah ada** (`app_setting_data`, 24 jam) — pertahankan |
| Laporan Dashboard Repo | `DashboardRepository` | Query berat (sum, count lintas tabel), cocok di-cache per parameter |

### 🟡 Tier 2 — TTL 10 Menit (Data Semi-Dinamis / Ada Filter/Pagination)

| Data | Model | Alasan |
|------|-------|--------|
| Daftar Renja SKPD | `RenjaSkpd` | Ada filter `skpd_id` + `tahun`, diubah saat input anggaran |
| Realisasi Kegiatan | `RealisasiKegiatan` | Diupdate per triwulan oleh SKPD, tapi sering di-read |
| Indikator Realisasi | `IndikatorRealisasi` | Status berubah saat submit/verifikasi |
| Target Pendapatan | `TargetPendapatan` | Berubah per tahun, tapi di-read sering |
| Transaksi Pendapatan | `TransaksiPendapatan` | Bertransaksi aktif tapi aggregate jarang berubah instan |
| Data Dashboard (per tahun) | `DashboardRepository` | Aggregate berat, parameter: `tahun`, `triwulan`, `skpd_id` |
| Progul Realisasi | `ProgulRealisasi` | Diinput per triwulan, di-read sering untuk laporan |

---

## 2. Strategi Cache Key

Gunakan format: `{entity}:{parameter_hash_atau_id}`

```
skpds:all
skpds:with_bidang
program_urusans:all
program_bidangs:all
program_programs:all
program_kegiatans:all
program_sub_kegiatans:all
kategori_indikators:all
satuan_indikators:all
master_indikators:all
master_indikators:iku
master_indikators:penyelenggaraan
rekenings:all
progul_programs:with_indikators
progul_indikators:all

renja_skpds:{skpd_id}:{tahun}
realisasi_kegiatans:{renja_skpd_id}
indikator_realisasis:{skpd_id}:{tahun}
target_pendapatans:{skpd_id}:{tahun}
transaksi_pendapatans:{tahun}
dashboard:skpd_count
dashboard:realisasi_keuangan:{tahun}
dashboard:chart_pendapatan:{tahun}
dashboard:skpd_pendapatan_summary:{tahun}
dashboard:skpd_pad_summary:{tahun}
progul_realisasis:{indikator_id}:{tahun}
```

---

## 3. Implementasi

### 3.1 Buat `CacheService` Terpusat

Buat file: `app/Services/CacheService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    // TTL Constants (dalam detik)
    const TTL_STATIC  = 3600;   // 60 menit — data master statis
    const TTL_DYNAMIC = 600;    // 10 menit — data semi-dinamis

    /**
     * Helper: get or cache data.
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Hapus satu atau banyak cache key sekaligus.
     */
    public static function forget(string|array $keys): void
    {
        foreach ((array) $keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Hapus semua cache dengan prefix tertentu menggunakan tag (jika driver mendukung).
     * Karena Redis mendukung tags, gunakan ini untuk group invalidation.
     */
    public static function flushTag(string $tag): void
    {
        Cache::tags([$tag])->flush();
    }
}
```

---

### 3.2 Implementasi Cache di Model (via `booted()` + static methods)

#### `app/Models/Skpd.php`

Tambahkan di dalam class:

```php
use Illuminate\Support\Facades\Cache;

// Di dalam booted():
protected static function booted(): void
{
    // ... existing code ...

    static::saved(function () {
        Cache::forget('skpds:all');
        Cache::forget('skpds:with_bidang');
    });

    static::deleted(function () {
        Cache::forget('skpds:all');
        Cache::forget('skpds:with_bidang');
    });
}

/**
 * Ambil semua SKPD (cached 60 menit).
 */
public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('skpds:all', 3600, fn() => static::orderBy('nama_skpd')->get());
}

/**
 * Ambil semua SKPD dengan relasi bidang (cached 60 menit).
 */
public static function getAllWithBidangCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('skpds:with_bidang', 3600, fn() => static::with('bidang')->orderBy('nama_skpd')->get());
}

// Ganti static $skpdCache pada formatDisplayKode & formatDisplayNomenklatur
// dengan memanfaatkan getAllWithBidangCached() agar cache berbasis Redis:
// Contoh di formatDisplayKode():
//   $skpdObj = static::getAllWithBidangCached()->firstWhere('id', $skpdId);
```

> **Catatan:** Pada `formatDisplayKode()` dan `formatDisplayNomenklatur()`, saat ini sudah ada `static $skpdCache = []` (in-memory per request). Ganti lookupnya dengan `static::getAllWithBidangCached()->firstWhere('id', $skpdId)` agar memanfaatkan Redis cache antar-request.

---

#### `app/Models/KategoriIndikator.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    static::saved(fn()    => Cache::forget('kategori_indikators:all'));
    static::deleted(fn()  => Cache::forget('kategori_indikators:all'));
}

public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('kategori_indikators:all', 3600, fn() => static::orderBy('nama_kategori')->get());
}
```

---

#### `app/Models/SatuanIndikator.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    static::saved(fn()   => Cache::forget('satuan_indikators:all'));
    static::deleted(fn() => Cache::forget('satuan_indikators:all'));
}

public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('satuan_indikators:all', 3600, fn() => static::orderBy('nama_satuan')->get());
}
```

---

#### `app/Models/ProgramUrusan.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    static::saved(fn()   => Cache::forget('program_urusans:all'));
    static::deleted(fn() => Cache::forget('program_urusans:all'));
}

public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('program_urusans:all', 3600, fn() => static::orderBy('kode')->get());
}
```

---

#### `app/Models/ProgramBidang.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    // existing creating() tetap ada ...
    static::saved(fn()   => Cache::forget('program_bidangs:all'));
    static::deleted(fn() => Cache::forget('program_bidangs:all'));
}

public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('program_bidangs:all', 3600, fn() => static::with('urusan')->orderBy('kode_lengkap')->get());
}
```

---

#### `app/Models/ProgramProgram.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    // existing creating() tetap ada ...
    static::saved(fn()   => Cache::forget('program_programs:all'));
    static::deleted(fn() => Cache::forget('program_programs:all'));
}

public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('program_programs:all', 3600, fn() => static::with('bidang.urusan')->orderBy('kode_lengkap')->get());
}
```

---

#### `app/Models/ProgramKegiatan.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    // existing creating() tetap ada ...
    static::saved(fn()   => Cache::forget('program_kegiatans:all'));
    static::deleted(fn() => Cache::forget('program_kegiatans:all'));
}

public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('program_kegiatans:all', 3600, fn() => static::with('program.bidang')->orderBy('kode_lengkap')->get());
}
```

---

#### `app/Models/ProgramSubKegiatan.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    // existing creating() tetap ada ...
    static::saved(fn()   => Cache::forget('program_sub_kegiatans:all'));
    static::deleted(fn() => Cache::forget('program_sub_kegiatans:all'));
}

public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('program_sub_kegiatans:all', 3600, fn() => static::with('kegiatan.program.bidang')->orderBy('kode_lengkap')->get());
}
```

---

#### `app/Models/Rekening.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    static::saved(fn()   => Cache::forget('rekenings:all'));
    static::deleted(fn() => Cache::forget('rekenings:all'));
}

public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('rekenings:all', 3600, fn() => static::orderBy('kode')->get());
}
```

---

#### `app/Models/MasterIndikator.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    // existing updated() tetap ada ...

    static::saved(function () {
        Cache::forget('master_indikators:all');
        Cache::forget('master_indikators:iku');
        Cache::forget('master_indikators:penyelenggaraan');
    });

    static::deleted(function () {
        Cache::forget('master_indikators:all');
        Cache::forget('master_indikators:iku');
        Cache::forget('master_indikators:penyelenggaraan');
    });
}

/**
 * Ambil semua master indikator untuk laporan IKU (cached 60 menit).
 */
public static function getIkuCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('master_indikators:iku', 3600, function () {
        $kategoriIku = \App\Models\KategoriIndikator::where('nama_kategori', 'IKU')->value('id');
        return static::with(['satuan', 'skpd', 'target', 'realisasi', 'childIndikators.satuan'])
            ->where('kategori_id', $kategoriIku)
            ->whereNull('parent_id')
            ->orderBy('id')
            ->get();
    });
}

/**
 * Ambil semua master indikator untuk laporan Penyelenggaraan (cached 60 menit).
 */
public static function getPenyelenggaraanCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('master_indikators:penyelenggaraan', 3600, function () {
        $kategoriId = \App\Models\KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');
        return static::with([
            'satuan', 'skpd', 'target', 'realisasi',
            'childIndikators.satuan', 'childIndikators.skpd',
            'childIndikators.target', 'childIndikators.realisasi',
        ])
        ->where('kategori_id', $kategoriId)
        ->whereNull('parent_id')
        ->orderBy('id')
        ->get();
    });
}
```

---

#### `app/Models/ProgulProgram.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    static::saved(fn()   => Cache::forget('progul_programs:with_indikators'));
    static::deleted(fn() => Cache::forget('progul_programs:with_indikators'));
}

public static function getAllWithIndikatorsCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('progul_programs:with_indikators', 3600, fn() =>
        static::with(['indikators.urusan', 'indikators.subKegiatan'])->orderBy('urutan')->get()
    );
}
```

---

#### `app/Models/ProgulIndikator.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    static::saved(function () {
        Cache::forget('progul_indikators:all');
        Cache::forget('progul_programs:with_indikators');
    });

    static::deleted(function () {
        Cache::forget('progul_indikators:all');
        Cache::forget('progul_programs:with_indikators');
    });
}

public static function getAllCached(): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember('progul_indikators:all', 3600, fn() =>
        static::with(['program', 'urusan', 'subKegiatan'])->orderBy('urutan')->get()
    );
}
```

---

### 3.3 Cache untuk Data Semi-Dinamis (TTL 10 Menit)

#### `app/Models/RenjaSkpd.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    // existing code ...

    static::saved(function (RenjaSkpd $renja) {
        Cache::forget("renja_skpds:{$renja->skpd_id}:{$renja->tahun}");
    });

    static::deleted(function (RenjaSkpd $renja) {
        Cache::forget("renja_skpds:{$renja->skpd_id}:{$renja->tahun}");
    });
}

/**
 * Ambil renja per SKPD per tahun (cached 10 menit).
 */
public static function getBySKPDAndTahunCached(int $skpdId, int $tahun): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember("renja_skpds:{$skpdId}:{$tahun}", 600, fn() =>
        static::with(['subKegiatan.kegiatan.program.bidang.urusan', 'realisasi_kegiatans'])
            ->where('skpd_id', $skpdId)
            ->where('tahun', $tahun)
            ->get()
    );
}
```

---

#### `app/Models/RealisasiKegiatan.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    // existing saved() code ...

    static::saved(function (RealisasiKegiatan $model) {
        Cache::forget("realisasi_kegiatans:{$model->renja_skpd_id}");
        // Invalidate juga renja_skpd parent agar statistik fresh
        if ($model->renja_skpd) {
            Cache::forget("renja_skpds:{$model->renja_skpd->skpd_id}:{$model->renja_skpd->tahun}");
        }
    });

    static::deleted(function (RealisasiKegiatan $model) {
        Cache::forget("realisasi_kegiatans:{$model->renja_skpd_id}");
    });
}

/**
 * Ambil realisasi kegiatan per renja_skpd (cached 10 menit).
 */
public static function getByRenjaCached(int $renjaSkpdId): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember("realisasi_kegiatans:{$renjaSkpdId}", 600, fn() =>
        static::with('bukti_fisik')->where('renja_skpd_id', $renjaSkpdId)->get()
    );
}
```

---

#### `app/Models/ProgulRealisasi.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    static::saved(function (ProgulRealisasi $model) {
        Cache::forget("progul_realisasis:{$model->indikator_id}:{$model->tahun}");
    });

    static::deleted(function (ProgulRealisasi $model) {
        Cache::forget("progul_realisasis:{$model->indikator_id}:{$model->tahun}");
    });
}

/**
 * Ambil realisasi progul per indikator per tahun (cached 10 menit).
 */
public static function getByIndikatorAndTahunCached(int $indikatorId, int $tahun): \Illuminate\Database\Eloquent\Collection
{
    return Cache::remember("progul_realisasis:{$indikatorId}:{$tahun}", 600, fn() =>
        static::with(['skpd', 'buktiDukung'])
            ->where('indikator_id', $indikatorId)
            ->where('tahun', $tahun)
            ->orderBy('triwulan')
            ->get()
    );
}
```

---

### 3.4 Cache di `DashboardRepository`

File: `app/Repositories/Eloquent/DashboardRepository.php`

```php
use Illuminate\Support\Facades\Cache;

public function getSkpdCount(): int
{
    return Cache::remember('dashboard:skpd_count', 3600, fn() => Skpd::count());
}

public function getRealisasiKeuanganTotal(): float
{
    $tahun = date('Y');
    return Cache::remember("dashboard:realisasi_keuangan:{$tahun}", 600, fn() =>
        (float) RealisasiKegiatan::where('is_verified', true)->sum('realisasi_keuangan')
    );
}

public function getTargetPendapatanTotal(): float
{
    $tahun = date('Y');
    return Cache::remember("dashboard:target_pendapatan_total:{$tahun}", 600, fn() =>
        (float) TargetPendapatan::sum('target_anggaran')
    );
}

public function getChartDataPendapatan(): array
{
    $tahun = date('Y');
    return Cache::remember("dashboard:chart_pendapatan:{$tahun}", 600, function () {
        $targetTotal    = $this->getTargetPendapatanTotal();
        $realisasiTotal = (float) TransaksiPendapatan::where('is_verif', true)->sum('jumlah_setor');
        return [
            'target'     => $targetTotal,
            'realisasi'  => $realisasiTotal,
            'persentase' => $targetTotal > 0 ? round(($realisasiTotal / $targetTotal) * 100, 2) : 0,
        ];
    });
}

public function getAllSkpdPendapatanSummary(?int $tahun = null): \Illuminate\Support\Collection
{
    $tahun = $tahun ?? date('Y');
    return Cache::remember("dashboard:skpd_pendapatan_summary:{$tahun}", 600, function () use ($tahun) {
        // ... existing implementation ...
    });
}

public function getAllSkpdPadSummary(?int $tahun = null): \Illuminate\Support\Collection
{
    $tahun = $tahun ?? date('Y');
    return Cache::remember("dashboard:skpd_pad_summary:{$tahun}", 600, function () use ($tahun) {
        // ... existing implementation ...
    });
}
```

> **Penting:** Untuk `getRealisasiKeuanganTotal()` dan `getChartDataPendapatan()`, tambahkan juga invalidasi cache di event `TransaksiPendapatan::saved()` dan `RealisasiKegiatan::saved()`.

---

### 3.5 Invalidasi Cache Lintas Model

Beberapa data yang saling mempengaruhi perlu saling invalidasi:

#### `app/Models/IndikatorRealisasi.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    static::saved(function (IndikatorRealisasi $model) {
        // Invalidasi cache master indikator laporan
        Cache::forget('master_indikators:iku');
        Cache::forget('master_indikators:penyelenggaraan');
    });

    static::deleted(function (IndikatorRealisasi $model) {
        Cache::forget('master_indikators:iku');
        Cache::forget('master_indikators:penyelenggaraan');
    });
}
```

#### `app/Models/TransaksiPendapatan.php`

```php
use Illuminate\Support\Facades\Cache;

protected static function booted(): void
{
    static::saved(function () {
        $tahun = date('Y');
        Cache::forget("dashboard:chart_pendapatan:{$tahun}");
        Cache::forget("dashboard:skpd_pendapatan_summary:{$tahun}");
        Cache::forget("dashboard:skpd_pad_summary:{$tahun}");
    });

    static::deleted(function () {
        $tahun = date('Y');
        Cache::forget("dashboard:chart_pendapatan:{$tahun}");
        Cache::forget("dashboard:skpd_pendapatan_summary:{$tahun}");
    });
}
```

---

### 3.6 Penggunaan di `LaporanRealisasi.php` (Filament Page)

Pada `getSkpdListProperty()` dan `getActiveSkpdProperty()`:

```php
public function getSkpdListProperty()
{
    if (Auth::user()?->role === 'SKPD') {
        return Skpd::getAllCached()
            ->where('id', Auth::user()->skpd_id)
            ->pluck('nama_skpd', 'id');
    }

    return Skpd::getAllCached()->pluck('nama_skpd', 'id');
}

public function getActiveSkpdProperty(): ?Skpd
{
    $skpdId = Auth::user()?->role === 'SKPD'
        ? Auth::user()->skpd_id
        : $this->selectedSkpdId;

    return Skpd::getAllWithBidangCached()->firstWhere('id', $skpdId);
}
```

---

## 4. Konfigurasi Redis (sudah ada, pastikan benar)

`.env` sudah terkonfigurasi dengan benar:

```env
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=Ge140019
REDIS_PORT=6379
```

Pastikan di `config/cache.php`, prefix cache tidak kosong agar tidak bertabrakan dengan session:

```php
// config/cache.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'lock_connection' => 'default',
],
```

Dan di `config/database.php`, pisahkan database Redis untuk cache dan session:

```php
'redis' => [
    'default' => [
        'url'      => env('REDIS_URL'),
        'host'     => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port'     => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),          // Session
    ],
    'cache' => [
        'url'      => env('REDIS_URL'),
        'host'     => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port'     => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),    // Cache — database berbeda
    ],
],
```

Tambahkan di `.env`:

```env
REDIS_DB=0
REDIS_CACHE_DB=1
```

---

## 5. Artisan Command untuk Cache Warmup (Opsional)

Buat command untuk pre-populate cache saat deploy:

File: `app/Console/Commands/WarmupCache.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{
    Skpd, KategoriIndikator, SatuanIndikator,
    ProgramUrusan, ProgramBidang, ProgramProgram,
    ProgramKegiatan, ProgramSubKegiatan, Rekening,
    MasterIndikator, ProgulProgram, ProgulIndikator
};

class WarmupCache extends Command
{
    protected $signature   = 'cache:warmup';
    protected $description = 'Pre-populate Redis cache dengan data master';

    public function handle(): int
    {
        $this->info('Warming up cache...');

        Skpd::getAllCached();
        Skpd::getAllWithBidangCached();
        KategoriIndikator::getAllCached();
        SatuanIndikator::getAllCached();
        ProgramUrusan::getAllCached();
        ProgramBidang::getAllCached();
        ProgramProgram::getAllCached();
        ProgramKegiatan::getAllCached();
        ProgramSubKegiatan::getAllCached();
        Rekening::getAllCached();
        MasterIndikator::getIkuCached();
        MasterIndikator::getPenyelenggaraanCached();
        ProgulProgram::getAllWithIndikatorsCached();
        ProgulIndikator::getAllCached();

        $this->info('✅ Cache warmup selesai!');
        return self::SUCCESS;
    }
}
```

Daftarkan di `app/Console/Kernel.php` atau `routes/console.php`:

```php
use App\Console\Commands\WarmupCache;
Artisan::command('cache:warmup', [WarmupCache::class, 'handle']);
```

---

## 6. Ringkasan TTL per Cache Key

| Cache Key | TTL | Invalidasi Trigger |
|-----------|-----|--------------------|
| `app_setting_data` | 24 jam | `AppSetting::saved/deleted` ✅ sudah ada |
| `skpds:all` | 60 menit | `Skpd::saved/deleted` |
| `skpds:with_bidang` | 60 menit | `Skpd::saved/deleted`, `ProgramBidang::saved/deleted` |
| `kategori_indikators:all` | 60 menit | `KategoriIndikator::saved/deleted` |
| `satuan_indikators:all` | 60 menit | `SatuanIndikator::saved/deleted` |
| `program_urusans:all` | 60 menit | `ProgramUrusan::saved/deleted` |
| `program_bidangs:all` | 60 menit | `ProgramBidang::saved/deleted` |
| `program_programs:all` | 60 menit | `ProgramProgram::saved/deleted` |
| `program_kegiatans:all` | 60 menit | `ProgramKegiatan::saved/deleted` |
| `program_sub_kegiatans:all` | 60 menit | `ProgramSubKegiatan::saved/deleted` |
| `rekenings:all` | 60 menit | `Rekening::saved/deleted` |
| `master_indikators:iku` | 60 menit | `MasterIndikator::saved/deleted`, `IndikatorRealisasi::saved` |
| `master_indikators:penyelenggaraan` | 60 menit | `MasterIndikator::saved/deleted`, `IndikatorRealisasi::saved` |
| `progul_programs:with_indikators` | 60 menit | `ProgulProgram::saved`, `ProgulIndikator::saved` |
| `progul_indikators:all` | 60 menit | `ProgulIndikator::saved/deleted` |
| `dashboard:skpd_count` | 60 menit | `Skpd::saved/deleted` |
| `renja_skpds:{skpd_id}:{tahun}` | 10 menit | `RenjaSkpd::saved/deleted` |
| `realisasi_kegiatans:{renja_id}` | 10 menit | `RealisasiKegiatan::saved/deleted` |
| `progul_realisasis:{ind_id}:{tahun}` | 10 menit | `ProgulRealisasi::saved/deleted` |
| `dashboard:realisasi_keuangan:{tahun}` | 10 menit | `RealisasiKegiatan::saved` |
| `dashboard:chart_pendapatan:{tahun}` | 10 menit | `TransaksiPendapatan::saved/deleted` |
| `dashboard:skpd_pendapatan_summary:{tahun}` | 10 menit | `TransaksiPendapatan::saved/deleted` |
| `dashboard:skpd_pad_summary:{tahun}` | 10 menit | `TransaksiPendapatan::saved/deleted` |

---

## 7. Urutan Implementasi (Step-by-Step)

1. **Tambahkan `REDIS_CACHE_DB=1`** di `.env` dan pastikan `config/database.php` memisahkan koneksi `cache` ke DB Redis yang berbeda dari session
2. **Buat `app/Services/CacheService.php`** (opsional, untuk konsistensi)
3. **Implementasi cache di model Tier 1** (data master): `Skpd`, `KategoriIndikator`, `SatuanIndikator`, `ProgramUrusan`, `ProgramBidang`, `ProgramProgram`, `ProgramKegiatan`, `ProgramSubKegiatan`, `Rekening`, `MasterIndikator`, `ProgulProgram`, `ProgulIndikator`
4. **Implementasi cache di model Tier 2** (data semi-dinamis): `RenjaSkpd`, `RealisasiKegiatan`, `ProgulRealisasi`, `TransaksiPendapatan`
5. **Update `DashboardRepository`** untuk menggunakan cache
6. **Update `LaporanRealisasi` page** untuk pakai method cached SKPD
7. **Buat `WarmupCache` command** dan jalankan setelah deploy
8. **Test invalidasi** — pastikan data update di form menyebabkan cache ter-refresh

---

## 8. Catatan Penting

- **`AppSetting`** sudah menggunakan cache dengan benar — jangan diubah.
- **`Skpd::formatDisplayKode()` dan `formatDisplayNomenklatur()`** saat ini menggunakan `static $skpdCache = []` (in-memory per-request). Ganti dengan `getAllWithBidangCached()` agar cache persist antar request via Redis.
- **Filament `preload()`** pada `Select` component sudah melakukan query sendiri — untuk form yang sering dibuka, pertimbangkan override `getOptionLabel` / `getSearchResults` untuk memanfaatkan cached collection daripada query langsung.
- **Jangan cache data yang user-specific** (seperti data berdasarkan `Auth::id()` tanpa memasukkan user ID ke cache key) — selalu sertakan identifier dalam key.
- **Konsistensi invalidasi:** Pastikan setiap `create`, `update`, dan `delete` membersihkan cache yang relevan.

