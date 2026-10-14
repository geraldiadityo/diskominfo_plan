# RENCANA IMPLEMENTASI (PLAN 4): Mapping Kode Urusan ke Kode Bidang SKPD di Tampilan Renja SKPD

Dokumen ini adalah **panduan instruksi teknis lengkap** bagi AI Agent / Software Engineer untuk mengimplementasikan fitur **pemetaan tampilan kode urusan (X.XX) menjadi kode bidang SKPD** di modul Renja SKPD, **tanpa mengubah data master program kegiatan maupun data yang tersimpan di tabel `renja_skpds`**.

---

## 1. Latar Belakang & Tujuan

### Konteks Masalah

Di menu **Master Program Kegiatan** (`ProgramUrusan`), setiap urusan memiliki format kode `X.XX` (contoh: `1.10`, `2.01`). Di bawah urusan terdapat **bidang** (`ProgramBidang`) yang masing-masing memiliki `kode_lengkap` tersendiri (contoh: `1.10.01`, `1.10.02`).

Ketika pengguna membuat **Renja SKPD** dan memilih sub kegiatan dari master program kegiatan, pada form dropdown **urusan** ditampilkan label seperti:
> `1.10 - Urusan Pemerintahan Bidang Komunikasi Dan Informatika`

Setelah data disimpan dan ditampilkan kembali di tabel/form Renja SKPD, kode yang muncul tetap mengacu pada kode **urusan** (`X.XX`), padahal setiap SKPD seharusnya punya **kode bidang urusannya sendiri** (yang lebih spesifik, misalnya `1.10.01`).

### Yang Harus Dilakukan

Tambahkan fitur **mapping antara SKPD dan ProgramBidang** (bidang dalam master program kegiatan), sehingga:

1. Di menu **SKPD** (kelola data SKPD), admin dapat menetapkan **kode bidang** yang menjadi milik SKPD tersebut (berupa relasi ke `ProgramBidang`).
2. Ketika menampilkan kode urusan di modul **Renja SKPD** (form, tabel, dan tampilan lainnya), sistem akan menggantikan tampilan kode `X.XX` (urusan) dengan **kode bidang dari mapping SKPD** yang sesuai.
3. **Data yang tersimpan di database tidak berubah** — tidak ada perubahan pada tabel `program_urusans`, `program_bidangs`, maupun `renja_skpds`. Perubahan hanya terjadi pada **layer presentasi / tampilan**.

---

## 2. Struktur Data yang Sudah Ada

```
ProgramUrusan      (program_urusans)
  - id
  - kode          → contoh: "1.10"
  - nomenklatur   → contoh: "Urusan Pemerintahan Bidang Komunikasi Dan Informatika"
  └── HasMany → ProgramBidang

ProgramBidang      (program_bidangs)
  - id
  - urusan_id     → FK ke program_urusans
  - kode          → contoh: "01"
  - kode_lengkap  → contoh: "1.10.01"
  - nomenklatur   → contoh: "Dinas Komunikasi Dan Informatika"
  └── BelongsTo → ProgramUrusan
  └── HasMany → ProgramProgram

Skpd               (skpds)
  - id
  - kode_skpd     → contoh: "2.16.0.00.0.00.01.0000"
  - nama_skpd     → contoh: "DINAS KOMUNIKASI DAN INFORMATIKA"

RenjaSkpd          (renja_skpds)
  - id
  - skpd_id       → FK ke skpds
  - sub_kegiatan_id → FK ke program_sub_kegiatans
  - tahun
  - pagu_anggaran
  - target_keuangan
  - target_fisik
  - target_kinerja
```

---

## 3. Rencana Implementasi

### Langkah 1: Tambahkan Kolom `bidang_id` ke Tabel `skpds`

Buat migration baru untuk menambahkan kolom `bidang_id` (nullable, FK ke `program_bidangs`) pada tabel `skpds`.

**File baru:** `database/migrations/YYYY_MM_DD_HHMMSS_add_bidang_id_to_skpds_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skpds', function (Blueprint $table) {
            $table->foreignId('bidang_id')
                ->nullable()
                ->after('nama_skpd')
                ->constrained('program_bidangs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('skpds', function (Blueprint $table) {
            $table->dropForeign(['bidang_id']);
            $table->dropColumn('bidang_id');
        });
    }
};
```

Setelah membuat file migration, jalankan:
```bash
php artisan migrate
```

---

### Langkah 2: Perbarui Model `Skpd`

Tambahkan `bidang_id` ke `$fillable` dan tambahkan relasi `bidang()` ke `ProgramBidang`.

**File:** [`app/Models/Skpd.php`](file:///Users/mac/Herd/diskominfo_plan/app/Models/Skpd.php)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skpd extends Model
{
    protected $fillable = [
        'kode_skpd',
        'nama_skpd',
        'bidang_id',       // ← TAMBAH INI
    ];

    /**
     * Relasi ke bidang dalam master program kegiatan.
     * Digunakan untuk menggantikan tampilan kode urusan (X.XX)
     * menjadi kode bidang SKPD ini di modul Renja SKPD.
     */
    public function bidang(): BelongsTo  // ← TAMBAH INI
    {
        return $this->belongsTo(ProgramBidang::class, 'bidang_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'skpd_id');
    }

    public function indikator(): HasMany
    {
        return $this->hasMany(MasterIndikator::class, 'skpd_id');
    }

    public function realisasi(): HasMany
    {
        return $this->hasMany(IndikatorRealisasi::class, 'skpd_id');
    }

    public function target_pendapatan(): HasMany
    {
        return $this->hasMany(TargetPendapatan::class, 'skpd_id');
    }
}
```

---

### Langkah 3: Perbarui Form SKPD — Tambahkan Select `bidang_id`

Perbarui form SKPD agar admin dapat memilih bidang yang menjadi milik SKPD tersebut.

**File:** [`app/Filament/Resources/Skpds/Schemas/SkpdForm.php`](file:///Users/mac/Herd/diskominfo_plan/app/Filament/Resources/Skpds/Schemas/SkpdForm.php)

```php
<?php

namespace App\Filament\Resources\Skpds\Schemas;

use App\Models\ProgramBidang;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SkpdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_skpd')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('nama_skpd')
                    ->required()
                    ->maxLength(255),

                // ↓ TAMBAH INI: Pilih bidang yang di-mapping ke SKPD ini
                Select::make('bidang_id')
                    ->label('Bidang (Mapping Kode Urusan)')
                    ->helperText('Pilih bidang dari master program kegiatan yang sesuai dengan SKPD ini. Kode bidang akan ditampilkan menggantikan kode urusan (X.XX) di tampilan Renja SKPD.')
                    ->options(
                        ProgramBidang::with('urusan')
                            ->get()
                            ->mapWithKeys(fn($b) => [
                                $b->id => $b->kode_lengkap . ' - ' . $b->nomenklatur
                                    . ' (' . ($b->urusan?->nomenklatur ?? 'Urusan?') . ')'
                            ])
                    )
                    ->searchable()
                    ->nullable()
                    ->placeholder('-- Pilih Bidang (Opsional) --'),
            ]);
    }
}
```

---

### Langkah 4: Perbarui Tabel SKPD — Tampilkan Kolom Bidang

Perbarui tabel daftar SKPD agar kolom bidang yang di-mapping tampil sebagai informasi.

**File:** [`app/Filament/Resources/Skpds/Tables/SkpdsTable.php`](file:///Users/mac/Herd/diskominfo_plan/app/Filament/Resources/Skpds/Tables/SkpdsTable.php)

```php
<?php

namespace App\Filament\Resources\Skpds\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SkpdsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_skpd')
                    ->label('Kode SKPD')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama_skpd')
                    ->label('Nama SKPD')
                    ->sortable()
                    ->searchable(),

                // ↓ TAMBAH INI: Tampilkan kode bidang yang di-mapping
                TextColumn::make('bidang.kode_lengkap')
                    ->label('Kode Bidang (Mapping)')
                    ->placeholder('Belum di-mapping')
                    ->badge()
                    ->color('info'),

                TextColumn::make('bidang.nomenklatur')
                    ->label('Nama Bidang')
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

---

### Langkah 5: Aktifkan Halaman Edit di SkpdResource

Saat ini `CreateSkpd` dan `EditSkpd` dikomentari. Aktifkan halaman `EditSkpd` agar admin bisa mengedit SKPD dan mengisi `bidang_id`.

**File:** [`app/Filament/Resources/Skpds/SkpdResource.php`](file:///Users/mac/Herd/diskominfo_plan/app/Filament/Resources/Skpds/SkpdResource.php)

Ubah bagian `getPages()`:

```php
// SEBELUM:
public static function getPages(): array
{
    return [
        'index' => ListSkpds::route('/'),
        // 'create' => CreateSkpd::route('/create'),
        // 'edit' => EditSkpd::route('/{record}/edit'),
    ];
}

// SESUDAH:
public static function getPages(): array
{
    return [
        'index'  => ListSkpds::route('/'),
        'create' => CreateSkpd::route('/create'),
        'edit'   => EditSkpd::route('/{record}/edit'),
    ];
}
```

---

### Langkah 6: Buat Helper / Method untuk Mendapatkan Kode Bidang dari Mapping SKPD

Buat sebuah static helper method (bisa di `Skpd` model atau di service class) untuk mengambil kode bidang berdasarkan `skpd_id`. Method ini akan dipakai di layer tampilan.

**Tambahkan method berikut di** [`app/Models/Skpd.php`](file:///Users/mac/Herd/diskominfo_plan/app/Models/Skpd.php) **(di dalam class `Skpd`)**:

```php
/**
 * Dapatkan kode bidang yang di-mapping ke SKPD ini.
 * Jika belum di-mapping, kembalikan kode urusan asli sebagai fallback.
 *
 * @param  string|null  $kodeUruan  Kode urusan asli (X.XX) sebagai fallback
 * @return string
 */
public function getKodeBidangDisplay(?string $kodeUrusan = null): string
{
    if ($this->bidang) {
        return $this->bidang->kode_lengkap;
    }

    // Fallback: kembalikan kode urusan asli jika belum ada mapping
    return $kodeUrusan ?? '';
}
```

---

### Langkah 7: Perbarui Tampilan di Form Renja SKPD (Select Urusan)

Di form Renja SKPD ([`app/Filament/Resources/RenjaSkpds/Schemas/RenjaSkpdForm.php`](file:///Users/mac/Herd/diskominfo_plan/app/Filament/Resources/RenjaSkpds/Schemas/RenjaSkpdForm.php)), ubah label/hint pada Select `urusan_id` agar saat **menampilkan** label setelah dipilih, kode yang terlihat diganti dengan kode bidang dari mapping SKPD.

Caranya gunakan `getOptionLabelUsing()` pada Select `urusan_id` untuk menampilkan kode bidang SKPD yang login (bukan kode urusan asli `X.XX`):

```php
// Di dalam Select::make('urusan_id')
// Tambahkan ->getOptionLabelUsing() untuk mengubah tampilan label ketika sudah dipilih

Select::make('urusan_id')
    ->label('1. Urusan')
    ->options(ProgramUrusan::query()->pluck('nomenklatur', 'id'))
    ->searchable()
    ->live()
    ->afterStateUpdated(fn(Set $set) => $set('bidang_id', null) ?? $set('program_id', null) ?? $set('kegiatan_id', null) ?? $set('sub_kegiatan_id', null))
    ->dehydrated(false)
    // ↓ TAMBAH INI: Ganti tampilan kode urusan dengan kode bidang SKPD
    ->getOptionLabelUsing(function ($value) {
        if (!$value) return null;

        $urusan = \App\Models\ProgramUrusan::find($value);
        if (!$urusan) return null;

        // Cek apakah SKPD yang sedang login/aktif punya mapping bidang
        $skpdId = \Illuminate\Support\Facades\Auth::user()?->skpd_id;
        $skpd   = $skpdId ? \App\Models\Skpd::with('bidang.urusan')->find($skpdId) : null;

        // Jika SKPD punya mapping bidang DAN bidang tersebut berada di bawah urusan ini
        if ($skpd?->bidang && $skpd->bidang->urusan_id == $value) {
            // Tampilkan kode bidang SKPD, bukan kode urusan
            return $skpd->bidang->kode_lengkap . ' - ' . $urusan->nomenklatur;
        }

        // Fallback: tampilkan kode urusan asli
        return $urusan->kode . ' - ' . $urusan->nomenklatur;
    }),
```

> **Catatan penting**: `getOptionLabelUsing()` hanya mengubah **label yang ditampilkan** di UI setelah opsi dipilih. Nilai yang disimpan ke database tetap `id` dari `ProgramUrusan`, **tidak berubah sama sekali**.

---

### Langkah 8: Perbarui Tampilan Kolom Urusan di Tabel Renja SKPD

Di tabel Renja SKPD ([`app/Filament/Resources/RenjaSkpds/Tables/RenjaSkpdsTable.php`](file:///Users/mac/Herd/diskominfo_plan/app/Filament/Resources/RenjaSkpds/Tables/RenjaSkpdsTable.php)), saat ini kolom urusan ditampilkan dengan:

```php
TextColumn::make('subKegiatan.kegiatan.program.bidang.urusan.nomenklatur')
    ->label('Urusan')
```

Ubah kolom ini agar menampilkan **kode bidang dari mapping SKPD**, bukan kode urusan `X.XX`. Gunakan `formatStateUsing()` atau `state()` dengan `getStateUsing()`:

```php
// SEBELUM:
TextColumn::make('subKegiatan.kegiatan.program.bidang.urusan.nomenklatur')
    ->label('Urusan')
    ->wrap()
    ->toggleable(isToggledHiddenByDefault: true),

// SESUDAH:
TextColumn::make('subKegiatan.kegiatan.program.bidang.urusan.nomenklatur')
    ->label('Urusan')
    ->wrap()
    ->toggleable(isToggledHiddenByDefault: true),

// ↓ TAMBAH KOLOM BARU ini tepat setelah kolom Urusan:
TextColumn::make('kode_bidang_skpd')
    ->label('Kode Bidang (SKPD)')
    ->getStateUsing(function ($record) {
        // Ambil SKPD dari record Renja
        $skpd = $record->skpd;
        if (!$skpd) return '-';

        // Load relasi bidang jika belum di-load
        $skpd->loadMissing('bidang');

        // Ambil urusan dari sub kegiatan → kegiatan → program → bidang → urusan
        $urusan = $record->subKegiatan?->kegiatan?->program?->bidang?->urusan;

        // Jika SKPD punya mapping bidang AND bidang tersebut sesuai urusan sub kegiatan ini
        if ($skpd->bidang && $urusan && $skpd->bidang->urusan_id === $urusan->id) {
            return $skpd->bidang->kode_lengkap;
        }

        // Fallback: tampilkan kode urusan asli
        return $urusan?->kode ?? '-';
    })
    ->badge()
    ->color('info')
    ->toggleable(isToggledHiddenByDefault: false),
```

---

### Langkah 9: Pastikan Eager Loading di `RenjaSkpdResource::getEloquentQuery()` Sudah Mencakup Relasi `skpd.bidang`

**File:** [`app/Filament/Resources/RenjaSkpds/RenjaSkpdResource.php`](file:///Users/mac/Herd/diskominfo_plan/app/Filament/Resources/RenjaSkpds/RenjaSkpdResource.php)

Saat ini query sudah memuat `skpd` dan `subKegiatan.kegiatan.program.bidang.urusan`. Pastikan relasi `skpd.bidang` juga di-load agar tidak ada N+1 query:

```php
#[Override]
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery()->with([
        'skpd.bidang',                           // ← UBAH dari 'skpd' menjadi 'skpd.bidang'
        'subKegiatan.kegiatan.program.bidang.urusan'
    ]);

    if (Auth::user()?->role === 'SKPD') {
        $query->where('skpd_id', Auth::user()->skpd_id);
    }

    return $query;
}
```

---

## 4. Ringkasan Perubahan File

| No | File | Jenis Perubahan |
|----|------|-----------------|
| 1 | `database/migrations/YYYY_MM_DD_add_bidang_id_to_skpds_table.php` | **BUAT BARU** — Tambah kolom `bidang_id` ke tabel `skpds` |
| 2 | `app/Models/Skpd.php` | **EDIT** — Tambah `bidang_id` ke `$fillable`, tambah relasi `bidang()`, tambah method `getKodeBidangDisplay()` |
| 3 | `app/Filament/Resources/Skpds/Schemas/SkpdForm.php` | **EDIT** — Tambah `Select::make('bidang_id')` |
| 4 | `app/Filament/Resources/Skpds/Tables/SkpdsTable.php` | **EDIT** — Tambah kolom `bidang.kode_lengkap` dan `bidang.nomenklatur` |
| 5 | `app/Filament/Resources/Skpds/SkpdResource.php` | **EDIT** — Aktifkan route `create` dan `edit` |
| 6 | `app/Filament/Resources/RenjaSkpds/Schemas/RenjaSkpdForm.php` | **EDIT** — Tambah `getOptionLabelUsing()` pada Select `urusan_id` |
| 7 | `app/Filament/Resources/RenjaSkpds/Tables/RenjaSkpdsTable.php` | **EDIT** — Tambah kolom virtual `kode_bidang_skpd` dengan `getStateUsing()` |
| 8 | `app/Filament/Resources/RenjaSkpds/RenjaSkpdResource.php` | **EDIT** — Update eager loading dari `'skpd'` menjadi `'skpd.bidang'` |

---

## 5. Alur Kerja (Flow) Fitur

```
[ADMIN] Menu SKPD
    → Edit SKPD
    → Pilih "Bidang (Mapping Kode Urusan)" dari dropdown
      (contoh: "1.10.01 - Dinas Komunikasi Dan Informatika (Urusan Pemerintahan Bidang Komunikasi Dan Informatika)")
    → Simpan

[USER SKPD] Menu Renja SKPD → Tambah / Edit
    → Pilih Urusan (contoh: "Urusan Pemerintahan Bidang Komunikasi Dan Informatika")
    → Setelah dipilih, label yang tampil: "1.10.01 - Urusan Pemerintahan Bidang Komunikasi Dan Informatika"
      (kode bidang SKPD, bukan kode urusan "1.10")
    → Data yang DISIMPAN ke DB: tetap id urusan, TIDAK BERUBAH

[USER SKPD] Melihat daftar Renja SKPD (tabel)
    → Kolom "Kode Bidang (SKPD)" menampilkan "1.10.01" (badge biru)
      bukan "1.10" (kode urusan asli)
    → Data di DB tetap tidak berubah
```

---

## 6. Batasan & Aturan Penting

> [!IMPORTANT]
> **Tidak ada perubahan data.** Semua modifikasi hanya bersifat **presentasi/tampilan**. Data yang tersimpan di tabel `program_urusans`, `program_bidangs`, dan `renja_skpds` **tidak boleh berubah**.

> [!NOTE]
> **Mapping bersifat opsional.** Kolom `bidang_id` di tabel `skpds` bersifat `nullable`. Jika sebuah SKPD belum di-mapping ke bidang manapun, sistem akan **fallback** menampilkan kode urusan asli (`X.XX`), sehingga tidak ada tampilan yang rusak/kosong.

> [!NOTE]
> **Satu SKPD — Satu Bidang.** Relasi mapping adalah `Skpd belongsTo ProgramBidang` (satu bidang per SKPD). Jika di masa depan perlu banyak bidang per SKPD, model perlu diubah menjadi many-to-many (pivot table `skpd_bidang`), tapi untuk saat ini cukup satu.

> [!WARNING]
> **Jangan ubah kode di `ProgramUrusanResource`, `ProgramBidangResource`, atau relasi di master program kegiatan lainnya.** Perubahan hanya dilakukan di lapisan Renja SKPD dan SKPD saja.

---

## 7. Contoh Hasil Tampilan

### Sebelum (kode urusan asli):
| Kolom Urusan | Kode |
|---|---|
| Urusan Pemerintahan Bidang Komunikasi Dan Informatika | `1.10` |

### Sesudah (kode bidang SKPD dari mapping):
| Kolom Urusan | Kode Bidang (SKPD) |
|---|---|
| Urusan Pemerintahan Bidang Komunikasi Dan Informatika | `1.10.01` *(badge biru)* |

Data di database tetap:
- `program_urusans.kode` = `1.10` ✅ tidak berubah
- `renja_skpds.sub_kegiatan_id` = (id lama) ✅ tidak berubah
- `skpds.bidang_id` = (id ProgramBidang) ← **hanya ini yang ditambahkan**

