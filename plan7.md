# Plan 7: Refaktor Halaman Publik Pendapatan — Panel Level 2 & Halaman PAD

## Konteks & Tujuan

Mengubah halaman **`IndexPendapatan`** (Rekapitulasi Pendapatan Daerah / halaman utama pendapatan publik) sehingga:

1. **4 panel kartu gelap** di sisi kanan — yang sebelumnya menampilkan Pajak, Retribusi, Kekayaan, dan Lain-lain PAD (level 3, anak dari `4.1`) — **diganti** menjadi panel berdasarkan **rekening level 2** (misal: `4.1 PAD`, `4.2 Dana Perimbangan`, `4.3 ...`, dst.) sesuai data rekening yang ada di database.
2. **Tabel SKPD** di bawah grafik (Daftar OPD) **diganti** menjadi **tabel pendapatan keseluruhan** (struktur hirarki yang sama seperti di halaman `PublicPendapatan` / tabel `getPendapatanTree`).
3. **Tombol "Pendapatan Keseluruhan"** di pojok kanan atas header — yang sebelumnya mengarah ke `/public-pendapatan` — **diubah label-nya** menjadi **"Pendapatan Asli Daerah"**, dan **halamannya diganti total** menjadi tampilan khusus PAD (hanya rekening dengan kode `4.1.*`) sesuai desain pada gambar referensi:
   - Grafik donut hanya menghitung **total target & realisasi PAD (`4.1`)** saja.
   - Tabel di bawah grafik tetap **tabel SKPD**, tetapi hanya SKPD yang **memiliki target pada rekening PAD (`4.1.*`)** saja.

---

## Analisis Kondisi Saat Ini

### Route & Livewire

| Route | Nama | Livewire Class | Blade View |
|---|---|---|---|
| `/pendapatan` | `pendapatan.index` | `IndexPendapatan` | `index-pendapatan.blade.php` |
| `/public-pendapatan` | `public.pendapatan` | `PublicPendapatan` | `public-pendapatan.blade.php` |
| `/pendapatan/jenis/{jenis}` | `pendapatan.jenis` | `DetailJenisPendapatan` | `detail-jenis-pendapatan.blade.php` |

### Data yang Digunakan di `IndexPendapatan`

```php
// IndexPendapatan.php — render()
$skpds          = $repository->getAllSkpdPendapatanSummary($this->tahun);   // → Tabel SKPD
$summaryByJenis = $repository->getPendapatanSummaryByJenisRekening($this->tahun); // → 4 Panel Kartu
```

`getPendapatanSummaryByJenisRekening()` saat ini mengembalikan:

```php
$categories = [
    'pajak'     => ['kode' => '4.1.01', 'nama' => 'Pajak Daerah'],
    'retribusi' => ['kode' => '4.1.02', 'nama' => 'Retribusi Daerah'],
    'kekayaan'  => ['kode' => '4.1.03', 'nama' => 'Hasil Pengelolaan Kekayaan'],
    'lain_lain' => ['kode' => '4.1.04', 'nama' => 'Lain-lain PAD Sah'],
];
```
→ Ini adalah rekening **level 3** (anak dari `4.1`). Harus diganti ke **level 2**.

### Data yang Digunakan di `PublicPendapatan`

```php
// PublicPendapatan.php — render()
$pendapatanData = $repository->getPendapatanTree($this->tahun); // → Tabel hirarki level 1–4
```

Struktur tiap baris di `getPendapatanTree()`:
```php
[
    'kode'       => '4.1.01',
    'uraian'     => 'Pajak Daerah',
    'level'      => 3,
    'target'     => 46000000,
    'realisasi'  => 22500000,
    'selisih'    => 23500000,
    'persentase' => 48.91,
]
```

---

## Perubahan yang Diperlukan

---

### Perubahan 1: Panel Kartu di `IndexPendapatan` → Tampilkan Level 2

#### 1a. Tambah method repository baru: `getPendapatanSummaryByLevel2()`

**File:** `app/Repositories/DashboardRepositoryInterface.php`

Tambahkan deklarasi method baru:

```php
/**
 * Get summary of Pendapatan grouped by Level 2 rekening nodes (e.g. 4.1, 4.2, 4.3).
 *
 * @param int|null $tahun
 * @return array  [ ['kode'=>'4.1','nama'=>'...','target'=>0,'realisasi'=>0,'persentase'=>0], ... ]
 */
public function getPendapatanSummaryByLevel2(?int $tahun = null): array;
```

#### 1b. Implementasi di `DashboardRepository`

**File:** `app/Repositories/Eloquent/DashboardRepository.php`

Tambahkan method berikut (setelah `getPendapatanSummaryByJenisRekening`):

```php
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
```

> **Catatan:** Method ini memanfaatkan `getPendapatanTree()` yang sudah ada — hasilnya sudah include level 1–4, tinggal difilter level 2 saja.

#### 1c. Update `IndexPendapatan.php` — ganti `summaryByJenis` dengan `summaryLevel2`

**File:** `app/Livewire/IndexPendapatan.php`

```php
// SEBELUM
$summaryByJenis = $repository->getPendapatanSummaryByJenisRekening($this->tahun);

return view('livewire.index-pendapatan', [
    'skpds'          => $skpds,
    'summaryByJenis' => $summaryByJenis,
    'availableYears' => $availableYears,
]);

// SESUDAH
$summaryLevel2 = $repository->getPendapatanSummaryByLevel2($this->tahun);

return view('livewire.index-pendapatan', [
    'skpds'          => $skpds,
    'summaryLevel2'  => $summaryLevel2,
    'availableYears' => $availableYears,
]);
```

Hapus juga dispatch di `updatedTahun()` yang masih menggunakan `$skpds->sum(...)` — update juga sumbernya jika grafik ikut berubah. Untuk sementara grafik di `IndexPendapatan` tetap menampilkan total keseluruhan dari `$skpds`, tidak perlu diubah.

#### 1d. Update `index-pendapatan.blade.php` — ubah 4 panel kartu ke dinamis level 2

**File:** `resources/views/livewire/index-pendapatan.blade.php`

**HAPUS** blok panel kartu lama yang statis (hardcode `pajak`, `retribusi`, `kekayaan`, `lain_lain`):

```blade
{{-- HAPUS bagian ini --}}
@foreach(['pajak', 'retribusi', 'kekayaan', 'lain_lain'] as $key)
    @if(isset($summaryByJenis[$key]))
        ...
    @endif
@endforeach
```

**GANTI** dengan iterasi dinamis dari `$summaryLevel2`:

```blade
{{-- Kartu warna per-indeks (max 4 warna, sisanya fallback ke warna pertama) --}}
@php
$cardColors = [
    ['border' => 'border-[#113a37]', 'iconBg' => 'bg-[#0f2924]', 'text' => 'text-[#00e1cc]'],
    ['border' => 'border-[#3a250a]', 'iconBg' => 'bg-[#291705]', 'text' => 'text-[#f59e0b]'],
    ['border' => 'border-[#0f203a]', 'iconBg' => 'bg-[#0a1629]', 'text' => 'text-[#3b82f6]'],
    ['border' => 'border-[#2c103a]', 'iconBg' => 'bg-[#1e0a29]', 'text' => 'text-[#a855f7]'],
];
@endphp

@forelse($summaryLevel2 as $idx => $item)
    @php
        $cfg = $cardColors[$idx % count($cardColors)];
    @endphp
    <div class="rounded-xl border {{ $cfg['border'] }} p-5 flex flex-col justify-between relative overflow-hidden"
         style="background-color: #0a0a0a; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 16px 16px;">

        {{-- Icon & Title --}}
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ $cfg['iconBg'] }}">
                <svg class="w-5 h-5 {{ $cfg['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="7" stroke-width="2"/>
                    <circle cx="12" cy="12" r="3" stroke-width="2"/>
                </svg>
            </div>
            <h4 class="text-white font-semibold text-[13px] leading-tight" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                {{ $item['nama'] }}
            </h4>
        </div>

        {{-- Target --}}
        <div class="mb-4">
            <p class="text-[#6b7280] text-[10px] font-bold uppercase tracking-wider mb-0.5">Target</p>
            <p class="text-white text-lg font-bold" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                Rp {{ formatShortRp($item['target']) }}
            </p>
        </div>

        {{-- Realisasi --}}
        <div class="mb-4">
            <p class="{{ $cfg['text'] }} text-[10px] font-bold uppercase tracking-wider mb-0.5">
                Realisasi ({{ $item['persentase'] }}%)
            </p>
            <p class="{{ $cfg['text'] }} text-lg font-bold" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                Rp {{ formatShortRp($item['realisasi']) }}
            </p>
        </div>

        {{-- Tidak ada link detail karena tidak ada halaman per level 2 --}}
    </div>
@empty
    <div class="col-span-2 text-center text-[#6b7280] text-sm py-8">
        Belum ada data rekening level 2 untuk tahun {{ $tahun }}.
    </div>
@endforelse
```

> **Catatan:** Fungsi helper `formatShortRp()` tetap dipertahankan di blade karena sudah ada. Cukup pastikan ia dideklarasikan sebelum digunakan (sudah ada di view lama di blok `@php`).

---

### Perubahan 2: Tabel di `IndexPendapatan` → Ganti dari Tabel SKPD ke Tabel Pendapatan Keseluruhan

Saat ini `IndexPendapatan` menampilkan tabel SKPD (Daftar OPD). Tabel ini **diganti** dengan tabel hirarki pendapatan (seperti yang ada di `public-pendapatan.blade.php`).

#### 2a. Update `IndexPendapatan.php` — tambah `pendapatanData` ke view data

**File:** `app/Livewire/IndexPendapatan.php`

```php
// SEBELUM — render() method
$skpds          = $repository->getAllSkpdPendapatanSummary($this->tahun);
$skpds          = $skpds->sortByDesc('persentase')->values();
$summaryByJenis = $repository->getPendapatanSummaryByJenisRekening($this->tahun);
$availableYears = $repository->getAvailablePendapatanYears();

return view('livewire.index-pendapatan', [
    'skpds'          => $skpds,
    'summaryByJenis' => $summaryByJenis,
    'availableYears' => $availableYears,
]);

// SESUDAH — render() method
$pendapatanData = $repository->getPendapatanTree($this->tahun);
$summaryLevel2  = $repository->getPendapatanSummaryByLevel2($this->tahun);
$availableYears = $repository->getAvailablePendapatanYears();

// Untuk grafik donut keseluruhan, ambil node level 1 (kode '4') sebagai total
$totalTarget    = collect($pendapatanData)->where('level', 1)->sum('target');
$totalRealisasi = collect($pendapatanData)->where('level', 1)->sum('realisasi');

return view('livewire.index-pendapatan', [
    'pendapatanData' => $pendapatanData,
    'summaryLevel2'  => $summaryLevel2,
    'availableYears' => $availableYears,
    'totalTarget'    => $totalTarget,
    'totalRealisasi' => $totalRealisasi,
]);
```

> **Catatan:** Variabel `$skpds` tidak lagi dibutuhkan di view utama `IndexPendapatan`. Event `chart-data-updated` juga perlu disesuaikan (lihat Perubahan 2b).

#### 2b. Update `updatedTahun()` di `IndexPendapatan.php`

```php
// SEBELUM
public function updatedTahun()
{
    $repository = app(DashboardRepositoryInterface::class);
    $skpds = $repository->getAllSkpdPendapatanSummary($this->tahun);
    $this->dispatch('chart-data-updated', [
        'target'    => $skpds->sum('target'),
        'realisasi' => $skpds->sum('realisasi'),
    ]);
}

// SESUDAH
public function updatedTahun()
{
    $repository     = app(DashboardRepositoryInterface::class);
    $pendapatanData = $repository->getPendapatanTree($this->tahun);
    $totalTarget    = collect($pendapatanData)->where('level', 1)->sum('target');
    $totalRealisasi = collect($pendapatanData)->where('level', 1)->sum('realisasi');

    $this->dispatch('chart-data-updated', [
        'target'    => $totalTarget,
        'realisasi' => $totalRealisasi,
    ]);
}
```

#### 2c. Update `index-pendapatan.blade.php` — ganti bagian grafik & tabel

**Grafik total target/realisasi (kiri panel):**

Ganti:
```blade
{{-- SEBELUM --}}
<p class="text-xl font-bold text-[#005faf] dark:text-[#7bb4ff]">Rp {{ number_format($skpds->sum('target'), 0, ',', '.') }}</p>
...
<p class="text-xl font-bold text-[#af101a] dark:text-[#ffb4ab]">Rp {{ number_format($skpds->sum('realisasi'), 0, ',', '.') }}</p>
```

Dengan:
```blade
{{-- SESUDAH --}}
<p class="text-xl font-bold text-[#005faf] dark:text-[#7bb4ff]">Rp {{ number_format($totalTarget, 0, ',', '.') }}</p>
...
<p class="text-xl font-bold text-[#af101a] dark:text-[#ffb4ab]">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</p>
```

**Script grafik donut:**

Ganti:
```js
// SEBELUM
renderOrUpdateChart({{ $skpds->sum('target') }}, {{ $skpds->sum('realisasi') }});
```

Dengan:
```js
// SESUDAH
renderOrUpdateChart({{ $totalTarget }}, {{ $totalRealisasi }});
```

**Bagian tabel (ganti seluruh blok `<!-- Table Section -->`)**

Hapus tabel SKPD lama:
```blade
{{-- HAPUS seluruh blok Daftar OPD --}}
<div class="bg-white dark:bg-[#121212] ...">
    <div class="p-5 ..."><h3>Daftar OPD</h3></div>
    <table>
        <thead><tr>Nama OPD / SKPD | Target | Realisasi | % | Sisa | Aksi</tr></thead>
        <tbody>@forelse($skpds ...)</tbody>
    </table>
</div>
```

Ganti dengan tabel hirarki pendapatan:

```blade
{{-- BARU: Tabel Pendapatan Keseluruhan (hierarki) --}}
<div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl shadow-sm overflow-hidden flex flex-col transition-colors">
    <div class="p-5 border-b border-[#e0e3e6] dark:border-[#1f1f1f] bg-[#f7f9fc] dark:bg-[#1a1a1a] flex justify-between items-center transition-colors">
        <h3 class="text-lg font-bold text-[#191c1e] dark:text-white" style="font-family: 'Plus Jakarta Sans', sans-serif;">
            Rincian Pendapatan
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left border-collapse" style="font-family: 'Inter', sans-serif;">
            <thead class="text-white text-center font-semibold bg-[#d32f2f]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                <tr>
                    <th rowspan="2" class="px-4 py-3 border border-[#ba1a20] w-[8%]">No / Kode</th>
                    <th rowspan="2" class="px-4 py-3 border border-[#ba1a20] w-[32%]">Jenis Pendapatan</th>
                    <th colspan="2" class="px-4 py-2 border border-[#ba1a20]">{{ $tahun }}</th>
                    <th colspan="2" class="px-4 py-2 border border-[#ba1a20]">+/-</th>
                </tr>
                <tr>
                    <th class="px-4 py-2 border border-[#ba1a20] w-[15%]">Target</th>
                    <th class="px-4 py-2 border border-[#ba1a20] w-[15%]">Realisasi</th>
                    <th class="px-4 py-2 border border-[#ba1a20] w-[15%]">Rp</th>
                    <th class="px-4 py-2 border border-[#ba1a20] w-[15%]">%</th>
                </tr>
            </thead>
            <tbody class="text-[#191c1e] dark:text-[#e0e0e0]">
                @foreach($pendapatanData as $index => $row)
                    @php
                        if ($row['level'] == 1) {
                            $bgClass = 'bg-[#cce0ff] dark:bg-[#001c3a]';
                        } elseif ($row['level'] == 2) {
                            $bgClass = 'bg-[#e6f0ff] dark:bg-[#0a1f3a]';
                        } elseif ($row['level'] == 3) {
                            $bgClass = 'bg-[#f2f7ff] dark:bg-[#0f1f2e]';
                        } else {
                            $bgClass = $index % 2 === 0 ? 'bg-[#f7f9fc] dark:bg-[#121212]' : 'bg-white dark:bg-[#1a1a1a]';
                        }
                        $paddingLeft = max(0, ($row['level'] - 1) * 20);
                        $textClass   = $row['level'] <= 3 ? 'font-bold' : '';
                    @endphp
                    <tr class="{{ $bgClass }} hover:bg-[#e6e8eb] dark:hover:bg-[#1f1f1f] transition-colors">
                        <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] text-center font-semibold">{{ $row['kode'] }}</td>
                        <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] {{ $textClass }}" style="padding-left: {{ 16 + $paddingLeft }}px;">
                            {{ $row['uraian'] }}
                        </td>
                        <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] text-right">
                            {{ number_format($row['target'], 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] text-right">
                            {{ number_format($row['realisasi'], 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] text-right">
                            {{ number_format($row['selisih'], 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] text-center font-semibold">
                            {{ number_format($row['persentase'], 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($pendapatanData) === 0)
            <div class="p-8 text-center text-[#5b403d] dark:text-[#a0a0a0]">
                Tidak ada data pendapatan untuk tahun {{ $tahun }}.
            </div>
        @endif
    </div>
</div>
```

---

### Perubahan 3: Tombol "Pendapatan Keseluruhan" → Ubah Label & Fungsionalitas Halaman PAD

#### 3a. Ubah label tombol di `index-pendapatan.blade.php`

**File:** `resources/views/livewire/index-pendapatan.blade.php`

```blade
{{-- SEBELUM --}}
<a href="{{ route('public.pendapatan', ['tahun' => $tahun]) }}" wire:navigate
   class="inline-flex items-center gap-2 px-4 py-2 bg-[#af101a] hover:bg-[#93000a] text-white rounded-lg font-semibold text-sm transition-colors shadow-sm"
   style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">...</svg>
    Pendapatan Keseluruhan
</a>

{{-- SESUDAH --}}
<a href="{{ route('public.pendapatan', ['tahun' => $tahun]) }}" wire:navigate
   class="inline-flex items-center gap-2 px-4 py-2 bg-[#af101a] hover:bg-[#93000a] text-white rounded-lg font-semibold text-sm transition-colors shadow-sm"
   style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">...</svg>
    Pendapatan Asli Daerah
</a>
```

#### 3b. Ubah halaman `PublicPendapatan` menjadi tampilan khusus PAD

Halaman `/public-pendapatan` (Livewire `PublicPendapatan` + blade `public-pendapatan.blade.php`) **dirombak total** menjadi seperti desain pada gambar referensi:

- **Judul halaman:** "Pendapatan Asli Daerah" (ganti dari "PENDAPATAN KESELURUHAN")
- **Grafik donut:** Hanya menghitung target & realisasi rekening **PAD (`4.1`) saja**
- **Tabel SKPD di bawah grafik:** Daftar SKPD yang memiliki target pada rekening `4.1.*` saja

##### 3b-1. Tambah method repository baru: `getAllSkpdPadSummary()`

**File:** `app/Repositories/DashboardRepositoryInterface.php`

```php
/**
 * Get summary of Pendapatan Asli Daerah (PAD) for each SKPD.
 * Only includes SKPDs that have targets on rekening with kode starting with '4.1'.
 *
 * @param int|null $tahun
 * @return \Illuminate\Support\Collection
 */
public function getAllSkpdPadSummary(?int $tahun = null);
```

**File:** `app/Repositories/Eloquent/DashboardRepository.php`

```php
public function getAllSkpdPadSummary(?int $tahun = null)
{
    $tahun = $tahun ?? date('Y');

    // Ambil SKPD yang memiliki target pada rekening kode 4.1.*
    $skpdIds = \App\Models\TargetPendapatan::where('tahun', $tahun)
        ->whereHas('rekening', function ($q) {
            $q->where('kode', 'like', '4.1%');
        })
        ->pluck('skpd_id')
        ->unique();

    return Skpd::whereIn('id', $skpdIds)->orderBy('nama_skpd')->get()->map(function ($skpd) use ($tahun) {
        // Target hanya dari rekening 4.1.*
        $target = (float) \App\Models\TargetPendapatan::where('skpd_id', $skpd->id)
            ->where('tahun', $tahun)
            ->whereHas('rekening', fn($q) => $q->where('kode', 'like', '4.1%'))
            ->sum('target_anggaran');

        // Realisasi hanya dari transaksi yang is_verif=true dan rekening 4.1.*
        $realisasi = (float) \App\Models\TransaksiPendapatan::where('is_verif', true)
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
```

##### 3b-2. Update `PublicPendapatan.php`

**File:** `app/Livewire/PublicPendapatan.php`

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class PublicPendapatan extends Component
{
    #[Url(as: 'tahun')]
    public $tahun;

    public function mount()
    {
        if (!$this->tahun) {
            $this->tahun = (int) request()->query('tahun', date('Y'));
        }
    }

    public function updatedTahun()
    {
        $repository     = app(DashboardRepositoryInterface::class);
        $padTree        = $repository->getPendapatanTree($this->tahun);
        $padNode        = collect($padTree)->firstWhere('kode', '4.1');
        $totalTarget    = (float) ($padNode['target']    ?? 0);
        $totalRealisasi = (float) ($padNode['realisasi'] ?? 0);

        $this->dispatch('chart-data-updated', [
            'target'    => $totalTarget,
            'realisasi' => $totalRealisasi,
        ]);
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        // Ambil data tree, filter hanya node PAD (kode 4.1 dan turunannya)
        $fullTree = $repository->getPendapatanTree($this->tahun);

        // Node 4.1 untuk total PAD
        $padNode        = collect($fullTree)->firstWhere('kode', '4.1');
        $totalTarget    = (float) ($padNode['target']    ?? 0);
        $totalRealisasi = (float) ($padNode['realisasi'] ?? 0);

        // Tabel SKPD — hanya SKPD yang mengisi PAD
        $skpds = $repository->getAllSkpdPadSummary($this->tahun);
        $skpds = $skpds->sortByDesc('persentase')->values();

        $lastUpdate     = $repository->getLastUpdatePendapatan($this->tahun);
        $availableYears = $repository->getAvailablePendapatanYears();

        return view('livewire.public-pendapatan', [
            'totalTarget'    => $totalTarget,
            'totalRealisasi' => $totalRealisasi,
            'skpds'          => $skpds,
            'tahun'          => $this->tahun,
            'lastUpdate'     => $lastUpdate,
            'availableYears' => $availableYears,
        ]);
    }
}
```

##### 3b-3. Rombak total `public-pendapatan.blade.php`

**File:** `resources/views/livewire/public-pendapatan.blade.php`

Halaman ini dirombak total mengikuti desain referensi pada gambar (mirip `IndexPendapatan` tapi untuk PAD saja). Struktur baru:

```blade
<div>
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Breadcrumbs --}}
        <nav class="flex text-sm text-[#5b403d] dark:text-[#a0a0a0] font-medium mb-6 transition-colors">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center hover:text-[#af101a] dark:hover:text-white transition-colors">Home</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <span class="mx-2">/</span>
                        <a href="{{ route('pendapatan.index', ['tahun' => $tahun]) }}" wire:navigate class="hover:text-[#af101a] dark:hover:text-white transition-colors">Pendapatan Daerah</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <span class="mx-2">/</span>
                        <span class="text-[#191c1e] dark:text-[#e0e0e0] font-semibold">Pendapatan Asli Daerah</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-[#d4e3ff] dark:bg-[#003840] text-[#001c3a] dark:text-[#4fd8e6] flex items-center justify-center shrink-0 transition-colors">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-3xl font-bold text-[#191c1e] dark:text-white tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Pendapatan Asli Daerah
                    </h2>
                    <p class="text-[#5b403d] dark:text-[#a0a0a0] mt-1 transition-colors">
                        Realisasi PAD per OPD - Tahun {{ $tahun }}
                    </p>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex flex-wrap items-center gap-3">
                {{-- Year Filter --}}
                <div class="flex items-center gap-2 bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-lg px-3 py-1.5 shadow-sm">
                    <label for="pad-tahun" class="text-xs font-bold uppercase tracking-wider text-[#5b403d] dark:text-[#a0a0a0] flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4 text-[#af101a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Tahun Realisasi:
                    </label>
                    <select id="pad-tahun" wire:model.live="tahun" class="text-sm font-semibold rounded border-none bg-transparent text-[#191c1e] dark:text-white focus:outline-none focus:ring-0 cursor-pointer">
                        @foreach($availableYears as $yearOption)
                            <option value="{{ $yearOption }}" {{ $tahun == $yearOption ? 'selected' : '' }} class="bg-white dark:bg-[#1e1e1e]">
                                {{ $yearOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Info Banner --}}
        <div class="bg-[#f2f4f7] dark:bg-[#1a1a1a] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-[8px] p-3 mb-6 flex flex-wrap items-center gap-2 text-sm transition-colors" style="font-family: 'Inter', sans-serif;">
            <div class="flex items-center">
                <div class="mr-2 text-[#ba1a1a]">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <span class="text-[#191c1e] dark:text-[#e0e0e0] font-semibold mr-1">data yang ditampilkan terakhir update</span>
                <span class="text-[#ba1a1a] font-bold">{{ $lastUpdate ?? '-' }}</span>
            </div>
            <a href="{{ route('pendapatan.index', ['tahun' => $tahun]) }}" wire:navigate class="ml-auto text-[#005faf] hover:underline font-semibold flex items-center text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Rekapitulasi
            </a>
        </div>

        {{-- Summary Chart & SKPD Table Section --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-stretch mb-8">

            {{-- Left: Grafik Donut PAD --}}
            <div class="xl:col-span-7 bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-6 shadow-sm transition-colors">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-[#191c1e] dark:text-white" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Grafik Pendapatan Asli Daerah
                    </h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <div class="md:col-span-1 flex flex-col gap-4">
                        <div class="p-4 bg-[#f2f4f7] dark:bg-[#1a1a1a] rounded-lg border border-transparent dark:border-[#2a2a2a] transition-colors">
                            <p class="text-sm font-semibold text-[#5b403d] dark:text-[#a0a0a0] mb-1">Total Target PAD</p>
                            <p class="text-xl font-bold text-[#005faf] dark:text-[#7bb4ff]">
                                Rp {{ number_format($totalTarget, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-4 bg-[#ffdad6]/30 dark:bg-[#af101a]/20 rounded-lg border border-transparent dark:border-[#af101a]/30 transition-colors">
                            <p class="text-sm font-semibold text-[#af101a] dark:text-[#ffb4ab] mb-1">Total Realisasi PAD</p>
                            <p class="text-xl font-bold text-[#af101a] dark:text-[#ffb4ab]">
                                Rp {{ number_format($totalRealisasi, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    <div class="md:col-span-2" wire:ignore>
                        <div id="pad-chart" style="min-height: 250px;"></div>
                    </div>
                </div>
            </div>

            {{-- Right: placeholder atau summary cards PAD (opsional, bisa dikosongkan / diisi ringkasan level 3) --}}
            {{-- Untuk saat ini bisa diisi ringkasan level 3 PAD (Pajak, Retribusi, Kekayaan, Lain-lain) --}}
            <div class="xl:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                if (!function_exists('formatShortRpPad')) {
                    function formatShortRpPad($num) {
                        if ($num >= 1000000000000) return round($num / 1000000000000, 1) . ' T';
                        if ($num >= 1000000000) return round($num / 1000000000, 1) . ' M';
                        if ($num >= 1000000) return round($num / 1000000, 1) . ' Jt';
                        return number_format($num);
                    }
                }
                // Ini untuk referensi card detail komponen PAD (level 3 di bawah 4.1)
                // Bisa diisi dengan data $summaryByJenis jika diperlukan
                // Untuk sekarang tampilkan total saja dalam 1 card besar
                $persentasePad = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 2) : 0;
                @endphp
                <div class="col-span-2 rounded-xl border border-[#113a37] p-5 flex flex-col justify-between relative overflow-hidden"
                     style="background-color: #0a0a0a; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 16px 16px;">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 bg-[#0f2924]">
                            <svg class="w-5 h-5 text-[#00e1cc]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="7" stroke-width="2"/>
                                <circle cx="12" cy="12" r="3" stroke-width="2"/>
                            </svg>
                        </div>
                        <h4 class="text-white font-semibold text-[13px] leading-tight" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            Pendapatan Asli Daerah (PAD)
                        </h4>
                    </div>
                    <div class="mb-4">
                        <p class="text-[#6b7280] text-[10px] font-bold uppercase tracking-wider mb-0.5">Target</p>
                        <p class="text-white text-2xl font-bold" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            Rp {{ formatShortRpPad($totalTarget) }}
                        </p>
                    </div>
                    <div class="mb-4">
                        <p class="text-[#00e1cc] text-[10px] font-bold uppercase tracking-wider mb-0.5">
                            Realisasi ({{ $persentasePad }}%)
                        </p>
                        <p class="text-[#00e1cc] text-2xl font-bold" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            Rp {{ formatShortRpPad($totalRealisasi) }}
                        </p>
                    </div>
                    {{-- Progress bar --}}
                    <div class="w-full bg-[#1a2e2c] rounded-full h-2">
                        <div class="bg-[#00e1cc] h-2 rounded-full transition-all" style="width: {{ min($persentasePad, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel SKPD yang mengisi PAD --}}
        <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl shadow-sm overflow-hidden flex flex-col transition-colors">
            <div class="p-5 border-b border-[#e0e3e6] dark:border-[#1f1f1f] bg-[#f7f9fc] dark:bg-[#1a1a1a] flex justify-between items-center transition-colors">
                <div>
                    <h3 class="text-lg font-bold text-[#191c1e] dark:text-white" style="font-family: 'Plus Jakarta Sans', sans-serif;">Daftar OPD — Pendapatan Asli Daerah</h3>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#005faf] dark:bg-[#003c73] text-white text-sm uppercase tracking-wider transition-colors" style="font-family: 'Manrope', sans-serif;">
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">Nama OPD / SKPD</th>
                            <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Target (Rp)</th>
                            <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Realisasi (Rp)</th>
                            <th class="px-6 py-4 font-semibold text-center whitespace-nowrap">%</th>
                            <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Sisa Target (Rp)</th>
                            <th class="px-6 py-4 font-semibold text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-[#191c1e] dark:text-[#e0e0e0] divide-y divide-[#e0e3e6] dark:divide-[#1f1f1f] transition-colors">
                        @forelse($skpds as $index => $skpd)
                        <tr class="hover:bg-[#f2f4f7] dark:hover:bg-[#1a1a1a] transition-colors {{ $index % 2 == 0 ? '' : 'bg-[#005faf]/5 dark:bg-[#ffffff]/5' }}">
                            <td class="px-6 py-4 font-medium text-[#191c1e] dark:text-white">{{ $skpd['nama_skpd'] }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">{{ number_format($skpd['target'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap font-medium text-[#005faf] dark:text-[#7bb4ff]">{{ number_format($skpd['realisasi'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $skpd['persentase'] < 50 ? 'bg-[#ffdad6] dark:bg-[#93000a]/30 text-[#93000a] dark:text-[#ffb4ab]' : 'bg-[#dbf9ff] dark:bg-[#004f58]/30 text-[#004f58] dark:text-[#4fd8e6]' }}">
                                    {{ $skpd['persentase'] }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap font-medium text-[#ba1a1a] dark:text-[#ffb4ab]">
                                {{ number_format($skpd['sisa'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <a href="{{ route('pendapatan.detail', ['skpdId' => $skpd['id'], 'tahun' => $tahun]) }}"
                                   class="inline-flex items-center gap-1 text-[#005faf] dark:text-[#7bb4ff] hover:text-[#af101a] dark:hover:text-[#ffb4ab] font-semibold transition-colors bg-white dark:bg-[#121212] hover:bg-[#ffdad6] dark:hover:bg-[#2a2a2a] border border-[#005faf] dark:border-[#7bb4ff] hover:border-[#af101a] dark:hover:border-[#ffb4ab] px-3 py-1.5 rounded-lg text-xs"
                                   style="font-family: 'Manrope', sans-serif;">
                                    Lihat Detail
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-[#5b403d] dark:text-[#a0a0a0]">
                                Belum ada OPD yang memiliki target Pendapatan Asli Daerah tahun ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let chart = null;

            function renderOrUpdateChart(totalTarget, totalRealisasi) {
                const sisaTarget = Math.max(totalTarget - totalRealisasi, 0);

                var options = {
                    series: [totalRealisasi, sisaTarget],
                    chart: { type: 'donut', height: 320, fontFamily: 'Inter, sans-serif' },
                    labels: ['Realisasi', 'Sisa Target'],
                    colors: ['#af101a', '#e0e3e6'],
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) { return val.toFixed(1) + "%" }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    value: {
                                        show: true,
                                        formatter: function (val) { return "Rp " + parseInt(val).toLocaleString('id-ID') }
                                    },
                                    total: {
                                        show: true,
                                        showAlways: true,
                                        label: 'Total Target PAD',
                                        formatter: function (w) { return "Rp " + totalTarget.toLocaleString('id-ID') }
                                    }
                                }
                            }
                        }
                    },
                    tooltip: {
                        y: { formatter: function (val) { return "Rp " + parseInt(val).toLocaleString('id-ID') } }
                    },
                    legend: { position: 'right', offsetY: 0, height: 230 }
                };

                const el = document.querySelector("#pad-chart");
                if (!el) return;

                if (chart) {
                    chart.updateOptions(options);
                    chart.updateSeries([totalRealisasi, sisaTarget]);
                } else {
                    chart = new ApexCharts(el, options);
                    chart.render();
                }
            }

            renderOrUpdateChart({{ $totalTarget }}, {{ $totalRealisasi }});

            Livewire.on('chart-data-updated', (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                renderOrUpdateChart(payload.target, payload.realisasi);
            });
        });
    </script>
</div>
```

---

## Ringkasan Perubahan File

| File | Jenis | Detail |
|------|-------|--------|
| `app/Repositories/DashboardRepositoryInterface.php` | **Edit** | Tambah 2 deklarasi method: `getPendapatanSummaryByLevel2()` dan `getAllSkpdPadSummary()` |
| `app/Repositories/Eloquent/DashboardRepository.php` | **Edit** | Implementasi `getPendapatanSummaryByLevel2()` dan `getAllSkpdPadSummary()` |
| `app/Livewire/IndexPendapatan.php` | **Edit** | Ganti `$skpds` + `$summaryByJenis` → `$pendapatanData` + `$summaryLevel2` + `$totalTarget` + `$totalRealisasi`; update `updatedTahun()` |
| `resources/views/livewire/index-pendapatan.blade.php` | **Edit** | (1) Panel kartu → dinamis level 2; (2) Tabel SKPD → tabel hirarki pendapatan; (3) Grafik pakai `$totalTarget`/`$totalRealisasi`; (4) Label tombol → "Pendapatan Asli Daerah" |
| `app/Livewire/PublicPendapatan.php` | **Edit** | Rombak: kirim `$totalTarget`, `$totalRealisasi` (hanya PAD / `4.1`), `$skpds` (PAD only), tambah `updatedTahun()` |
| `resources/views/livewire/public-pendapatan.blade.php` | **Edit** | Rombak total: judul "Pendapatan Asli Daerah", grafik donut PAD, tabel SKPD PAD |

---

## Urutan Pengerjaan

```
1. Tambah deklarasi method di DashboardRepositoryInterface
2. Implementasi getPendapatanSummaryByLevel2() di DashboardRepository
3. Implementasi getAllSkpdPadSummary() di DashboardRepository
4. Update IndexPendapatan.php (render + updatedTahun)
5. Update index-pendapatan.blade.php:
   a. Ganti panel kartu ke summaryLevel2
   b. Ganti tabel SKPD ke tabel hirarki pendapatan
   c. Update variabel grafik ($totalTarget/$totalRealisasi)
   d. Ubah label tombol → "Pendapatan Asli Daerah"
6. Update PublicPendapatan.php
7. Rombak public-pendapatan.blade.php
8. Test: cek panel level 2 muncul, tabel hirarki benar, halaman PAD grafik & tabel hanya PAD
```

---

## Ilustrasi Alur Data

```
IndexPendapatan (/pendapatan)
├── Panel Kiri: Grafik donut → totalTarget & totalRealisasi dari level 1 (node '4')
├── Panel Kanan (4 kartu): level 2 rekening → 4.1 PAD, 4.2 Dana Perimbangan, 4.3 ..., dst.
├── Tabel bawah: Tabel hirarki getPendapatanTree() (level 1–4, semua rekening)
└── Tombol "Pendapatan Asli Daerah" → /public-pendapatan

PublicPendapatan (/public-pendapatan)
├── Judul: "Pendapatan Asli Daerah"
├── Grafik donut → totalTarget & totalRealisasi dari node '4.1' saja
├── Card ringkasan PAD
└── Tabel SKPD → hanya SKPD yang punya target rekening 4.1.*
```

