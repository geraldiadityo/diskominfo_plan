# Plan: Halaman Indikator Kinerja Penyelenggaraan (Frontend Public)

## Ringkasan Fitur

Buat halaman **public** baru bernama **"Indikator Kinerja Penyelenggaraan"** yang:
- Diletakkan sebagai sub menu dari menu **"Kinerja"** di navigasi frontend.
- Menampilkan tabel indikator penyelenggaraan beserta **Baseline** dan **Realisasi per tahun** (mirip tampilan gambar referensi terlampir).
- Data bersumber dari model `MasterIndikator` (kategori = "Penyelenggaraan") dan `IndikatorRealisasi`.
- Memiliki **filter 6 tahun** (default: 2024–2029).
- Tahun pertama yang dipilih menjadi kolom **Baseline**.
- 5 tahun berikutnya menampilkan pasangan **Target | Realisasi**.
- Mendukung **grouping** berdasarkan aspek / sub-kategori indikator (misalnya "Aspek Geografi dan Demografi", "Aspek Kesejahteraan Masyarakat", dst.).
- Kolom yang ditampilkan: **No | Indikator | Satuan | Baseline (Tahun-1) | Target+Realisasi per tahun (Tahun 2–6) | Sumber Data / SKPD Penanggung Jawab**.

---

## Struktur File yang Akan Dibuat / Dimodifikasi

```
app/
├── Livewire/
│   └── PublicKinerjaPenyelenggaraan.php          ← BARU (Livewire Component)
├── Repositories/
│   ├── DashboardRepositoryInterface.php           ← MODIFIKASI (tambah method)
│   └── Eloquent/
│       └── DashboardRepository.php               ← MODIFIKASI (implementasi method)

resources/views/livewire/
│   └── public-kinerja-penyelenggaraan.blade.php  ← BARU (Blade View)

resources/views/layouts/
│   └── frontend.blade.php                         ← MODIFIKASI (tambah link sub menu)

routes/
│   └── web.php                                    ← MODIFIKASI (tambah route baru)
```

---

## Referensi Tampilan

Berdasarkan gambar referensi (spreadsheet RPJMD), tabel memiliki struktur:

| No | Indikator | Satuan | Baseline (Tahun-1) | Target Tahun-2 | Realisasi | Target Tahun-3 | Realisasi | … | Sumber Data / SKPD |
|----|-----------|--------|-------------------|---------------|-----------|---------------|-----------|---|--------------------|

- Baris **judul kategori** (bold, background berbeda) memisahkan kelompok indikator (contoh: "I. Aspek Geografi dan Demografi", "II. Aspek Kesejahteraan Masyarakat", dst.)
- Kolom **Baseline** hanya menampilkan nilai realisasi tahun pertama filter (tanpa kolom Target).
- Kolom tahun selanjutnya (tahun 2–5) menampilkan pasangan **Target | Realisasi**.
- Kolom terakhir: **Sumber Data / SKPD Penanggung Jawab** (`skpd.nama_skpd`).

---

## Detail Implementasi

### 1. Route (`routes/web.php`)

Tambahkan satu route baru:

```php
use App\Livewire\PublicKinerjaPenyelenggaraan;

Route::get('/kinerja-penyelenggaraan', PublicKinerjaPenyelenggaraan::class)
    ->name('kinerja-penyelenggaraan.index');
```

---

### 2. Navigasi Frontend (`resources/views/layouts/frontend.blade.php`)

Di dalam dropdown sub menu "Kinerja" (yang sudah ada "Kinerja Utama" dan "Kinerja Program"), tambahkan:

```html
<a href="{{ route('kinerja-penyelenggaraan.index') }}"
   class="block px-4 py-2 text-sm text-[#5b403d] dark:text-[#a0a0a0]
          hover:bg-[#f2f4f7] dark:hover:bg-[#2a2a2a]
          hover:text-[#af101a] dark:hover:text-white font-medium">
    Kinerja Penyelenggaraan
</a>
```

---

### 3. Repository Interface (`app/Repositories/DashboardRepositoryInterface.php`)

Tambahkan signature method baru:

```php
/**
 * Get Indikator Kinerja Penyelenggaraan data grouped by KategoriIndikator,
 * with baseline from the first year and target+realisasi for the rest.
 *
 * @param array $years  Contoh: [2025, 2026, 2027, 2028, 2029]
 * @return \Illuminate\Support\Collection
 */
public function getKinerjaPenyelenggaraanData(array $years): \Illuminate\Support\Collection;
```

---

### 4. Repository Implementasi (`app/Repositories/Eloquent/DashboardRepository.php`)

Tambahkan implementasi method berikut di dalam class `DashboardRepository`:

```php
public function getKinerjaPenyelenggaraanData(array $years): \Illuminate\Support\Collection
{
    $kategoriPenyelenggaraan = \App\Models\KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')
        ->value('id');

    $indikators = \App\Models\MasterIndikator::with([
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

    return $indikators->map(function ($indikator) use ($years) {
        return $this->mapPenyelenggaraanIndikator($indikator, $years);
    });
}

private function mapPenyelenggaraanIndikator(\App\Models\MasterIndikator $indikator, array $years): array
{
    $baselineYear   = $years[0];
    $remainingYears = array_slice($years, 1);

    // Baseline: realisasi tahun pertama (hanya approved)
    $baselineRealisasi = $indikator->realisasi
        ->where('tahun', $baselineYear)
        ->where('status', \App\Enums\StatusRealisasi::APPROVED)
        ->first();

    // Data per tahun (tahun 2–5): target + realisasi approved
    $dataYears = [];
    foreach ($remainingYears as $year) {
        $target    = $indikator->target->where('tahun', $year)->first();
        $realisasi = $indikator->realisasi
            ->where('tahun', $year)
            ->where('status', \App\Enums\StatusRealisasi::APPROVED)
            ->first();

        $dataYears[$year] = [
            'target'    => $target    ? $target->nilai_target       : null,
            'realisasi' => $realisasi ? $realisasi->nilai_realisasi : null,
        ];
    }

    return [
        'id'             => $indikator->id,
        'nama_indikator' => $indikator->nama_indikator,
        'satuan'         => $indikator->satuan?->nama_satuan ?? '-',
        'baseline'       => $baselineRealisasi?->nilai_realisasi ?? null,
        'years'          => $dataYears,
        'skpd'           => $indikator->skpd?->nama_skpd ?? '-',
        'children'       => $indikator->childIndikators->map(function ($child) use ($years) {
            $baselineYear   = $years[0];
            $remainingYears = array_slice($years, 1);

            $baselineChild = $child->realisasi
                ->where('tahun', $baselineYear)
                ->where('status', \App\Enums\StatusRealisasi::APPROVED)
                ->first();

            $childYears = [];
            foreach ($remainingYears as $year) {
                $target    = $child->target->where('tahun', $year)->first();
                $realisasi = $child->realisasi
                    ->where('tahun', $year)
                    ->where('status', \App\Enums\StatusRealisasi::APPROVED)
                    ->first();
                $childYears[$year] = [
                    'target'    => $target    ? $target->nilai_target       : null,
                    'realisasi' => $realisasi ? $realisasi->nilai_realisasi : null,
                ];
            }

            return [
                'id'             => $child->id,
                'nama_indikator' => $child->nama_indikator,
                'satuan'         => $child->satuan?->nama_satuan ?? '-',
                'baseline'       => $baselineChild?->nilai_realisasi ?? null,
                'years'          => $childYears,
                'skpd'           => $child->skpd?->nama_skpd ?? '-',
            ];
        })->values()->all(),
    ];
}
```

> **Catatan**: Jika `MasterIndikator` untuk kategori Penyelenggaraan tidak menggunakan hierarki parent-child, abaikan bagian `children` dan tampilkan flat list.

---

### 5. Livewire Component (`app/Livewire/PublicKinerjaPenyelenggaraan.php`)

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Repositories\DashboardRepositoryInterface;

#[Layout('layouts.frontend')]
class PublicKinerjaPenyelenggaraan extends Component
{
    #[Url(as: 'tahun_awal')]
    public int $filterStartYear = 2025;

    /**
     * Menghasilkan array 5 tahun berdasarkan tahun awal filter.
     * Tahun pertama = baseline.
     */
    public function getYearsProperty(): array
    {
        return range($this->filterStartYear, $this->filterStartYear + 4);
    }

    /**
     * Daftar pilihan tahun awal yang tersedia untuk filter.
     * Sesuaikan dengan tahun RPJMD aktual di database.
     */
    public function getAvailableStartYearsProperty(): array
    {
        return [2021, 2022, 2023, 2024, 2025];
    }

    public function render(DashboardRepositoryInterface $repository)
    {
        $years = $this->years;

        $data = $repository->getKinerjaPenyelenggaraanData($years);

        return view('livewire.public-kinerja-penyelenggaraan', [
            'indikatorData'       => $data,
            'years'               => $years,
            'baselineYear'        => $years[0],
            'remainingYears'      => array_slice($years, 1),
            'availableStartYears' => $this->availableStartYears,
        ]);
    }
}
```

---

### 6. Blade View (`resources/views/livewire/public-kinerja-penyelenggaraan.blade.php`)

```blade
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- ===== HEADER ===== --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-4">
        <div class="px-6 py-4 border-b border-gray-200 text-center">
            <p class="text-sm text-red-500 mb-1">Indikator Kinerja</p>
            <h2 class="text-xl font-bold text-red-600 uppercase">
                Indikator Penyelenggaraan Pemerintahan Daerah
            </h2>
        </div>

        {{-- ===== FILTER 5 TAHUN ===== --}}
        <div class="px-6 py-4 bg-[#eaf0f6] border-b border-gray-200 flex flex-wrap items-center gap-4">
            <label class="text-sm font-semibold text-gray-700">Periode:</label>
            <div class="flex flex-wrap gap-2">
                @foreach ($availableStartYears as $startYear)
                    <button
                        wire:click="$set('filterStartYear', {{ $startYear }})"
                        class="px-3 py-1 text-sm rounded-md border font-medium transition-colors
                            {{ $filterStartYear == $startYear
                                ? 'bg-red-600 text-white border-red-600'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-red-50 hover:border-red-400' }}">
                        {{ $startYear }}–{{ $startYear + 4 }}
                    </button>
                @endforeach
            </div>
            <span class="ml-auto text-xs text-gray-500 italic">
                * Tahun {{ $baselineYear }} = Baseline (nilai realisasi awal)
            </span>
        </div>
    </div>

    {{-- ===== TABEL ===== --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto px-4 pb-8">
            <table class="w-full text-xs text-left text-gray-900 border-collapse whitespace-nowrap">

                {{-- THEAD --}}
                <thead class="text-white bg-[#1a3c6b] font-semibold">
                    {{-- Row 1: Judul kolom utama --}}
                    <tr>
                        <th rowspan="2" class="px-3 py-3 border border-blue-900 text-center align-middle w-10">No</th>
                        <th rowspan="2" class="px-4 py-3 border border-blue-900 text-center align-middle min-w-[280px]">Indikator</th>
                        <th rowspan="2" class="px-3 py-3 border border-blue-900 text-center align-middle">Satuan</th>
                        <th rowspan="2" class="px-3 py-3 border border-blue-900 text-center align-middle">
                            Baseline<br>({{ $baselineYear }})
                        </th>
                        @foreach ($remainingYears as $year)
                            <th colspan="2" class="px-2 py-2 border border-blue-900 text-center">{{ $year }}</th>
                        @endforeach
                        <th rowspan="2" class="px-3 py-3 border border-blue-900 text-center align-middle min-w-[180px]">
                            Sumber Data /<br>SKPD Penanggung Jawab
                        </th>
                    </tr>
                    {{-- Row 2: Target | Realisasi per tahun --}}
                    <tr>
                        @foreach ($remainingYears as $year)
                            <th class="px-2 py-2 border border-blue-900 text-center">Target</th>
                            <th class="px-2 py-2 border border-blue-900 text-center">Realisasi</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse ($indikatorData as $indikator)
                        {{-- Baris Indikator Utama --}}
                        <tr class="bg-white hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-2 border border-gray-300 text-center">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 border border-gray-300 whitespace-normal font-medium">
                                {{ $indikator['nama_indikator'] }}
                            </td>
                            <td class="px-3 py-2 border border-gray-300 text-center">{{ $indikator['satuan'] }}</td>
                            {{-- Baseline --}}
                            <td class="px-3 py-2 border border-gray-300 text-center font-semibold bg-yellow-50">
                                {{ $indikator['baseline'] !== null
                                    ? (is_numeric($indikator['baseline']) ? floatval($indikator['baseline']) : $indikator['baseline'])
                                    : 'N.A' }}
                            </td>
                            {{-- Target & Realisasi per tahun --}}
                            @foreach ($remainingYears as $year)
                                <td class="px-2 py-2 border border-gray-300 text-center">
                                    {{ isset($indikator['years'][$year]['target']) && $indikator['years'][$year]['target'] !== null
                                        ? (is_numeric($indikator['years'][$year]['target'])
                                            ? floatval($indikator['years'][$year]['target'])
                                            : $indikator['years'][$year]['target'])
                                        : '' }}
                                </td>
                                <td class="px-2 py-2 border border-gray-300 text-center text-blue-700">
                                    {{ isset($indikator['years'][$year]['realisasi']) && $indikator['years'][$year]['realisasi'] !== null
                                        ? (is_numeric($indikator['years'][$year]['realisasi'])
                                            ? floatval($indikator['years'][$year]['realisasi'])
                                            : $indikator['years'][$year]['realisasi'])
                                        : '' }}
                                </td>
                            @endforeach
                            <td class="px-3 py-2 border border-gray-300 text-center whitespace-normal">
                                {{ $indikator['skpd'] }}
                            </td>
                        </tr>

                        {{-- Child Indikators (jika ada) --}}
                        @if (!empty($indikator['children']))
                            @foreach ($indikator['children'] as $child)
                                <tr class="bg-gray-50 hover:bg-gray-100 transition-colors">
                                    <td class="px-3 py-2 border border-gray-300 text-center text-gray-400">-</td>
                                    <td class="px-4 py-2 border border-gray-300 whitespace-normal pl-8 italic text-gray-700">
                                        {{ $child['nama_indikator'] }}
                                    </td>
                                    <td class="px-3 py-2 border border-gray-300 text-center">{{ $child['satuan'] }}</td>
                                    <td class="px-3 py-2 border border-gray-300 text-center bg-yellow-50">
                                        {{ $child['baseline'] !== null
                                            ? (is_numeric($child['baseline']) ? floatval($child['baseline']) : $child['baseline'])
                                            : 'N.A' }}
                                    </td>
                                    @foreach ($remainingYears as $year)
                                        <td class="px-2 py-2 border border-gray-300 text-center">
                                            {{ isset($child['years'][$year]['target']) && $child['years'][$year]['target'] !== null
                                                ? (is_numeric($child['years'][$year]['target'])
                                                    ? floatval($child['years'][$year]['target'])
                                                    : $child['years'][$year]['target'])
                                                : '' }}
                                        </td>
                                        <td class="px-2 py-2 border border-gray-300 text-center text-blue-700">
                                            {{ isset($child['years'][$year]['realisasi']) && $child['years'][$year]['realisasi'] !== null
                                                ? (is_numeric($child['years'][$year]['realisasi'])
                                                    ? floatval($child['years'][$year]['realisasi'])
                                                    : $child['years'][$year]['realisasi'])
                                                : '' }}
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-2 border border-gray-300 text-center whitespace-normal">
                                        {{ $child['skpd'] }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                    @empty
                        <tr>
                            <td colspan="{{ 4 + (count($remainingYears) * 2) }}"
                                class="px-4 py-8 border border-gray-300 text-center text-gray-500">
                                Tidak ada data indikator penyelenggaraan yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

</div>
```

---

## Catatan Penting untuk Implementasi

### A. Grouping Data (Sub-Kategori / Aspek)

Berdasarkan gambar referensi, indikator dikelompokkan menjadi beberapa aspek:
- "I. Aspek Geografi dan Demografi"
- "II. Aspek Kesejahteraan Masyarakat"
- dst.

Ada tiga opsi implementasi grouping:

**Opsi 1** – Flat list dulu (tanpa grouping), lalu tambahkan grouping belakangan.

**Opsi 2** – Gunakan field khusus di `MasterIndikator` (misalnya `aspek` atau `grup`).

**Opsi 3** – Buat tabel baru `aspek_indikator_penyelenggaraan` dengan kolom `nama_aspek`, lalu tambahkan relasi `belongs_to` di `MasterIndikator`. Di Blade, tampilkan baris header aspek sebelum loop indikator:

```blade
@foreach ($groupedData as $aspek)
    <tr class="bg-[#1a3c6b] text-white font-bold">
        <td colspan="{{ 4 + (count($remainingYears) * 2) }}"
            class="px-4 py-3 border border-blue-900 uppercase">
            {{ $aspek['nama_aspek'] }}
        </td>
    </tr>
    @foreach ($aspek['indikators'] as $index => $indikator)
        {{-- ... baris indikator ... --}}
    @endforeach
@endforeach
```

### B. Filter Tahun — Penyesuaian `$availableStartYears`

Sesuaikan dengan data aktual di database. Cara dinamis:

```php
// Di Livewire component
public function getAvailableStartYearsProperty(): array
{
    return \App\Models\IndikatorTarget::whereHas('indikator', function ($q) {
        $kategoriId = \App\Models\KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');
        $q->where('kategori_id', $kategoriId);
    })->distinct()->orderBy('tahun')->pluck('tahun')->toArray();
}
```

### C. Nilai Realisasi — Filter Status APPROVED

Pastikan hanya realisasi yang sudah diverifikasi (status = APPROVED) yang ditampilkan di halaman publik, sesuai enum `StatusRealisasi::APPROVED`.

### D. Performa — Caching (Opsional)

Jika data banyak, tambahkan cache di repository:

```php
return \Illuminate\Support\Facades\Cache::remember(
    "kinerja_penyelenggaraan_" . implode('_', $years),
    now()->addMinutes(30),
    fn() => $indikators->map(...)
);
```

---

## Urutan Pengerjaan

1. **Tambah method** ke `DashboardRepositoryInterface`.
2. **Implementasi method** di `DashboardRepository`.
3. **Buat Livewire component** `PublicKinerjaPenyelenggaraan`.
4. **Buat Blade view** `public-kinerja-penyelenggaraan.blade.php`.
5. **Tambah route** di `routes/web.php`.
6. **Tambah link sub menu** di `resources/views/layouts/frontend.blade.php`.
7. **Test** dengan berbagai kombinasi filter tahun.
8. *(Opsional)* **Tambah grouping aspek** jika struktur data mendukung.

