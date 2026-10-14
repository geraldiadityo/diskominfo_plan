# RENCANA IMPLEMENTASI (PLAN 3): Filter Data Renja SKPD & Laporan Realisasi Berdasarkan SKPD untuk Verifikator & Administrator Non-Admin SKPD

Dokumen ini ditujukan sebagai **panduan instruksi teknis lengkap dan presisi** bagi AI Agent / Software Engineer untuk mengimplementasikan dan menyempurnakan fitur **Filter Data Berdasarkan SKPD** pada:
1. Modul Admin Panel **Renja SKPD** (`App\Filament\Resources\RenjaSkpds\RenjaSkpdResource`).
2. Halaman **Laporan Realisasi Fisik dan Keuangan** (`App\Filament\Pages\LaporanRealisasi` & view terkait).

Khusus untuk pengguna dengan peran (role) **selain dari Admin SKPD** (yaitu Verifikator BAPPEDA, Verifikator BAKEUDA, dan Administrator Aplikasi), filter SKPD dapat dipilih secara bebas. Sedangkan untuk pengguna dengan peran **Admin SKPD (`SKPD`)**, data **terkunci otomatis hanya untuk instansi miliknya** dan **elemen pilihan SKPD dibuat `disabled` (terkunci/tidak dapat dipilih/diubah)**.

---

## 1. Ringkasan Eksekutif & Latar Belakang

### 1.1. Konteks Masalah
1. **Pada Modul Renja SKPD (`RenjaSkpdResource` / `RenjaSkpdsTable`)**:
   - **Role Selain Admin SKPD (`BAPPEDA`, `BAKEUDA`, `ADMIN`)**: Memuat seluruh data kegiatan dari seluruh SKPD tanpa adanya filter pilihan SKPD (hanya ada filter tahun). Kolom nama SKPD disembunyikan secara *default*, menyulitkan verifikator Bappeda dan Bakeuda dalam menyaring kegiatan instansi tertentu yang sedang dievaluasi.
   - **Role Admin SKPD (`SKPD`)**: Data sudah dibatasi ke SKPD miliknya, namun filter SKPD tidak boleh muncul agar tidak membingungkan.
2. **Pada Halaman Laporan Realisasi Fisik & Keuangan (`LaporanRealisasi`)**:
   - Halaman ini menyajikan lembar matriks evaluasi 18 kolom (Program, Kegiatan, Sub Kegiatan, Indikator Bukti Fisik, dan Keuangan) serta tombol unduh Excel & Cetak.
   - **Role Selain Admin SKPD (`BAPPEDA`, `BAKEUDA`, `ADMIN`)**: Wajib dapat memilih dan berganti SKPD secara bebas melalui dropdown filter guna memeriksa dan mengunduh laporan realisasi dari SKPD mana pun yang diverifikasi.
   - **Role Admin SKPD (`SKPD`)**: **TIDAK BOLEH bisa memilih SKPD lain**. Admin SKPD hanya berhak melihat data realisasi instansinya sendiri. Elemen pilihan SKPD pada toolbar filter wajib berstatus **`disabled` (terkunci)** dan menampilkan nama SKPD yang bersangkutan, serta backend wajib menolak perubahan nilai SKPD.

### 1.2. Sasaran Implementasi
1. **Tabel Renja SKPD**:
   - Menambahkan `SelectFilter` SKPD yang *searchable* dan *preloaded* khusus untuk role selain SKPD (`Auth::user()->role !== 'SKPD'`).
   - Menjadikan kolom nama SKPD tampil secara *default* (`isToggledHiddenByDefault: false`) untuk role selain SKPD.
   - Eager loading relasi `skpd` di `getEloquentQuery()` untuk mencegah *N+1 query*.
2. **Halaman Laporan Realisasi Fisik & Keuangan**:
   - **Role Selain SKPD**: Dropdown pilihan SKPD aktif dan interaktif (`wire:model.live="selectedSkpdId"`), memuat seluruh daftar SKPD terurut abjad.
   - **Role Admin SKPD**: Pilihan SKPD berstatus **`disabled`**, menampilkan nama SKPD miliknya, dan tidak dapat diklik atau diubah.
   - **Backend Guard**: Server-side validation pada lifecycle Livewire (`mount()`, `updatedSelectedSkpdId()`, `getLaporanData()`, `downloadExcel()`) memastikan role SKPD tidak dapat memanipulasi `selectedSkpdId` melalui URL query parameter atau manipulasi state Livewire.

---

## 2. Matriks Hak Akses & Aturan Bisnis

### 2.1. Matriks Akses Antar Role
| Role Sistem | Label Pengguna | Hak Akses di Tabel Renja SKPD | Hak Akses di Laporan Realisasi Fisik & Keuangan | Status Elemen Pilihan SKPD di Laporan |
|:---|:---|:---|:---|:---:|
| `SKPD` | **ADMIN SKPD** | - Query terkunci ke SKPD login<br>- Filter SKPD disembunyikan<br>- Kolom SKPD disembunyikan | - Data terkunci ke SKPD login (`skpd_id`)<br>- Tidak bisa memilih SKPD lain<br>- Hanya melihat laporan instansinya sendiri | **DISABLED** (Terkunci, hanya baca nama SKPD miliknya) |
| `BAPPEDA` | **VERIFIKATOR BAPPEDA** | - Query semua SKPD<br>- Filter SKPD aktif & searchable<br>- Kolom SKPD tampak default | - Dapat memilih SKPD mana saja<br>- Meninjau capaian fisik & keuangan seluruh SKPD | **ENABLED** (Dropdown Aktif) |
| `BAKEUDA` | **VERIFIKATOR BAKEUDA** | - Query semua SKPD<br>- Filter SKPD aktif & searchable<br>- Kolom SKPD tampak default | - Dapat memilih SKPD mana saja<br>- Meninjau serapan & anggaran seluruh SKPD | **ENABLED** (Dropdown Aktif) |
| `ADMIN` | **ADMINISTRATOR APLIKASI** | - Akses penuh seluruh SKPD<br>- Filter SKPD aktif & searchable<br>- Kolom SKPD tampak default | - Akses penuh seluruh SKPD<br>- Bebas memilih instansi mana pun | **ENABLED** (Dropdown Aktif) |

### 2.2. Standar Logika Pengecekan
- **Bukan Admin SKPD (Verifikator/Admin)**:
  ```php
  $isNotSkpd = Auth::user()?->role !== 'SKPD';
  // atau
  $isVerifikatorOrAdmin = in_array(Auth::user()?->role, ['BAPPEDA', 'BAKEUDA', 'ADMIN']);
  ```
- **Admin SKPD**:
  ```php
  $isSkpd = Auth::user()?->role === 'SKPD';
  ```

---

## 3. Arsitektur Teknis & File yang Terlibat

```mermaid
flowchart TD
    User([Pengguna Login]) --> CheckRole{Role == 'SKPD'?}

    %% Cabang Admin SKPD
    CheckRole -- Ya (Admin SKPD) --> LimitRenja[RenjaSkpd: Query terkunci skpd_id milik user]
    LimitRenja --> HideRenjaFilter[Filter SKPD Tabel: Disembunyikan]
    CheckRole -- Ya (Admin SKPD) --> LockLaporan[Laporan: selectedSkpdId dipaksa Auth::user()->skpd_id]
    LockLaporan --> DisabledSelect[Toolbar Laporan: Input/Select SKPD DISABLED]
    DisabledSelect --> ViewOwnReport[Hanya Lihat Laporan SKPD Sendiri]

    %% Cabang Non-SKPD
    CheckRole -- Tidak (BAPPEDA/BAKEUDA/ADMIN) --> AllowRenja[RenjaSkpd: Query memuat seluruh SKPD]
    AllowRenja --> ShowRenjaFilter[Filter SKPD Tabel: Muncul & Searchable]
    CheckRole -- Tidak (BAPPEDA/BAKEUDA/ADMIN) --> AllowLaporan[Laporan: selectedSkpdId bebas dipilih]
    AllowLaporan --> EnabledSelect[Toolbar Laporan: Select SKPD ENABLED & Livewire Live]
    EnabledSelect --> ViewAnyReport[Verifikator Bebas Pilih & Evaluasi Semua SKPD]
```

### Daftar File yang Terkena Dampak:
1. `app/Filament/Resources/RenjaSkpds/Tables/RenjaSkpdsTable.php`
   - Filter `skpd_id` untuk role non-SKPD.
   - Menampilkan kolom `skpd.nama_skpd` secara default.
   - Tata letak filter `FiltersLayout::AboveContent`.
2. `app/Filament/Resources/RenjaSkpds/RenjaSkpdResource.php`
   - Eager loading `'skpd'` di `getEloquentQuery()`.
3. `app/Filament/Resources/RenjaSkpds/RelationManagers/RealisasiKegiatanRelationManager.php`
   - Perbaikan otorisasi tombol verifikasi dan input solusi untuk role `BAPPEDA` dan `BAKEUDA`.
4. `app/Filament/Pages/LaporanRealisasi.php`
   - Penguncian `selectedSkpdId` untuk role `SKPD` di `mount()`, hook `updatedSelectedSkpdId()`, `getLaporanData()`, dan `downloadExcel()`.
5. `resources/views/filament/pages/laporan-realisasi.blade.php`
   - Tampilan toolbar filter: Elemen SKPD di-*disabled* jika role `SKPD`, dan dropdown `<select wire:model.live="selectedSkpdId">` aktif jika selain role `SKPD`.

---

## 4. Langkah-Langkah Eksekusi Implementasi (Implementation Steps)

### Langkah 1: Eager Load Relasi `skpd` di `RenjaSkpdResource.php`
Buka file `app/Filament/Resources/RenjaSkpds/RenjaSkpdResource.php`.
Tambahkan `'skpd'` pada array relasi yang dimuat di `getEloquentQuery()`:

```php
// File: app/Filament/Resources/RenjaSkpds/RenjaSkpdResource.php

#[Override]
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery()->with([
        'skpd', // Mencegah N+1 query problem saat nama SKPD dirender di tabel
        'subKegiatan.kegiatan.program.bidang.urusan'
    ]);

    if (Auth::user()?->role === 'SKPD') {
        $query->where('skpd_id', Auth::user()->skpd_id);
    }

    return $query;
}
```

---

### Langkah 2: Tambahkan Filter SKPD & Sesuaikan Kolom di `RenjaSkpdsTable.php`
Buka file `app/Filament/Resources/RenjaSkpds/Tables/RenjaSkpdsTable.php`.

1. **Kolom SKPD**: Ubah `isToggledHiddenByDefault: true` menjadi `isToggledHiddenByDefault: false` agar verifikator langsung melihat nama instansi.
2. **Filter SKPD**: Tambahkan `SelectFilter::make('skpd_id')` dengan relasi `skpd`, `searchable()`, `preload()`, dan `visible(fn() => Auth::user()?->role !== 'SKPD')`.
3. **Filter Layout**: Pasang `layout: FiltersLayout::AboveContent` dan `filtersFormColumns(2)` agar filter tampil rapi dan langsung terlihat di atas tabel.

```php
// Cuplikan pada app/Filament/Resources/RenjaSkpds/Tables/RenjaSkpdsTable.php

// Kolom:
TextColumn::make('skpd.nama_skpd')
    ->label('SKPD')
    ->searchable()
    ->sortable()
    ->wrap()
    ->toggleable(isToggledHiddenByDefault: false)
    ->hidden(fn() => Auth::user()?->role === 'SKPD'),

// Filters:
->filters([
    SelectFilter::make('skpd_id')
        ->label('Pilih SKPD')
        ->relationship('skpd', 'nama_skpd')
        ->searchable()
        ->preload()
        ->placeholder('Semua SKPD')
        ->visible(fn() => Auth::user()?->role !== 'SKPD'),

    SelectFilter::make('tahun')
        ->label('Tahun Anggaran')
        ->options([
            '2024' => '2024',
            '2025' => '2025',
            '2026' => '2026',
            '2027' => '2027',
            '2028' => '2028',
            '2029' => '2029',
            '2030' => '2030'
        ])
        ->default(date('Y')),
], layout: FiltersLayout::AboveContent)
->filtersFormColumns(2)
```

---

### Langkah 3: Sinkronisasi Validasi Role Verifikator di `RealisasiKegiatanRelationManager.php`
Buka file `app/Filament/Resources/RenjaSkpds/RelationManagers/RealisasiKegiatanRelationManager.php`.
Ganti pengecekan string role lama yang mengecek `'VERIFIKATOR'` menjadi `Auth::user()?->role !== 'SKPD'` agar verifikator **BAPPEDA** dan **BAKEUDA** memiliki hak verifikasi dan pengisian solusi:

1. **Field Solusi (Form)**:
```php
Textarea::make('solusi')
    ->label('Solusi')
    ->disabled(fn(): bool => Auth::user()?->role === 'SKPD')
    ->columnSpanFull(),
```

2. **Aksi Verifikasi (Table Record Action)**:
```php
Action::make('verifikasi')
    ->label('Verif')
    ->icon('heroicon-o-check-circle')
    ->color('success')
    ->requiresConfirmation()
    ->visible(fn(RealisasiKegiatan $record): bool => Auth::user()?->role !== 'SKPD' && !$record->is_verified)
    ->action(function (RealisasiKegiatan $record): void {
        $record->update(['is_verified' => true]);
        Notification::make()
            ->title('Berhasil diverifikasi')
            ->success()
            ->send();
    }),
```

---

### Langkah 4: Penguncian Filter SKPD (Disabled) pada Halaman Laporan Realisasi Fisik & Keuangan

Ini adalah implementasi khusus pada modul **Laporan Realisasi** (`App\Filament\Pages\LaporanRealisasi`):

#### 4.1. Server-Side Protection di `app/Filament/Pages/LaporanRealisasi.php`
Pastikan backend selalu memaksakan ID SKPD milik user yang sedang login jika perannya adalah `SKPD`:

1. **Pada method `mount()`**:
```php
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

    // KUNCI SKPD: Jika role SKPD, paksa selalu menggunakan skpd_id miliknya
    if (Auth::user()?->role === 'SKPD') {
        $this->selectedSkpdId = Auth::user()->skpd_id;
    } else {
        $this->selectedSkpdId = (int) request()->query(
            'skpd_id',
            Auth::user()?->skpd_id ?? Skpd::query()->orderBy('nama_skpd')->value('id')
        );
    }
}
```

2. **Tambahkan Hook Livewire Lifecycle `updatedSelectedSkpdId()`**:
Mencegah injeksi manipulasi properti Livewire dari klien:
```php
/**
 * Hook perlindungan: Jika role SKPD mencoba mengubah selectedSkpdId,
 * otomatis kembalikan ke skpd_id miliknya sendiri.
 */
public function updatedSelectedSkpdId($value): void
{
    if (Auth::user()?->role === 'SKPD') {
        $this->selectedSkpdId = Auth::user()->skpd_id;
    }
}
```

3. **Pastikan `getLaporanData()` dan `downloadExcel()` Terproteksi**:
```php
public function getLaporanData(): array
{
    if (Auth::user()?->role === 'SKPD') {
        $this->selectedSkpdId = Auth::user()->skpd_id;
    }

    if (!$this->selectedSkpdId) {
        return [];
    }

    // Ambil data Renja SKPD sesuai $this->selectedSkpdId ...
}
```

---

#### 4.2. Tampilan UI Toolbar di `resources/views/filament/pages/laporan-realisasi.blade.php`
Pada bagian toolbar filter, sediakan elemen pilihan SKPD dengan kondisi kondisional:

- **Jika Role adalah `SKPD`**: Tampilkan elemen pilihan dengan atribut **`disabled`**, berlatar abu-abu terang (*read-only*), penunjuk kursor *not-allowed*, dan bertuliskan nama SKPD miliknya.
- **Jika Role Selain `SKPD` (`BAPPEDA`, `BAKEUDA`, `ADMIN`)**: Tampilkan tag `<select wire:model.live="selectedSkpdId">` aktif yang memuat seluruh daftar SKPD.

```blade
{{-- File: resources/views/filament/pages/laporan-realisasi.blade.php --}}

{{-- Filter SKPD --}}
<div class="lr-field-skpd">
    <label class="lr-label">Perangkat Daerah (SKPD)</label>
    
    @if(auth()->user()?->role === 'SKPD')
        {{-- Pilihan SKPD TERKUNCI (DISABLED) untuk Admin SKPD --}}
        <select class="lr-select" disabled style="background-color: #f1f5f9 !important; color: #334155 !important; cursor: not-allowed; border-color: #cbd5e1 !important;" title="Role Admin SKPD hanya dapat melihat laporan instansi sendiri">
            <option selected>{{ $activeSkpdName }}</option>
        </select>
        <small style="font-size: 11px; color: #64748b; margin-top: 3px;">
            * Terkunci: Hanya dapat melihat data SKPD sendiri
        </small>
    @else
        {{-- Dropdown Pilihan SKPD AKTIF untuk Verifikator BAPPEDA / BAKEUDA / ADMIN --}}
        <select wire:model.live="selectedSkpdId" class="lr-select">
            @foreach($this->skpdList as $id => $nama)
                <option value="{{ $id }}">{{ $nama }}</option>
            @endforeach
        </select>
        <small style="font-size: 11px; color: #2563eb; margin-top: 3px;">
            * Mode Verifikator: Pilih SKPD untuk meninjau laporan
        </small>
    @endif
</div>
```

---

## 5. Fitur Pendukung & Rekomendasi Lanjutan (Nilai Tambah)

### 5.1. Filter Realisasi Belum Diverifikasi pada Tabel Renja SKPD
Verifikator dapat memanfaatkan filter cepat untuk hanya menampilkan kegiatan Renja yang memiliki realisasi triwulan berstatus `is_verified = false`:
```php
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

Filter::make('belum_diverifikasi')
    ->label('Ada Realisasi Belum Diverifikasi')
    ->query(fn (Builder $query): Builder => $query->whereHas('realisasi_kegiatans', function (Builder $q) {
        $q->where('is_verified', false);
    }))
    ->visible(fn() => Auth::user()?->role !== 'SKPD'),
```

### 5.2. Sinkronisasi Tautan "Lihat Laporan" dengan SKPD yang Sedang Difilter
Pada `app/Filament/Resources/RenjaSkpds/Pages/ListRenjaSkpds.php`, tautan header action `bukaLaporan` dapat meneruskan `skpd_id` aktif jika pengguna sedang menyaring instansi tertentu, sehingga ketika verifikator membuka Laporan Realisasi, halaman langsung menampilkan data SKPD yang sama.

---

## 6. Edge Cases & Penanganan Keamanan Multi-Tenant

1. **Pencegahan Manipulasi Parameter Query URL (`?skpd_id=...`)**:
   - Jika pengguna role `SKPD` mencoba mengubah URL menjadi `/admin/laporan-realisasi?skpd_id=99` (milik SKPD lain), method `mount()` dan `getLaporanData()` akan menolak parameter tersebut dan menimpanya kembali dengan `Auth::user()->skpd_id`.
2. **Pencegahan Livewire Tampering Payload**:
   - Jika pengguna memodifikasi permintaan jaringan WebSocket/XHR Livewire untuk mengubah properti `$selectedSkpdId`, hook `updatedSelectedSkpdId()` dan pengecekan di `getLaporanData()` akan secara tegas membatalkan perubahan tersebut.
3. **Pencegahan Unduh Excel Data Instansi Lain**:
   - Pada method `downloadExcel()`, data diambil melalui `$this->getLaporanData()` yang sudah memiliki proteksi role `SKPD`, sehingga file Excel yang diunduh dipastikan hanya memuat data milik instansinya sendiri.
4. **Performa Pencarian SKPD pada Verifikator**:
   - Daftar puluhan dinas/badan di-render secara optimal dengan HTML native select dan Livewire reactive binding tanpa jeda latensi tinggi.

---

## 7. Panduan Pengujian & Checklist Verifikasi (Testing & QA)

| No | Modul | Skenario Pengujian | Akun Pengguna / Role | Ekspektasi Hasil | Status |
|:---:|---|---|---|---|:---:|
| 1 | **Renja SKPD** | Akses tabel Renja SKPD | Role `SKPD` | - Filter SKPD **TIDAK muncul**.<br>- Kolom SKPD di tabel **TIDAK muncul**.<br>- Hanya data milik SKPD login yang tampil. | [ ] |
| 2 | **Renja SKPD** | Akses tabel Renja SKPD | Role `BAPPEDA` / `BAKEUDA` / `ADMIN` | - Filter SKPD **MUNCUL** di atas tabel.<br>- Kolom SKPD **MUNCUL** di tabel secara default.<br>- Verifikator dapat memfilter data per SKPD. | [ ] |
| 3 | **Laporan Realisasi** | Akses Halaman Laporan Realisasi | Role `SKPD` | - Pilihan SKPD berstatus **DISABLED**.<br>- Menampilkan nama SKPD miliknya.<br>- Tidak bisa memilih atau mengganti SKPD lain.<br>- Tabel hanya memuat data miliknya. | [ ] |
| 4 | **Laporan Realisasi** | Akses Halaman Laporan Realisasi | Role `BAPPEDA` / `BAKEUDA` / `ADMIN` | - Dropdown SKPD **ENABLED** (aktif).<br>- Dapat memilih SKPD mana saja.<br>- Data tabel berganti secara reaktif sesuai SKPD terpilih. | [ ] |
| 5 | **Laporan Realisasi** | Percobaan manipulasi URL (`?skpd_id=X`) | Role `SKPD` | - Sistem menolak ID lain dan tetap menyajikan data SKPD login. | [ ] |
| 6 | **Laporan Realisasi** | Tombol Download Excel | Role `SKPD` vs Non-SKPD | - Role SKPD mengunduh Excel khusus miliknya.<br>- Verifikator mengunduh Excel sesuai SKPD yang sedang dipilih di filter. | [ ] |
| 7 | **Verifikasi Triwulan** | Aksi Verifikasi & Solusi | Role `BAPPEDA` / `BAKEUDA` | - Tombol **Verif** berfungsi lancar mengubah status.<br>- Textarea **Solusi** dapat diisi. | [ ] |

---

## 8. Ringkasan File & Komponen Terkait

```
app/
├── Filament/
│   ├── Pages/
│   │   └── LaporanRealisasi.php
│   │       ├── mount(): Kunci selectedSkpdId untuk role SKPD
│   │       ├── updatedSelectedSkpdId(): Proteksi Livewire hook
│   │       ├── getLaporanData(): Fallback penguncian ke skpd_id login
│   │       └── downloadExcel(): Menjamin keamanan data saat unduh spreadsheet
│   └── Resources/
│       └── RenjaSkpds/
│           ├── RenjaSkpdResource.php
│           │   └── getEloquentQuery(): Eager loading relasi 'skpd'
│           ├── Tables/
│           │   └── RenjaSkpdsTable.php
│           │       ├── Kolom skpd.nama_skpd: isToggledHiddenByDefault false
│           │       └── SelectFilter skpd_id: Visible khusus non-SKPD
│           └── RelationManagers/
│               └── RealisasiKegiatanRelationManager.php
│                   └── Tombol verifikasi & field solusi: Izin untuk role non-SKPD
resources/
└── views/
    └── filament/
        └── pages/
            └── laporan-realisasi.blade.php
                └── Toolbar Filter SKPD: Disabled untuk role SKPD, select aktif untuk non-SKPD
```
