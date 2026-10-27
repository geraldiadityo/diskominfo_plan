# Plan 9 — Program Unggulan: Refactor ke Sub Kegiatan + Target/Realisasi Per Triwulan

## Konteks & Tujuan

Program Unggulan (Progul) saat ini menggunakan struktur **indikator kinerja tingkat tinggi** (teks deskriptif). Permintaan baru:

1. **Ubah granularitas** — dari tingkat indikator (teks) → ke **Program / Kegiatan / Sub Kegiatan** yang diambil dari Renja SKPD (`renja_skpds`).
2. **Sesuaikan nomor rekening** — di file Excel, nomor rekening yang tertera salah; harus di-lookup dari tabel master nomenklatur (`program_programs`, `program_kegiatans`, `program_sub_kegiatans`) berdasarkan nama/nomenklatur.
3. **Target & Realisasi Per Triwulan** — ubah struktur `progul_realisasis` yang semula per-tahun menjadi **per triwulan** (TW I–IV), dengan kolom `target_fisik`, `target_keuangan`, `realisasi_fisik`, `realisasi_keuangan`.
4. **Seeder JSON** — buat/ubah seeder `ProgulSeeder.php` yang membaca `data_progul.json` baru (format sub kegiatan + triwulan) agar mudah dieksekusi di production.
5. **Halaman Publik** — tambahkan **filter tahun** di `PublicProgramPrioritas`, tampilkan tabel per tahun dengan kolom target & realisasi per triwulan.

---

## Audit Kondisi Saat Ini

### Struktur Database Existing

| Tabel | Isi Sekarang |
|---|---|
| `progul_programs` | Nama program unggulan (misal: "Sungai Penuh Maju") |
| `progul_indikators` | Indikator kinerja teks (misal: "Revitalisasi pasar rakyat") |
| `progul_indikator_urusan` | Urusan per indikator |
| `progul_realisasis` | Target & realisasi **per tahun** (target_tahunan, nilai_realisasi, status) |

### Struktur Master Nomenklatur (untuk lookup rekening)

```
program_urusans       → kode + nomenklatur
program_bidangs       → urusan_id + kode + kode_lengkap + nomenklatur
program_programs      → bidang_id + kode + kode_lengkap + nomenklatur
program_kegiatans     → program_id + kode + kode_lengkap + nomenklatur
program_sub_kegiatans → kegiatan_id + kode + kode_lengkap + nomenklatur
```

### Data Excel (`prog_prio_baru.xlsx`)

File Excel memiliki struktur kolom:

| Kolom | Isi |
|---|---|
| A | No |
| B | Program Prioritas (misal: "Sungai Penuh Maju") |
| C | Rincian Program Prioritas (indikator teks) |
| D | Penjelasan |
| E | Nomor urut Program (SALAH — perlu dicocokkan ke master) |
| F | Nama Program SKPD |
| G | Nomor urut Kegiatan (F/G bergantian) |
| G | Nama Kegiatan |
| H | Nomor urut Sub Kegiatan |
| H | Nama Sub Kegiatan |
| I | Realisasi TW I — Fisik |
| J | Realisasi TW I — Keuangan |
| K | Realisasi TW II — Fisik |
| L | Realisasi TW II — Keuangan |
| M | Realisasi TW III — Fisik |
| N | Realisasi TW III — Keuangan |
| O | Realisasi TW IV — Fisik |
| P | Realisasi TW IV — Keuangan |
| Q | Penanggung Jawab (nama SKPD) |

> **Catatan penting:** Nomor rekening di Excel (kolom E/F/G/H) TIDAK bisa dipakai langsung. Harus dilakukan lookup ke tabel `program_sub_kegiatans` berdasarkan **kesamaan nama/nomenklatur** untuk mendapatkan `kode_lengkap` yang benar.

---

## Perubahan yang Diperlukan

### 1. Migrasi Database Baru

#### 1a. Ubah/Tambah Tabel `progul_indikators` → Relasi ke Sub Kegiatan

Tabel `progul_indikators` saat ini hanya menyimpan nama teks indikator. Perlu ditambah kolom `sub_kegiatan_id` untuk relasi ke master nomenklatur.

**File baru:** `database/migrations/2026_10_28_000001_add_sub_kegiatan_to_progul_indikators.php`

```php
public function up(): void
{
    Schema::table('progul_indikators', function (Blueprint $table) {
        $table->foreignId('sub_kegiatan_id')
              ->nullable()
              ->after('program_id')
              ->constrained('program_sub_kegiatans')
              ->nullOnDelete();
    });
}
```

#### 1b. Ubah Tabel `progul_realisasis` — Per Triwulan

Tabel existing `progul_realisasis` menggunakan `target_tahunan` (string) dan `nilai_realisasi` (string). Perlu diubah menjadi struktur per triwulan dengan kolom numerik terpisah.

**File baru:** `database/migrations/2026_10_28_000002_restructure_progul_realisasis_for_triwulan.php`

```php
public function up(): void
{
    Schema::table('progul_realisasis', function (Blueprint $table) {
        // Hapus kolom lama
        $table->dropColumn(['target_tahunan', 'nilai_realisasi', 'status', 'catatan_bappeda', 'submitted_at', 'verified_at']);
        
        // Tambah kolom triwulan
        $table->tinyInteger('triwulan')->after('tahun'); // 1, 2, 3, atau 4
        $table->decimal('target_fisik', 8, 2)->nullable()->after('triwulan');     // %
        $table->decimal('target_keuangan', 20, 2)->nullable()->after('target_fisik'); // Rp
        $table->decimal('realisasi_fisik', 8, 2)->nullable()->after('target_keuangan');    // %
        $table->decimal('realisasi_keuangan', 20, 2)->nullable()->after('realisasi_fisik'); // Rp
    });
    
    // Ubah unique key agar include triwulan
    Schema::table('progul_realisasis', function (Blueprint $table) {
        $table->dropUnique(['indikator_id', 'skpd_id', 'tahun']);
        $table->unique(['indikator_id', 'skpd_id', 'tahun', 'triwulan']);
    });
}
```

> **Perhatian:** Jika ada data existing di `progul_realisasis`, lakukan backup/truncate sebelum menjalankan migrasi ini, atau pisahkan langkah `dropColumn` dan tambah kolom baru.

---

### 2. Update Model

#### `app/Models/ProgulIndikator.php`

Tambah relasi ke `ProgramSubKegiatan`:

```php
use App\Models\ProgramSubKegiatan;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tambah di $fillable
protected $fillable = [
    'program_id',
    'sub_kegiatan_id',   // ← BARU
    'nama_indikator',
    'detail',
    'urutan',
];

// Tambah relasi
public function subKegiatan(): BelongsTo
{
    return $this->belongsTo(ProgramSubKegiatan::class, 'sub_kegiatan_id');
}
```

#### `app/Models/ProgulRealisasi.php`

Update `$fillable` dan `$casts` sesuai kolom baru:

```php
protected $fillable = [
    'indikator_id',
    'skpd_id',
    'tahun',
    'triwulan',            // ← BARU (1/2/3/4)
    'target_fisik',        // ← BARU (desimal %)
    'target_keuangan',     // ← BARU (desimal Rp)
    'realisasi_fisik',     // ← BARU (desimal %)
    'realisasi_keuangan',  // ← BARU (desimal Rp)
];

protected function casts(): array
{
    return [
        'target_fisik'       => 'decimal:2',
        'target_keuangan'    => 'decimal:2',
        'realisasi_fisik'    => 'decimal:2',
        'realisasi_keuangan' => 'decimal:2',
    ];
}
```

---

### 3. Update Data JSON Seeder (`data_progul.json`)

Format JSON baru yang harus dibuat dari data Excel. Struktur per **sub kegiatan** bukan per indikator teks. Setiap sub kegiatan memiliki data realisasi per triwulan.

**Lokasi:** `database/seeders/data/data_progul.json`

**Struktur format baru:**

```json
[
  {
    "program_unggulan": "Sungai Penuh Maju",
    "urutan": 1,
    "rincian": "Revitalisasi pasar rakyat dan perdagangan",
    "penjelasan": "Melakukan revitalisasi...",
    "penanggung_jawab": "Dinas Perindustrian dan Perdagangan",
    "program_skpd": [
      {
        "nama_program": "PROGRAM PENINGKATAN SARANA DISTRIBUSI PERDAGANGAN",
        "kegiatan": [
          {
            "nama_kegiatan": "Pembangunan dan Pengelolaan Sarana Distribusi Perdagangan",
            "sub_kegiatan": [
              {
                "nama_sub_kegiatan": "Penyediaan Sarana Distribusi Perdagangan",
                "tahun": 2025,
                "realisasi": [
                  {
                    "triwulan": 1,
                    "target_fisik": 25.00,
                    "target_keuangan": 0,
                    "realisasi_fisik": 0,
                    "realisasi_keuangan": 0
                  },
                  {
                    "triwulan": 2,
                    "target_fisik": 50.00,
                    "target_keuangan": 0,
                    "realisasi_fisik": 0,
                    "realisasi_keuangan": 0
                  },
                  {
                    "triwulan": 3,
                    "target_fisik": 75.00,
                    "target_keuangan": 0,
                    "realisasi_fisik": 0,
                    "realisasi_keuangan": 0
                  },
                  {
                    "triwulan": 4,
                    "target_fisik": 100.00,
                    "target_keuangan": 0,
                    "realisasi_fisik": 0,
                    "realisasi_keuangan": 0
                  }
                ]
              }
            ]
          }
        ]
      }
    ]
  }
]
```

> **Catatan:** Data fisik & keuangan per triwulan diisi dari kolom I–P di Excel. Jika kosong di Excel (data belum ada), isi `null` atau `0`. `target_keuangan` dapat dikosongkan (0) jika belum ada data dari renja, karena yang utama adalah target & realisasi fisik.

---

### 4. Lookup Nomor Rekening Sub Kegiatan dari Master

Saat seeder berjalan, untuk setiap `nama_sub_kegiatan` di JSON:

1. Query ke tabel `program_sub_kegiatans` dengan `LIKE` pada kolom `nomenklatur`.
2. Jika ditemukan → ambil `id` untuk dipakai sebagai `sub_kegiatan_id` di `progul_indikators`.
3. Jika tidak ditemukan → log warning, simpan `sub_kegiatan_id = null`.

**Logika lookup di seeder:**

```php
protected function resolveSubKegiatan(string $namaSubKegiatan): ?ProgramSubKegiatan
{
    // Coba exact match dulu
    $subKeg = ProgramSubKegiatan::where('nomenklatur', $namaSubKegiatan)->first();
    
    if (!$subKeg) {
        // Fallback: LIKE search dengan kata kunci utama
        $keywords = explode(' ', $namaSubKegiatan);
        $query = ProgramSubKegiatan::query();
        foreach (array_slice($keywords, 0, 5) as $word) {
            if (strlen($word) > 4) {
                $query->where('nomenklatur', 'LIKE', "%{$word}%");
            }
        }
        $subKeg = $query->first();
    }
    
    return $subKeg;
}
```

---

### 5. Refactor `ProgulSeeder.php`

**File:** `database/seeders/ProgulSeeder.php`

Seeder baru membaca `data_progul.json` format baru dan:
1. Upsert `ProgulProgram` berdasarkan `program_unggulan`
2. Upsert `ProgulIndikator` per sub kegiatan (dengan lookup `sub_kegiatan_id`)
3. Upsert `ProgulRealisasi` per triwulan

```php
public function run(): void
{
    $file = database_path('seeders/data/data_progul.json');
    $data = json_decode(file_get_contents($file), true);

    DB::transaction(function () use ($data) {
        foreach ($data as $urutan => $item) {
            // 1. Upsert Program Unggulan
            $program = ProgulProgram::updateOrCreate(
                ['nama_program' => trim($item['program_unggulan'])],
                ['urutan' => $urutan + 1]
            );

            $skpd = $this->resolveSkpd([$item['penanggung_jawab'] ?? '']);

            foreach ($item['program_skpd'] ?? [] as $progSkpd) {
                foreach ($progSkpd['kegiatan'] ?? [] as $keg) {
                    foreach ($keg['sub_kegiatan'] ?? [] as $urutanSk => $sk) {
                        $namaSubKeg = trim($sk['nama_sub_kegiatan']);
                        
                        // 2. Lookup sub_kegiatan_id dari master
                        $subKegiatan = $this->resolveSubKegiatan($namaSubKeg);

                        // 3. Upsert Indikator (per Sub Kegiatan)
                        $indikator = ProgulIndikator::updateOrCreate(
                            [
                                'program_id'     => $program->id,
                                'nama_indikator' => $namaSubKeg,
                            ],
                            [
                                'sub_kegiatan_id' => $subKegiatan?->id,
                                'detail'          => $item['penjelasan'] ?? null,
                                'urutan'          => $urutanSk + 1,
                            ]
                        );

                        // Simpan urusan dari SKPD penanggung jawab
                        ProgulIndikatorUrusan::where('indikator_id', $indikator->id)->delete();
                        if ($item['penanggung_jawab'] ?? '') {
                            ProgulIndikatorUrusan::create([
                                'indikator_id' => $indikator->id,
                                'nama_urusan'  => $item['penanggung_jawab'],
                            ]);
                        }

                        // 4. Upsert Realisasi Per Triwulan
                        $tahun = $sk['tahun'] ?? 2025;
                        foreach ($sk['realisasi'] ?? [] as $tw) {
                            ProgulRealisasi::updateOrCreate(
                                [
                                    'indikator_id' => $indikator->id,
                                    'skpd_id'      => $skpd?->id,
                                    'tahun'        => $tahun,
                                    'triwulan'     => $tw['triwulan'],
                                ],
                                [
                                    'target_fisik'       => $tw['target_fisik'] ?? null,
                                    'target_keuangan'    => $tw['target_keuangan'] ?? null,
                                    'realisasi_fisik'    => $tw['realisasi_fisik'] ?? null,
                                    'realisasi_keuangan' => $tw['realisasi_keuangan'] ?? null,
                                ]
                            );
                        }
                    }
                }
            }
        }
    });
}
```

---

### 6. Update Halaman Publik — Filter Tahun & Tabel Triwulan

#### `app/Livewire/PublicProgramPrioritas.php`

Tambah property `tahun` dan `availableYears`, ubah query agar filter per tahun:

```php
#[Url(as: 'tahun')]
public int $tahun = 2025;

public array $availableYears = [2025, 2026, 2027, 2028, 2029];

public function updatedTahun(): void
{
    // Reload data saat tahun berubah
}

public function render()
{
    $programs = ProgulProgram::withCount('indikators')
        ->orderBy('urutan')
        ->get();

    $selectedProgram = null;
    $indikators = collect();

    if ($this->selectedProgramId) {
        $selectedProgram = ProgulProgram::with([
            'indikators.subKegiatan.kegiatan.program',  // untuk kode rekening
            'indikators.urusan',
            'indikators.realisasis' => function ($q) {
                $q->where('tahun', $this->tahun)->orderBy('triwulan');
            },
            'indikators.realisasis.skpd',
        ])->find($this->selectedProgramId);

        if ($selectedProgram) {
            $indikators = $selectedProgram->indikators;
            // ... filter search & skpd tetap sama
        }
    }

    return view('livewire.public-program-prioritas', [
        'programs'        => $programs,
        'selectedProgram' => $selectedProgram,
        'indikators'      => $indikators,
        'tahun'           => $this->tahun,
        'availableYears'  => $this->availableYears,
    ]);
}
```

#### View `resources/views/livewire/public-program-prioritas.blade.php`

Tambahkan **filter dropdown tahun** di atas tabel, dan ubah struktur tabel menjadi per triwulan:

```blade
{{-- Filter Tahun --}}
<div class="flex items-center gap-3 mb-6">
    <span class="text-xs text-gray-400 font-medium">Tahun:</span>
    @foreach ($availableYears as $year)
        <button
            wire:click="$set('tahun', {{ $year }})"
            class="px-4 py-1.5 rounded-full text-xs font-bold transition-all
                {{ $tahun === $year
                    ? 'bg-teal-500 text-white shadow-md shadow-teal-500/30'
                    : 'bg-[#0c2a2b] border border-[#164e50] text-teal-300/70 hover:border-teal-400/40' }}">
            {{ $year }}
        </button>
    @endforeach
</div>

{{-- Tabel Sub Kegiatan dengan Target & Realisasi Per Triwulan --}}
<div class="overflow-x-auto rounded-xl border border-[#164e50]">
    <table class="w-full text-xs text-left text-gray-300">
        <thead class="bg-[#0c3a3d] text-teal-300">
            <tr>
                <th class="px-4 py-3">No Rekening</th>
                <th class="px-4 py-3">Sub Kegiatan</th>
                <th class="px-4 py-3 text-center" colspan="2">TW I</th>
                <th class="px-4 py-3 text-center" colspan="2">TW II</th>
                <th class="px-4 py-3 text-center" colspan="2">TW III</th>
                <th class="px-4 py-3 text-center" colspan="2">TW IV</th>
                <th class="px-4 py-3">Penanggung Jawab</th>
            </tr>
            <tr class="bg-[#0a2f32] text-teal-400/70 text-[10px]">
                <th></th><th></th>
                <th class="px-2 py-1 text-center">Target</th>
                <th class="px-2 py-1 text-center">Realisasi</th>
                <th class="px-2 py-1 text-center">Target</th>
                <th class="px-2 py-1 text-center">Realisasi</th>
                <th class="px-2 py-1 text-center">Target</th>
                <th class="px-2 py-1 text-center">Realisasi</th>
                <th class="px-2 py-1 text-center">Target</th>
                <th class="px-2 py-1 text-center">Realisasi</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($indikators as $ind)
                @php
                    $twData = $ind->realisasis->keyBy('triwulan');
                    $kode = $ind->subKegiatan?->kode_lengkap ?? '-';
                @endphp
                <tr class="border-t border-[#164e50] hover:bg-[#0f3436]/50 transition-colors">
                    <td class="px-4 py-3 font-mono text-teal-400 whitespace-nowrap">{{ $kode }}</td>
                    <td class="px-4 py-3">{{ $ind->nama_indikator }}</td>
                    @for ($tw = 1; $tw <= 4; $tw++)
                        @php $r = $twData->get($tw); @endphp
                        <td class="px-2 py-3 text-center text-gray-400">
                            {{ $r?->target_fisik !== null ? $r->target_fisik . '%' : '-' }}
                        </td>
                        <td class="px-2 py-3 text-center {{ $r?->realisasi_fisik >= $r?->target_fisik ? 'text-teal-300' : 'text-amber-300' }}">
                            {{ $r?->realisasi_fisik !== null ? $r->realisasi_fisik . '%' : '-' }}
                        </td>
                    @endfor
                    <td class="px-4 py-3 text-gray-400">
                        {{ $ind->realisasis->first()?->skpd?->nama_skpd ?? ($ind->urusan->first()?->nama_urusan ?? '-') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

---

## Urutan Eksekusi

```
1. Buat data_progul.json baru (dari Excel, format sub kegiatan + triwulan)
   → Perhatikan: lookup nama sub kegiatan ke master nomenklatur untuk validasi

2. php artisan make:migration add_sub_kegiatan_to_progul_indikators
3. php artisan make:migration restructure_progul_realisasis_for_triwulan
4. php artisan migrate

5. Update app/Models/ProgulIndikator.php (tambah fillable + relasi)
6. Update app/Models/ProgulRealisasi.php (update fillable + casts)

7. Refactor database/seeders/ProgulSeeder.php (logika baru)

8. php artisan db:seed --class=ProgulSeeder   ← aman di-run ulang (updateOrCreate)

9. Update app/Livewire/PublicProgramPrioritas.php (tambah filter tahun)
10. Update resources/views/livewire/public-program-prioritas.blade.php (tabel triwulan)
```

---

## File yang Perlu Dibuat/Diubah

| File | Aksi | Prioritas |
|---|---|---|
| `database/seeders/data/data_progul.json` | **Buat ulang** — format sub kegiatan + triwulan dari Excel | 🔴 Utama |
| `database/migrations/..._add_sub_kegiatan_to_progul_indikators.php` | **Buat baru** | 🔴 Utama |
| `database/migrations/..._restructure_progul_realisasis_for_triwulan.php` | **Buat baru** | 🔴 Utama |
| `database/seeders/ProgulSeeder.php` | **Refactor total** | 🔴 Utama |
| `app/Models/ProgulIndikator.php` | **Update** — tambah `sub_kegiatan_id` + relasi | 🔴 Utama |
| `app/Models/ProgulRealisasi.php` | **Update** — kolom triwulan baru | 🔴 Utama |
| `app/Livewire/PublicProgramPrioritas.php` | **Update** — tambah filter tahun | 🟡 Frontend |
| `resources/views/livewire/public-program-prioritas.blade.php` | **Update** — tabel triwulan + filter tahun | 🟡 Frontend |

## File yang TIDAK Perlu Diubah

| File | Alasan |
|---|---|
| `app/Models/ProgulProgram.php` | Struktur program unggulan tidak berubah |
| `app/Models/ProgulIndikatorUrusan.php` | Tetap dipakai untuk urusan |
| `app/Models/ProgulBuktiDukung.php` | Bukti dukung tidak berubah |
| `app/Filament/Resources/ProgulPrograms/` | Resource Filament admin tidak scope utama |
| `database/migrations/2026_10_20_*` | Migrasi existing tidak diubah, cukup tambah migrasi baru |

---

## Catatan Penting untuk Implementasi

> [!IMPORTANT]
> **Lookup Nomor Rekening Wajib Dilakukan dengan Hati-hati**
> Nomor rekening di Excel salah. Gunakan nama sub kegiatan sebagai kunci pencarian ke tabel `program_sub_kegiatans`. Jika nama tidak match 100%, lakukan pencocokan keyword. Catat semua sub kegiatan yang tidak ditemukan dan laporkan untuk review manual.

> [!WARNING]
> **Migrasi `progul_realisasis` Bersifat Destruktif**
> Kolom lama (`target_tahunan`, `nilai_realisasi`, `status`, dll.) akan dihapus. Pastikan tabel ini di-truncate atau tidak ada data penting sebelum migrate. Di production, gunakan `db:seed --class=ProgulSeeder` setelah migrate untuk re-seed.

> [!NOTE]
> **Format Target Fisik di Tabel**
> Kolom target & realisasi fisik menggunakan **persentase desimal** (misal: `25.00` = 25%). Kolom keuangan menggunakan **nominal Rupiah**. Jika data keuangan dari renja belum tersedia saat ini, isi `0` atau `null` dan bisa diupdate kemudian via Filament admin.

> [!TIP]
> **Filter Tahun di Halaman Publik**
> Default filter tahun = tahun berjalan (2025). Gunakan `wire:navigate` atau `$set` via Livewire agar tidak full page reload. Tombol tahun sebaiknya ditampilkan sebagai pill/badge yang aktif, sesuai desain existing halaman.

