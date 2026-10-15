# Plan 5: Master Indikator Penyelenggaraan Pemda

## Konteks & Tujuan

Membuat modul **Indikator Penyelenggaraan Pemda** di admin panel (Filament) yang memiliki struktur data **parent–child**:
- **Parent (indikator master):** tidak memiliki satuan — ini adalah judul/kelompok indikator
- **Child (indikator anak):** memiliki satuan — ini adalah indikator yang bisa diukur

Data akan disimpan di tabel `master_indikators` yang sudah ada, dengan kolom `parent_id` untuk menghubungkan parent–child. Indikator ini diidentifikasi dengan kategori khusus bernama **"Penyelenggaraan"**.

Sumber data seeder: `database/seeders/data/indikator_penyelenggaraan_pemda.json`

---

## Analisis Struktur yang Ada

### Tabel yang akan dipakai (sudah ada, tidak perlu migrasi baru):
- `master_indikators` — kolom: `id`, `parent_id`, `kategori_id`, `nama_indikator`, `satuan_id`, `kondisi_awal`, `is_measurable`, `skpd_id`, `program_id`
- `kategori_indikators` — kolom: `id`, `nama_kategori`
- `satuan_indikators` — kolom: `id`, `nama_satuan`

### Resource yang sudah ada (tidak perlu dibuat ulang):
- `app/Filament/Resources/MasterIndikators/MasterIndikatorResource.php`
- `app/Filament/Resources/MasterIndikators/Schemas/MasterIndikatorForm.php`
- `app/Filament/Resources/MasterIndikators/Tables/MasterIndikatorsTable.php`
- `app/Filament/Resources/MasterIndikators/Pages/ListMasterIndikators.php`
- `app/Filament/Resources/MasterIndikators/Pages/CreateMasterIndikator.php`
- `app/Filament/Resources/MasterIndikators/Pages/EditMasterIndikator.php`

### Struktur JSON seeder:
```json
[
  {
    "indikator_master": "Aspek Geografi dan Demografi",   // <- ini parent (tanpa satuan)
    "indikator_anak": [
      { "indikator": "Indeks Ketahanan Pangan", "satuan": "Indeks" }, // <- ini child
      { "indikator": "Prevalensi Ketidakcukupan Konsumsi Pangan", "satuan": "%" }
    ]
  }
]
```

---

## Langkah-langkah Implementasi

### Langkah 1: Buat Seeder `IndikatorPenyelenggaraanSeeder`

Buat file: `database/seeders/IndikatorPenyelenggaraanSeeder.php`

**Logika seeder:**
1. Baca file JSON dari `database/seeders/data/indikator_penyelenggaraan_pemda.json`
2. **Upsert KategoriIndikator** dengan nama `"Penyelenggaraan"` — ambil/buat dan catat `$kategoriId`
3. Loop setiap item di array JSON:
   a. **Upsert parent** ke tabel `master_indikators`:
      - `parent_id` = `null`
      - `kategori_id` = `$kategoriId`
      - `nama_indikator` = `indikator_master`
      - `satuan_id` = `null`
      - `is_measurable` = `false`
      - Gunakan `upsert()` dengan unique key `['nama_indikator', 'kategori_id']`
   b. Loop setiap child di `indikator_anak`:
      - **Upsert satuan** ke `satuan_indikators` (jika satuan tidak null) — ambil `$satuanId`
      - **Upsert child** ke `master_indikators`:
        - `parent_id` = ID parent yang baru di-upsert
        - `kategori_id` = `$kategoriId`
        - `nama_indikator` = `indikator`
        - `satuan_id` = `$satuanId`
        - `is_measurable` = `true`
        - Gunakan `upsert()` dengan unique key `['nama_indikator', 'parent_id']`
4. Tampilkan pesan info/sukses di console

**Catatan penting untuk upsert:**
- Karena `upsert()` Laravel tidak mengembalikan ID per row, gunakan strategi:
  - Untuk parent: setelah upsert, lakukan `firstWhere('nama_indikator', $namaParent)` untuk mendapatkan ID-nya
  - Alternatif yang lebih aman: gunakan `updateOrCreate()` per item agar bisa mendapatkan ID langsung

> Gunakan `updateOrCreate()` (bukan `upsert()` bulk) agar bisa mendapatkan ID parent untuk dipakai sebagai `parent_id` anak. Ini tetap idempotent (bisa dijalankan berulang kali).

**Kode Seeder:**

```php
<?php

namespace Database\Seeders;

use App\Models\KategoriIndikator;
use App\Models\MasterIndikator;
use App\Models\SatuanIndikator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndikatorPenyelenggaraanSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/indikator_penyelenggaraan_pemda.json');

        if (!file_exists($file)) {
            $this->command->error("File tidak ditemukan: {$file}");
            return;
        }

        $data = json_decode(file_get_contents($file), true);

        if (!$data) {
            $this->command->error("Gagal membaca JSON.");
            return;
        }

        $this->command->info("Memulai seeder Indikator Penyelenggaraan Pemda...");

        // 1. Upsert kategori "Penyelenggaraan"
        $kategori = KategoriIndikator::updateOrCreate(
            ['nama_kategori' => 'Penyelenggaraan'],
            []
        );

        $totalParent = 0;
        $totalChild  = 0;

        DB::transaction(function () use ($data, $kategori, &$totalParent, &$totalChild) {
            foreach ($data as $item) {
                $namaParent = trim($item['indikator_master']);

                // 2. Upsert parent (tanpa satuan, is_measurable = false)
                $parent = MasterIndikator::updateOrCreate(
                    [
                        'nama_indikator' => $namaParent,
                        'kategori_id'    => $kategori->id,
                        'parent_id'      => null,
                    ],
                    [
                        'satuan_id'     => null,
                        'is_measurable' => false,
                        'skpd_id'       => null,
                    ]
                );

                $totalParent++;

                // 3. Upsert setiap child
                foreach ($item['indikator_anak'] as $anak) {
                    $namaAnak = trim($anak['indikator']);
                    $namaSatuan = isset($anak['satuan']) ? trim($anak['satuan']) : null;

                    // Upsert satuan jika ada
                    $satuanId = null;
                    if ($namaSatuan) {
                        $satuan = SatuanIndikator::updateOrCreate(
                            ['nama_satuan' => $namaSatuan],
                            []
                        );
                        $satuanId = $satuan->id;
                    }

                    // Upsert child
                    MasterIndikator::updateOrCreate(
                        [
                            'nama_indikator' => $namaAnak,
                            'parent_id'      => $parent->id,
                            'kategori_id'    => $kategori->id,
                        ],
                        [
                            'satuan_id'     => $satuanId,
                            'is_measurable' => true,
                            'skpd_id'       => null,
                        ]
                    );

                    $totalChild++;
                }
            }
        });

        $this->command->info("Selesai! {$totalParent} parent dan {$totalChild} child berhasil di-seed.");
    }
}
```

---

### Langkah 2: Daftarkan Seeder ke `DatabaseSeeder`

Buka file `database/seeders/DatabaseSeeder.php` dan tambahkan `IndikatorPenyelenggaraanSeeder::class` ke dalam array `$this->call(...)`.

```php
$this->call([
    // ... seeder yang sudah ada ...
    IndikatorPenyelenggaraanSeeder::class,
]);
```

---

### Langkah 3: Buat Resource Filament Baru — `IndikatorPenyelenggaraanResource`

Resource ini khusus menampilkan indikator dengan `kategori.nama_kategori = 'Penyelenggaraan'`. Resource ini **terpisah** dari `MasterIndikatorResource` yang sudah ada.

#### 3.1 Buat Resource File

Buat file: `app/Filament/Resources/IndikatorPenyelenggaraans/IndikatorPenyelenggaraanResource.php`

```php
<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans;

use App\Filament\Resources\IndikatorPenyelenggaraans\Pages\CreateIndikatorPenyelenggaraan;
use App\Filament\Resources\IndikatorPenyelenggaraans\Pages\EditIndikatorPenyelenggaraan;
use App\Filament\Resources\IndikatorPenyelenggaraans\Pages\ListIndikatorPenyelenggaraans;
use App\Filament\Resources\IndikatorPenyelenggaraans\Schemas\IndikatorPenyelenggaraanForm;
use App\Filament\Resources\IndikatorPenyelenggaraans\Tables\IndikatorPenyelenggaraansTable;
use App\Models\KategoriIndikator;
use App\Models\MasterIndikator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class IndikatorPenyelenggaraanResource extends Resource
{
    protected static ?string $model = MasterIndikator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;
    protected static string|UnitEnum|null $navigationGroup = 'Master Indikator';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Indikator Penyelenggaraan';
    protected static ?string $slug = 'indikator-penyelenggaraan';

    /**
     * Scope query hanya untuk indikator dengan kategori 'Penyelenggaraan'
     */
    public static function getEloquentQuery(): Builder
    {
        $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');

        return parent::getEloquentQuery()
            ->where('kategori_id', $kategoriId)
            ->whereNull('parent_id'); // Tampilkan hanya parent di list utama
    }

    public static function form(Schema $schema): Schema
    {
        return IndikatorPenyelenggaraanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndikatorPenyelenggaraansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListIndikatorPenyelenggaraans::route('/'),
            'create' => CreateIndikatorPenyelenggaraan::route('/create'),
            'edit'   => EditIndikatorPenyelenggaraan::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return in_array($user->role, ['ADMIN', 'BAPPEDA']);
    }
}
```

#### 3.2 Buat Schema Form

Buat file: `app/Filament/Resources/IndikatorPenyelenggaraans/Schemas/IndikatorPenyelenggaraanForm.php`

Form ini khusus untuk menambah/edit indikator penyelenggaraan. Bedanya dari `MasterIndikatorForm`:
- `parent_id` hanya bisa memilih dari indikator yang ber-kategori "Penyelenggaraan"
- `kategori_id` di-set otomatis ke "Penyelenggaraan" (hidden)
- Field `skpd_id` tetap ada (opsional)
- Field `satuan_id` hanya muncul jika ada parent (artinya ini child)

```php
<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\Schemas;

use App\Models\KategoriIndikator;
use App\Models\MasterIndikator;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IndikatorPenyelenggaraanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Indikator Penyelenggaraan')
                    ->schema([
                        // Parent selector — hanya indikator penyelenggaraan tanpa parent (root)
                        Select::make('parent_id')
                            ->label('Indikator Induk (Aspek)')
                            ->options(function () {
                                $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');
                                return MasterIndikator::where('kategori_id', $kategoriId)
                                    ->whereNull('parent_id')
                                    ->pluck('nama_indikator', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Kosongkan jika ini adalah Aspek (level atas). Isi jika ini adalah Indikator turunan.'),

                        Textarea::make('nama_indikator')
                            ->label('Nama Indikator / Aspek')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(1),

                Section::make('Satuan & Pengukuran')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('satuan_id')
                                ->label('Satuan')
                                ->relationship('satuan', 'nama_satuan')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText('Kosongkan jika ini adalah Aspek (level atas/parent)'),

                            Toggle::make('is_measurable')
                                ->label('Dapat Diukur')
                                ->default(true)
                                ->inline(false)
                                ->helperText('Aktifkan jika indikator ini memiliki target & realisasi'),
                        ])
                    ]),
            ]);
    }
}
```

#### 3.3 Buat Table

Buat file: `app/Filament/Resources/IndikatorPenyelenggaraans/Tables/IndikatorPenyelenggaraansTable.php`

Table ini menampilkan daftar indikator penyelenggaraan (parent saja) beserta jumlah child-nya. Dari sini user bisa expand atau masuk ke edit.

```php
<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\Tables;

use App\Models\MasterIndikator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IndikatorPenyelenggaraansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_indikator')
                    ->label('Aspek / Indikator')
                    ->wrap()
                    ->searchable()
                    ->description(fn(MasterIndikator $record): string =>
                        $record->parent_id === null
                            ? 'Aspek (Level Induk)'
                            : 'Indikator Turunan'
                    ),

                TextColumn::make('satuan.nama_satuan')
                    ->label('Satuan')
                    ->placeholder('-')
                    ->searchable(),

                IconColumn::make('is_measurable')
                    ->label('Dapat Diukur')
                    ->boolean()
                    ->alignCenter(),
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

#### 3.4 Buat Pages

**`ListIndikatorPenyelenggaraans.php`**

Buat file: `app/Filament/Resources/IndikatorPenyelenggaraans/Pages/ListIndikatorPenyelenggaraans.php`

```php
<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\Pages;

use App\Filament\Resources\IndikatorPenyelenggaraans\IndikatorPenyelenggaraanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIndikatorPenyelenggaraans extends ListRecords
{
    protected static string $resource = IndikatorPenyelenggaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
```

**`CreateIndikatorPenyelenggaraan.php`**

Buat file: `app/Filament/Resources/IndikatorPenyelenggaraans/Pages/CreateIndikatorPenyelenggaraan.php`

```php
<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\Pages;

use App\Filament\Resources\IndikatorPenyelenggaraans\IndikatorPenyelenggaraanResource;
use App\Models\KategoriIndikator;
use Filament\Resources\Pages\CreateRecord;

class CreateIndikatorPenyelenggaraan extends CreateRecord
{
    protected static string $resource = IndikatorPenyelenggaraanResource::class;

    /**
     * Otomatis set kategori_id ke "Penyelenggaraan" saat menyimpan
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');
        $data['kategori_id'] = $kategoriId;

        // Jika parent kosong (level atas), is_measurable = false dan satuan = null
        if (empty($data['parent_id'])) {
            $data['is_measurable'] = false;
            $data['satuan_id']     = null;
        }

        return $data;
    }
}
```

**`EditIndikatorPenyelenggaraan.php`**

Buat file: `app/Filament/Resources/IndikatorPenyelenggaraans/Pages/EditIndikatorPenyelenggaraan.php`

```php
<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\Pages;

use App\Filament\Resources\IndikatorPenyelenggaraans\IndikatorPenyelenggaraanResource;
use App\Models\KategoriIndikator;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIndikatorPenyelenggaraan extends EditRecord
{
    protected static string $resource = IndikatorPenyelenggaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');
        $data['kategori_id'] = $kategoriId;

        if (empty($data['parent_id'])) {
            $data['is_measurable'] = false;
            $data['satuan_id']     = null;
        }

        return $data;
    }
}
```

---

### Langkah 4: Tambah RelationManager untuk Child Indikator

Agar dari halaman edit Aspek (parent) bisa langsung mengelola indikator turunannya, buat RelationManager.

Buat file: `app/Filament/Resources/IndikatorPenyelenggaraans/RelationManager/ChildIndikatorRelationManager.php`

```php
<?php

namespace App\Filament\Resources\IndikatorPenyelenggaraans\RelationManager;

use App\Models\KategoriIndikator;
use App\Models\SatuanIndikator;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class ChildIndikatorRelationManager extends RelationManager
{
    protected static string $relationship = 'childIndikators';
    protected static ?string $title = 'Daftar Indikator Turunan';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('nama_indikator')
                ->label('Nama Indikator')
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            Select::make('satuan_id')
                ->label('Satuan')
                ->relationship('satuan', 'nama_satuan')
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('Satuan pengukuran indikator'),

            Toggle::make('is_measurable')
                ->label('Dapat Diukur')
                ->default(true)
                ->inline(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_indikator')
                    ->label('Indikator')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('satuan.nama_satuan')
                    ->label('Satuan')
                    ->placeholder('-'),

                IconColumn::make('is_measurable')
                    ->label('Dapat Diukur')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');
                        $data['kategori_id'] = $kategoriId;
                        return $data;
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

#### 4.1 Tambahkan relasi `childIndikators` ke Model `MasterIndikator`

Buka file `app/Models/MasterIndikator.php` dan tambahkan relasi berikut (jika belum ada):

```php
public function childIndikators(): HasMany
{
    return $this->hasMany(MasterIndikator::class, 'parent_id');
}
```

> **Catatan:** Saat ini di model ada relasi `children()` yang salah — menggunakan `belongsTo` bukan `hasMany`. Perbaiki juga relasi ini:
> - `children()` → ganti ke `HasMany` (child dari parent)  
> - `parent()` → buat relasi baru `BelongsTo` untuk akses parent dari child

#### 4.2 Daftarkan RelationManager di Resource

Tambahkan `ChildIndikatorRelationManager::class` ke `getRelations()` di `IndikatorPenyelenggaraanResource.php`.

---

## Ringkasan File yang Dibuat/Diubah

### File Baru:
| File | Keterangan |
|------|-----------|
| `database/seeders/IndikatorPenyelenggaraanSeeder.php` | Seeder dengan `updateOrCreate` (idempotent) |
| `app/Filament/Resources/IndikatorPenyelenggaraans/IndikatorPenyelenggaraanResource.php` | Resource utama |
| `app/Filament/Resources/IndikatorPenyelenggaraans/Schemas/IndikatorPenyelenggaraanForm.php` | Form tambah/edit |
| `app/Filament/Resources/IndikatorPenyelenggaraans/Tables/IndikatorPenyelenggaraansTable.php` | Konfigurasi tabel |
| `app/Filament/Resources/IndikatorPenyelenggaraans/Pages/ListIndikatorPenyelenggaraans.php` | Halaman daftar |
| `app/Filament/Resources/IndikatorPenyelenggaraans/Pages/CreateIndikatorPenyelenggaraan.php` | Halaman tambah |
| `app/Filament/Resources/IndikatorPenyelenggaraans/Pages/EditIndikatorPenyelenggaraan.php` | Halaman edit |
| `app/Filament/Resources/IndikatorPenyelenggaraans/RelationManager/ChildIndikatorRelationManager.php` | Kelola child dari halaman edit parent |

### File yang Diubah:
| File | Perubahan |
|------|-----------|
| `database/seeders/DatabaseSeeder.php` | Tambahkan `IndikatorPenyelenggaraanSeeder::class` |
| `app/Models/MasterIndikator.php` | Tambah relasi `childIndikators()` (HasMany), perbaiki relasi `children()` |

---

## Cara Menjalankan Seeder

```bash
# Jalankan hanya seeder ini:
php artisan db:seed --class=IndikatorPenyelenggaraanSeeder

# Atau jalankan semua seeder (aman diulang karena menggunakan updateOrCreate):
php artisan db:seed
```

---

## Catatan Teknis

- **Idempotent:** Seeder menggunakan `updateOrCreate()` — aman dijalankan berkali-kali tanpa duplikasi data.
- **Kategori otomatis:** Saat membuat/edit dari admin panel, `kategori_id` otomatis di-set ke "Penyelenggaraan" melalui `mutateFormDataBeforeCreate` dan `mutateFormDataBeforeSave`.
- **Parent tidak punya satuan:** Logika di `CreateRecord` dan `EditRecord` secara otomatis menghapus `satuan_id` dan men-set `is_measurable = false` jika `parent_id` kosong.
- **Tidak ada migrasi baru:** Semua data disimpan di `master_indikators` yang sudah ada, dibedakan via `kategori_id`.



---

---

# Plan 5B: Realisasi Indikator Penyelenggaraan

## Konteks & Tujuan

Membuat modul **Realisasi Indikator Penyelenggaraan** yang memungkinkan:
- **SKPD (role: SKPD):** Login → lihat daftar indikator yang menjadi tanggung jawabnya → isi nilai realisasi & upload bukti dokumen
- **Admin / BAPPEDA (role: ADMIN / BAPPEDA):** Bisa melihat semua, mengubah status (verifikasi), mengisi catatan evaluasi
- Data target sudah terisi terlebih dahulu dari seeder, sehingga SKPD langsung melihat target yang harus dicapai

---

## Analisis Struktur yang Ada

### Tabel & Model yang akan digunakan (sudah ada):
- `indikator_realisasis` — kolom: `id`, `indikator_id`, `skpd_id`, `tahun`, `nilai_realisasi`, `bukti_dokument`, `status`, `catatan_bappeda`, `submitted_at`, `verified_at`
- `indikator_targets` — kolom: `id`, `indikator_id`, `tahun`, `nilai_target`
- `master_indikators` — sumber data indikator penyelenggaraan (kategori "Penyelenggaraan")
- `skpds` — referensi SKPD

Tabel `indikator_realisasis` dan `indikator_targets` sudah ada dan dipakai untuk indikator umum. Kita menggunakan tabel yang sama — dibedakan berdasarkan kategori indikatornya ("Penyelenggaraan").

### Struktur JSON seeder (`realisasi_indikator_penyelenggaraan.json`):
```json
[
  {
    "skpd": "Dinas Pertanian, Ketahanan Pangan dan Perikanan",
    "indikator": [
      {
        "no": 1,
        "nama_indikator": "Indeks Ketahanan Pangan",
        "satuan": "Indeks",
        "baseline_2024": 87.06,
        "target_2025": 87.21,
        "target_2026": 87.36,
        "target_2027": 87.51,
        "target_2028": 87.66,
        "target_2029": 87.81,
        "target_2030": 87.81
      }
    ]
  }
]
```

**Statistik data JSON:** 38 SKPD, 95 indikator terukur (dengan satuan), 39 indikator non-terukur (tanpa satuan). Seeder hanya meng-insert indikator yang memiliki satuan.

**Catatan penting:** Seeder harus mencocokkan nama SKPD dari JSON ke kolom `nama_skpd` di tabel `skpds`, dan mencocokkan nama indikator dari JSON ke `nama_indikator` di tabel `master_indikators` (kategori "Penyelenggaraan"). Jika tidak ditemukan, lewati dan catat warning.

---

## Langkah-langkah Implementasi

### Langkah 1: Buat Seeder `RealisasiIndikatorPenyelenggaraanSeeder`

Buat file: `database/seeders/RealisasiIndikatorPenyelenggaraanSeeder.php`

**Logika seeder:**
1. Baca file JSON `database/seeders/data/realisasi_indikator_penyelenggaraan.json`
2. Ambil `kategoriId` untuk "Penyelenggaraan" dari `kategori_indikators`
3. Loop setiap item (per SKPD):
   - Skip item `"Seluruh Kecamatan"` (bukan SKPD tunggal)
   - Cari SKPD berdasarkan `nama_skpd` dengan case-insensitive match — jika tidak ditemukan, coba LIKE match, jika masih tidak ditemukan → warning + skip
   - Loop setiap `indikator` di item tersebut:
     - Lewati jika `satuan === null` (non-measurable)
     - Cari `master_indikator` berdasarkan `nama_indikator` + `kategori_id` + `parent_id IS NOT NULL`
     - Upsert ke `indikator_targets` untuk tahun 2025-2030
     - Upsert ke `indikator_realisasis` untuk tahun 2025-2030 (record kosong dengan nilai_realisasi = '-', status = 'DRAF')

**Kode Seeder:**

```php
<?php

namespace Database\Seeders;

use App\Models\IndikatorRealisasi;
use App\Models\IndikatorTarget;
use App\Models\KategoriIndikator;
use App\Models\MasterIndikator;
use App\Models\Skpd;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealisasiIndikatorPenyelenggaraanSeeder extends Seeder
{
    const TAHUN_RANGE = [2025, 2026, 2027, 2028, 2029, 2030];

    public function run(): void
    {
        $file = database_path('seeders/data/realisasi_indikator_penyelenggaraan.json');

        if (!file_exists($file)) {
            $this->command->error("File tidak ditemukan: {$file}");
            return;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!$data) {
            $this->command->error("Gagal membaca JSON.");
            return;
        }

        $this->command->info("Memulai seeder Realisasi Indikator Penyelenggaraan...");

        $kategori = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->first();
        if (!$kategori) {
            $this->command->error("Kategori 'Penyelenggaraan' tidak ditemukan. Jalankan IndikatorPenyelenggaraanSeeder terlebih dahulu.");
            return;
        }

        $totalTarget    = 0;
        $totalRealisasi = 0;
        $skipped        = 0;

        DB::transaction(function () use ($data, $kategori, &$totalTarget, &$totalRealisasi, &$skipped) {
            foreach ($data as $item) {
                $namaSkpd = trim($item['skpd'] ?? '');

                // Skip "Seluruh Kecamatan"
                if (strtolower($namaSkpd) === 'seluruh kecamatan') {
                    $this->command->warn("Skip 'Seluruh Kecamatan' (bukan SKPD tunggal).");
                    continue;
                }

                // Cari SKPD exact match dulu
                $skpd = Skpd::whereRaw('LOWER(TRIM(nama_skpd)) = ?', [strtolower($namaSkpd)])->first();

                // Fallback: LIKE match
                if (!$skpd) {
                    $skpd = Skpd::where('nama_skpd', 'LIKE', "%{$namaSkpd}%")->first();
                }

                if (!$skpd) {
                    $this->command->warn("SKPD tidak ditemukan: '{$namaSkpd}' — dilewati.");
                    $skipped++;
                    continue;
                }

                foreach ($item['indikator'] as $ind) {
                    // Lewati non-measurable
                    if (empty($ind['satuan'])) {
                        continue;
                    }

                    $namaIndikator = trim($ind['nama_indikator'] ?? '');
                    if ($namaIndikator === '') {
                        continue;
                    }

                    // Cari master_indikator
                    $masterIndikator = MasterIndikator::where('kategori_id', $kategori->id)
                        ->whereNotNull('parent_id')
                        ->whereRaw('LOWER(TRIM(nama_indikator)) = ?', [strtolower($namaIndikator)])
                        ->first();

                    if (!$masterIndikator) {
                        $this->command->warn("Indikator tidak ditemukan: '{$namaIndikator}' — dilewati.");
                        $skipped++;
                        continue;
                    }

                    foreach (self::TAHUN_RANGE as $tahun) {
                        $nilaiTarget = $ind["target_{$tahun}"] ?? null;

                        if ($nilaiTarget !== null) {
                            IndikatorTarget::updateOrCreate(
                                [
                                    'indikator_id' => $masterIndikator->id,
                                    'tahun'        => $tahun,
                                ],
                                [
                                    'nilai_target' => (string) $nilaiTarget,
                                ]
                            );
                            $totalTarget++;
                        }

                        IndikatorRealisasi::updateOrCreate(
                            [
                                'indikator_id' => $masterIndikator->id,
                                'skpd_id'      => $skpd->id,
                                'tahun'        => $tahun,
                            ],
                            [
                                'nilai_realisasi' => '-',
                                'status'          => 'DRAF',
                            ]
                        );
                        $totalRealisasi++;
                    }
                }
            }
        });

        $this->command->info("Selesai! {$totalTarget} target dan {$totalRealisasi} record realisasi berhasil di-seed.");
        if ($skipped > 0) {
            $this->command->warn("{$skipped} item dilewati (SKPD/Indikator tidak ditemukan).");
        }
    }
}
```

> Seeder ini memerlukan `IndikatorPenyelenggaraanSeeder` sudah dijalankan terlebih dahulu.

---

### Langkah 2: Daftarkan Seeder ke `DatabaseSeeder`

Tambahkan setelah `IndikatorPenyelenggaraanSeeder`:

```php
$this->call([
    IndikatorPenyelenggaraanSeeder::class,
    RealisasiIndikatorPenyelenggaraanSeeder::class,
]);
```

---

### Langkah 3: Tambah Unique Constraint di Migration (jika belum ada)

Cek apakah `indikator_realisasis` memiliki unique constraint `['indikator_id', 'skpd_id', 'tahun']`. Jika belum, buat migration baru:

```php
// database/migrations/XXXX_add_unique_to_indikator_realisasis_table.php
Schema::table('indikator_realisasis', function (Blueprint $table) {
    $table->unique(['indikator_id', 'skpd_id', 'tahun'], 'indikator_skpd_tahun_unique');
});
```

> Cek dulu dengan: `php artisan db:show --table=indikator_realisasis` apakah constraint sudah ada.

---

### Langkah 4: Buat Resource Filament — `RealisasiPenyelenggaraanResource`

Resource khusus untuk realisasi indikator penyelenggaraan, terpisah dari `IndikatorRealisasiResource` yang sudah ada.

#### 4.1 Resource Utama

Buat file: `app/Filament/Resources/RealisasiPenyelenggaraans/RealisasiPenyelenggaraanResource.php`

```php
<?php

namespace App\Filament\Resources\RealisasiPenyelenggaraans;

use App\Filament\Resources\RealisasiPenyelenggaraans\Pages\EditRealisasiPenyelenggaraan;
use App\Filament\Resources\RealisasiPenyelenggaraans\Pages\ListRealisasiPenyelenggaraans;
use App\Filament\Resources\RealisasiPenyelenggaraans\Schemas\RealisasiPenyelenggaraanForm;
use App\Filament\Resources\RealisasiPenyelenggaraans\Tables\RealisasiPenyelenggaraansTable;
use App\Models\IndikatorRealisasi;
use App\Models\KategoriIndikator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class RealisasiPenyelenggaraanResource extends Resource
{
    protected static ?string $model = IndikatorRealisasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentCheck;
    protected static string|UnitEnum|null $navigationGroup = 'Evaluasi Dan Pelaporan';
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Realisasi Penyelenggaraan';
    protected static ?string $modelLabel = 'Realisasi Penyelenggaraan';
    protected static ?string $pluralModelLabel = 'Realisasi Penyelenggaraan';
    protected static ?string $slug = 'realisasi-penyelenggaraan';

    public static function getEloquentQuery(): Builder
    {
        $user       = Auth::user();
        $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');

        return parent::getEloquentQuery()
            ->whereHas('indikator', function (Builder $q) use ($kategoriId) {
                $q->where('kategori_id', $kategoriId);
            })
            ->when(
                $user?->role === 'SKPD',
                fn ($q) => $q->where('skpd_id', $user->skpd_id)
            );
    }

    public static function form(Schema $schema): Schema
    {
        return RealisasiPenyelenggaraanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RealisasiPenyelenggaraansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRealisasiPenyelenggaraans::route('/'),
            'edit'  => EditRealisasiPenyelenggaraan::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return in_array($user?->role, ['ADMIN', 'BAPPEDA', 'SKPD']);
    }
}
```

#### 4.2 Form Schema

Buat file: `app/Filament/Resources/RealisasiPenyelenggaraans/Schemas/RealisasiPenyelenggaraanForm.php`

Aturan field per role:

| Field | SKPD | Admin/BAPPEDA |
|-------|------|---------------|
| Indikator, Tahun, Aspek, SKPD | disabled (readonly) | disabled |
| Target Tahunan | disabled | disabled |
| **Nilai Realisasi** | **editable** | editable |
| **Bukti Dokumen** | **editable** | editable |
| Status | disabled (hanya lihat) | **editable** |
| Catatan Bappeda | visible jika ada catatan (readonly) | **editable** |

```php
<?php

namespace App\Filament\Resources\RealisasiPenyelenggaraans\Schemas;

use App\Enums\StatusRealisasi;
use App\Models\IndikatorTarget;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class RealisasiPenyelenggaraanForm
{
    public static function configure(Schema $schema): Schema
    {
        $user   = Auth::user();
        $isSkpd = $user?->role === 'SKPD';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Indikator')
                    ->schema([
                        TextInput::make('aspek_display')
                            ->label('Aspek / Kelompok')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->indikator?->parent?->nama_indikator ?? '-')
                            ),

                        TextInput::make('indikator_display')
                            ->label('Nama Indikator')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->indikator?->nama_indikator ?? '-')
                            ),

                        TextInput::make('skpd_display')
                            ->label('SKPD Penanggung Jawab')
                            ->disabled()
                            ->dehydrated(false)
                            ->hidden($isSkpd)
                            ->afterStateHydrated(fn ($component, $record) =>
                                $component->state($record?->skpd?->nama_skpd ?? '-')
                            ),

                        TextInput::make('tahun')
                            ->label('Tahun')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('target_display')
                            ->label('Target Tahunan')
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix(fn ($record) => $record?->indikator?->satuan?->nama_satuan ?? '')
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $target = IndikatorTarget::where('indikator_id', $record->indikator_id)
                                        ->where('tahun', $record->tahun)
                                        ->value('nilai_target');
                                    $component->state($target ?? '(belum diset)');
                                }
                            }),
                    ])->columns(2),

                Section::make('Isian Realisasi')
                    ->schema([
                        TextInput::make('nilai_realisasi')
                            ->label('Nilai Realisasi')
                            ->required()
                            ->suffix(fn ($record) => $record?->indikator?->satuan?->nama_satuan ?? '')
                            ->helperText('Masukkan angka realisasi. Gunakan "-" jika belum ada data.'),

                        FileUpload::make('bukti_dokument')
                            ->label('Bukti / Dokumen Pendukung')
                            ->directory('bukti_penyelenggaraan')
                            ->disk('public')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'])
                            ->maxSize(5120)
                            ->helperText('Format: PDF, JPG, PNG. Maks 5MB.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Verifikasi & Status')
                    ->hidden($isSkpd)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(StatusRealisasi::class)
                            ->required()
                            ->default(StatusRealisasi::DRAF),

                        Textarea::make('catatan_bappeda')
                            ->label('Catatan Verifikator / Bappeda')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
```

#### 4.3 Table Configuration

Buat file: `app/Filament/Resources/RealisasiPenyelenggaraans/Tables/RealisasiPenyelenggaraansTable.php`

```php
<?php

namespace App\Filament\Resources\RealisasiPenyelenggaraans\Tables;

use App\Enums\StatusRealisasi;
use App\Models\IndikatorTarget;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

use function Symfony\Component\Clock\now;

class RealisasiPenyelenggaraansTable
{
    public static function configure(Table $table): Table
    {
        $user   = Auth::user();
        $isSkpd = $user?->role === 'SKPD';

        return $table
            ->columns([
                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('indikator.parent.nama_indikator')
                    ->label('Aspek')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                TextColumn::make('indikator.nama_indikator')
                    ->label('Indikator')
                    ->wrap()
                    ->searchable()
                    ->description(fn ($record) => $record->indikator?->satuan?->nama_satuan ?? ''),

                TextColumn::make('target_tahunan')
                    ->label('Target')
                    ->alignRight()
                    ->state(fn ($record) => IndikatorTarget::where('indikator_id', $record->indikator_id)
                        ->where('tahun', $record->tahun)
                        ->value('nilai_target') ?? '-'
                    ),

                TextColumn::make('nilai_realisasi')
                    ->label('Realisasi')
                    ->alignRight()
                    ->color(fn ($record) => $record->nilai_realisasi === '-' ? 'gray' : 'success'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (StatusRealisasi $state): string => match ($state) {
                        StatusRealisasi::DRAF      => 'gray',
                        StatusRealisasi::SUBMITTED => 'warning',
                        StatusRealisasi::APPROVED  => 'success',
                        StatusRealisasi::REJECTED  => 'danger',
                    }),

                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD')
                    ->wrap()
                    ->hidden($isSkpd)
                    ->toggleable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Tahun Anggaran')
                    ->options(array_combine(range(2025, 2030), range(2025, 2030)))
                    ->default(date('Y')),

                SelectFilter::make('status')
                    ->label('Status Laporan')
                    ->options(StatusRealisasi::class),

                SelectFilter::make('skpd_id')
                    ->label('SKPD')
                    ->relationship('skpd', 'nama_skpd')
                    ->searchable()
                    ->preload()
                    ->hidden($isSkpd),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->recordActions([
                EditAction::make()
                    ->label('Isi Realisasi')
                    ->visible(fn ($record) => $isSkpd
                        ? in_array($record->status, [StatusRealisasi::DRAF, StatusRealisasi::REJECTED])
                        : true
                    ),

                Action::make('submit')
                    ->label('Submit')
                    ->icon(Heroicon::PaperAirplane)
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $isSkpd
                        && $record->status === StatusRealisasi::DRAF
                        && $record->nilai_realisasi !== '-'
                    )
                    ->action(fn ($record) => $record->update([
                        'status'       => StatusRealisasi::SUBMITTED,
                        'submitted_at' => now(),
                    ])),

                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => !$isSkpd && $record->status === StatusRealisasi::SUBMITTED)
                    ->action(fn ($record) => $record->update([
                        'status'      => StatusRealisasi::APPROVED,
                        'verified_at' => now(),
                    ])),

                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->visible(fn ($record) => !$isSkpd && $record->status === StatusRealisasi::SUBMITTED)
                    ->form([
                        Textarea::make('catatan_bappeda')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'status'          => StatusRealisasi::REJECTED,
                            'catatan_bappeda' => $data['catatan_bappeda'],
                            'verified_at'     => now(),
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->hidden($isSkpd),
            ])
            ->defaultSort('tahun', 'asc');
    }
}
```

#### 4.4 Pages

**`ListRealisasiPenyelenggaraans.php`**

Buat file: `app/Filament/Resources/RealisasiPenyelenggaraans/Pages/ListRealisasiPenyelenggaraans.php`

```php
<?php

namespace App\Filament\Resources\RealisasiPenyelenggaraans\Pages;

use App\Enums\StatusRealisasi;
use App\Filament\Resources\RealisasiPenyelenggaraans\RealisasiPenyelenggaraanResource;
use App\Models\IndikatorRealisasi;
use App\Models\KategoriIndikator;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListRealisasiPenyelenggaraans extends ListRecords
{
    protected static string $resource = RealisasiPenyelenggaraanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
        // Tidak ada Create — record dibuat oleh seeder
    }

    public function getTabs(): array
    {
        $user       = Auth::user();
        $isSkpd     = $user?->role === 'SKPD';
        $kategoriId = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->value('id');

        $base = fn () => IndikatorRealisasi::whereHas('indikator', fn ($q) => $q->where('kategori_id', $kategoriId))
            ->when($isSkpd, fn ($q) => $q->where('skpd_id', $user->skpd_id));

        return [
            'semua' => Tab::make('Semua')->badge($base()->count()),

            'draf' => Tab::make('Belum Diisi')
                ->badge($base()->where('status', StatusRealisasi::DRAF)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', StatusRealisasi::DRAF)),

            'submitted' => Tab::make('Menunggu Verifikasi')
                ->badge($base()->where('status', StatusRealisasi::SUBMITTED)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', StatusRealisasi::SUBMITTED)),

            'approved' => Tab::make('Disetujui')
                ->badge($base()->where('status', StatusRealisasi::APPROVED)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', StatusRealisasi::APPROVED)),

            'rejected' => Tab::make('Ditolak')
                ->badge($base()->where('status', StatusRealisasi::REJECTED)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', StatusRealisasi::REJECTED)),
        ];
    }
}
```

**`EditRealisasiPenyelenggaraan.php`**

Buat file: `app/Filament/Resources/RealisasiPenyelenggaraans/Pages/EditRealisasiPenyelenggaraan.php`

```php
<?php

namespace App\Filament\Resources\RealisasiPenyelenggaraans\Pages;

use App\Filament\Resources\RealisasiPenyelenggaraans\RealisasiPenyelenggaraanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditRealisasiPenyelenggaraan extends EditRecord
{
    protected static string $resource = RealisasiPenyelenggaraanResource::class;

    protected function getHeaderActions(): array
    {
        $user   = Auth::user();
        $isSkpd = $user?->role === 'SKPD';

        return $isSkpd ? [] : [
            DeleteAction::make(),
        ];
    }

    /**
     * SKPD hanya bisa mengubah nilai_realisasi dan bukti_dokument.
     * Field lain dikunci ke nilai asli record.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user   = Auth::user();
        $isSkpd = $user?->role === 'SKPD';

        if ($isSkpd) {
            $record = $this->getRecord();
            $data['indikator_id']    = $record->indikator_id;
            $data['skpd_id']         = $record->skpd_id;
            $data['tahun']           = $record->tahun;
            $data['status']          = $record->status->value;
            $data['catatan_bappeda'] = $record->catatan_bappeda;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
```

---

## Ringkasan File yang Dibuat/Diubah (Plan 5B)

### File Baru:
| File | Keterangan |
|------|-----------|
| `database/seeders/RealisasiIndikatorPenyelenggaraanSeeder.php` | Seeder target + record realisasi kosong per SKPD |
| `app/Filament/Resources/RealisasiPenyelenggaraans/RealisasiPenyelenggaraanResource.php` | Resource utama |
| `app/Filament/Resources/RealisasiPenyelenggaraans/Schemas/RealisasiPenyelenggaraanForm.php` | Form edit |
| `app/Filament/Resources/RealisasiPenyelenggaraans/Tables/RealisasiPenyelenggaraansTable.php` | Tabel dengan aksi submit/approve/reject |
| `app/Filament/Resources/RealisasiPenyelenggaraans/Pages/ListRealisasiPenyelenggaraans.php` | List dengan tabs status |
| `app/Filament/Resources/RealisasiPenyelenggaraans/Pages/EditRealisasiPenyelenggaraan.php` | Edit dengan field lock per role |

### File yang Diubah:
| File | Perubahan |
|------|-----------|
| `database/seeders/DatabaseSeeder.php` | Tambah `RealisasiIndikatorPenyelenggaraanSeeder::class` |

---

## Cara Menjalankan Seeder (Plan 5B)

```bash
# Urutan penting: IndikatorPenyelenggaraanSeeder harus lebih dulu
php artisan db:seed --class=IndikatorPenyelenggaraanSeeder
php artisan db:seed --class=RealisasiIndikatorPenyelenggaraanSeeder

# Atau sekaligus:
php artisan db:seed
```

---

## Catatan Teknis (Plan 5B)

- **Tidak ada migrasi baru:** Pakai tabel `indikator_realisasis` dan `indikator_targets` yang sudah ada.
- **Scope per kategori:** `getEloquentQuery()` memfilter hanya record dengan indikator berkategori "Penyelenggaraan".
- **SKPD langsung mendapat data:** Seeder membuat record realisasi kosong per kombinasi `indikator_id + skpd_id + tahun`.
- **Lock field per role:** Di `mutateFormDataBeforeSave`, field kritis dikunci untuk role SKPD.
- **Submit flow:** SKPD isi realisasi → klik Submit → Admin/BAPPEDA Approve/Reject.
- **Idempotent:** `updateOrCreate` memastikan seeder aman dijalankan berulang kali.
- **Cek unique constraint:** Pastikan tabel `indikator_realisasis` memiliki unique key `(indikator_id, skpd_id, tahun)` agar `updateOrCreate` berfungsi dengan benar.

---

## Pembaruan & Penyesuaian Status Ketercapaian & Error Tabs

1. **Perbaikan Error Tabs (`Call to a member function newQueryWithoutRelationships() on null`):**
   - Masalah: Penamaan parameter pada closure `modifyQueryUsing` di `ListRealisasiPenyelenggaraans` menggunakan `$q` alih-alih `$query`. Filament menginjeksi dependency via reflection berdasarkan nama parameter `query`.
   - Solusi: Mengubah `fn (Builder $q) => ...` menjadi `fn (Builder $query) => ...`.

2. **Status Ketercapaian (`is_tercapai` - boolean true/false):**
   - Kolom `is_tercapai` ditambahkan ke `indikator_realisasis` (default `false`).
   - Tampilan di tabel & form: **Tercapai** (`true` / badge hijau) dan **Tidak Tercapai** (`false` / badge merah).
   - Hak akses: Hanya dapat diubah oleh **Admin dan Verifikator** (Bappeda). Untuk SKPD dinonaktifkan (read-only).
   - Aksi cepat di tabel: Action `Ubah Ketercapaian` untuk Admin/Verifikator untuk toggle antara *Tercapai* dan *Tidak Tercapai*.

3. **Status Verifikasi (`status` & `is_verif`):**
   - Role SKPD hanya sampai level **DRAF** dan **SUBMITTED** (melalui aksi Submit setelah mengisi realisasi).
   - Aksi **Approve** dan **Reject** hanya muncul dan dapat dieksekusi oleh **Admin dan Verifikator** (`role !== 'SKPD'`).
