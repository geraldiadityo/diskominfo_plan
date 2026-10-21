# Rencana Implementasi (Plan 4): Modul Program Unggulan di Admin Panel Filament

Dokumen ini merupakan **spesifikasi teknis dan panduan instruksi implementasi lengkap** bagi AI Agent / Software Engineer untuk membangun fitur **Program Unggulan** pada aplikasi berbasis **Laravel 12** dan **Filament v4**.

---

## 1. Ringkasan Fitur & Sasaran

### 1.1. Deskripsi Fitur

Membangun modul **Program Unggulan** yang terdiri dari **3 entitas utama** yang saling berelasi:

| # | Entitas | Deskripsi |
|---|---|---|
| 1 | **Program Unggulan** | Daftar nama program unggulan (misal: "Sungai Penuh Maju", "Sungai Penuh Sejahtera") |
| 2 | **Indikator Kinerja Program Unggulan** | Indikator kinerja tiap program, dilengkapi detail dan daftar urusan |
| 3 | **Realisasi Program Unggulan** | Capaian realisasi per indikator per tahun (5 tahun), target tahunan, bukti dukung, dan SKPD pelaksana |

Data awal bersumber dari: `database/seeders/data/data_progul.json`

### 1.2. Aturan Hak Akses

| Role | Program | Indikator | Realisasi |
|---|---|---|---|
| `ADMIN` | CRUD penuh | CRUD penuh | Lihat semua, bisa edit |
| `BAPPEDA` | Lihat saja | Lihat saja | Lihat semua, bisa verifikasi |
| `SKPD` | Tidak tampil | Tidak tampil | Hanya data milik SKPD sendiri |

---

## 2. Struktur Tabel Database & Migration

### 2.1. Gambaran Relasi

```
progul_programs (Program Unggulan)
    └── progul_indikators (Indikator Kinerja)
            ├── progul_realisasis (Realisasi per Tahun)
            │       ├── progul_bukti_dukung (Bukti Dukung)
            │       └── skpd_id → skpds
            └── progul_indikator_urusan (Pivot: Indikator ↔ Urusan)
```

### 2.2. Tabel `progul_programs`

**File:** `database/migrations/2026_10_20_000001_create_progul_programs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progul_programs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_program');        // "Sungai Penuh Maju", dst.
            $table->text('deskripsi')->nullable();
            $table->integer('urutan')->default(0); // urutan tampil
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progul_programs');
    }
};
```

### 2.3. Tabel `progul_indikators`

**File:** `database/migrations/2026_10_20_000002_create_progul_indikators_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progul_indikators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')
                  ->constrained('progul_programs')
                  ->cascadeOnDelete();
            $table->text('nama_indikator');        // Nama indikator kinerja
            $table->text('detail')->nullable();    // Detail/deskripsi panjang
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progul_indikators');
    }
};
```

### 2.4. Tabel `progul_indikator_urusan` (Pivot: Indikator ↔ Urusan)

**File:** `database/migrations/2026_10_20_000003_create_progul_indikator_urusan_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progul_indikator_urusan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')
                  ->constrained('progul_indikators')
                  ->cascadeOnDelete();
            $table->string('nama_urusan'); // nama urusan teks bebas (bukan FK)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progul_indikator_urusan');
    }
};
```

> **Catatan:** Urusan disimpan sebagai teks bebas (string), bukan foreign key ke tabel urusan lain, karena data JSON bersifat deskriptif dan belum terstruktur ke entitas master urusan.

### 2.5. Tabel `progul_realisasis`

**File:** `database/migrations/2026_10_20_000004_create_progul_realisasis_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progul_realisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')
                  ->constrained('progul_indikators')
                  ->cascadeOnDelete();
            $table->foreignId('skpd_id')
                  ->nullable()
                  ->constrained('skpds')
                  ->nullOnDelete();
            $table->year('tahun');
            $table->string('target_tahunan')->nullable();  // target untuk tahun tsb (teks: "100%", "5 unit", dll)
            $table->string('nilai_realisasi')->nullable(); // nilai realisasi aktual
            $table->enum('status', ['draf', 'diajukan', 'disetujui', 'ditolak'])->default('draf');
            $table->text('catatan_bappeda')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            // Satu record per indikator per skpd per tahun
            $table->unique(['indikator_id', 'skpd_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progul_realisasis');
    }
};
```

### 2.6. Tabel `progul_bukti_dukung`

**File:** `database/migrations/2026_10_20_000005_create_progul_bukti_dukung_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progul_bukti_dukung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_id')
                  ->constrained('progul_realisasis')
                  ->cascadeOnDelete();
            $table->string('keterangan')->nullable(); // nama/label bukti
            $table->string('file_bukti');             // path file di storage (PDF/gambar)
            $table->enum('status_verifikasi', [
                'belum_diupload',
                'menunggu_verifikasi',
                'disetujui',
                'ditolak',
            ])->default('menunggu_verifikasi');
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progul_bukti_dukung');
    }
};
```

---

## 3. Eloquent Models

### 3.1. `app/Models/ProgulProgram.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgulProgram extends Model
{
    protected $table = 'progul_programs';

    protected $fillable = [
        'nama_program',
        'deskripsi',
        'urutan',
    ];

    public function indikators(): HasMany
    {
        return $this->hasMany(ProgulIndikator::class, 'program_id')->orderBy('urutan');
    }
}
```

### 3.2. `app/Models/ProgulIndikator.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgulIndikator extends Model
{
    protected $table = 'progul_indikators';

    protected $fillable = [
        'program_id',
        'nama_indikator',
        'detail',
        'urutan',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(ProgulProgram::class, 'program_id');
    }

    public function urusan(): HasMany
    {
        return $this->hasMany(ProgulIndikatorUrusan::class, 'indikator_id');
    }

    public function realisasis(): HasMany
    {
        return $this->hasMany(ProgulRealisasi::class, 'indikator_id')->orderBy('tahun');
    }
}
```

### 3.3. `app/Models/ProgulIndikatorUrusan.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgulIndikatorUrusan extends Model
{
    protected $table = 'progul_indikator_urusan';

    protected $fillable = [
        'indikator_id',
        'nama_urusan',
    ];

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(ProgulIndikator::class, 'indikator_id');
    }
}
```

### 3.4. `app/Models/ProgulRealisasi.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgulRealisasi extends Model
{
    protected $table = 'progul_realisasis';

    protected $fillable = [
        'indikator_id',
        'skpd_id',
        'tahun',
        'target_tahunan',
        'nilai_realisasi',
        'status',
        'catatan_bappeda',
        'submitted_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'verified_at'  => 'datetime',
        ];
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(ProgulIndikator::class, 'indikator_id');
    }

    public function skpd(): BelongsTo
    {
        return $this->belongsTo(Skpd::class, 'skpd_id');
    }

    public function buktiDukung(): HasMany
    {
        return $this->hasMany(ProgulBuktiDukung::class, 'realisasi_id');
    }
}
```

### 3.5. `app/Models/ProgulBuktiDukung.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgulBuktiDukung extends Model
{
    protected $table = 'progul_bukti_dukung';

    protected $fillable = [
        'realisasi_id',
        'keterangan',
        'file_bukti',
        'status_verifikasi',
        'catatan_verifikasi',
    ];

    public function realisasi(): BelongsTo
    {
        return $this->belongsTo(ProgulRealisasi::class, 'realisasi_id');
    }
}
```

---

## 4. Seeder Program Unggulan

### 4.1. `database/seeders/ProgulSeeder.php`

Seeder ini membaca `database/seeders/data/data_progul.json` dan mengisi semua tabel program unggulan.

```php
<?php

namespace Database\Seeders;

use App\Models\ProgulIndikator;
use App\Models\ProgulIndikatorUrusan;
use App\Models\ProgulProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgulSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/data_progul.json');

        if (!file_exists($file)) {
            $this->command->error("File tidak ditemukan: {$file}");
            return;
        }

        $data = json_decode(file_get_contents($file), true);

        if (!$data) {
            $this->command->error("Gagal membaca JSON data_progul.json");
            return;
        }

        $this->command->info("Memulai seeder Program Unggulan...");

        DB::transaction(function () use ($data) {
            foreach ($data as $urutan => $item) {
                $namaProgram = trim($item['program'] ?? '');
                if ($namaProgram === '') {
                    continue;
                }

                // Upsert Program
                $program = ProgulProgram::updateOrCreate(
                    ['nama_program' => $namaProgram],
                    ['urutan' => $urutan + 1]
                );

                $this->command->line("  ✓ Program: {$namaProgram}");

                foreach (($item['indikator_kinerja'] ?? []) as $urutanInd => $ind) {
                    $namaIndikator = trim($ind['indikator_kinerja'] ?? '');
                    $detail        = trim($ind['detail'] ?? '');

                    if ($namaIndikator === '') {
                        continue;
                    }

                    // Upsert Indikator
                    $indikator = ProgulIndikator::updateOrCreate(
                        [
                            'program_id'     => $program->id,
                            'nama_indikator' => $namaIndikator,
                        ],
                        [
                            'detail' => $detail ?: null,
                            'urutan' => $urutanInd + 1,
                        ]
                    );

                    // Sync Urusan: hapus lama, insert baru
                    ProgulIndikatorUrusan::where('indikator_id', $indikator->id)->delete();

                    foreach (($ind['urusan'] ?? []) as $namaUrusan) {
                        $namaUrusan = trim($namaUrusan);
                        if ($namaUrusan === '') {
                            continue;
                        }

                        ProgulIndikatorUrusan::create([
                            'indikator_id' => $indikator->id,
                            'nama_urusan'  => $namaUrusan,
                        ]);
                    }

                    $this->command->line("    → Indikator: {$namaIndikator}");
                }
            }
        });

        $totalProgram   = ProgulProgram::count();
        $totalIndikator = ProgulIndikator::count();

        $this->command->info("Selesai! {$totalProgram} program dan {$totalIndikator} indikator berhasil di-seed.");
    }
}
```

### 4.2. Daftarkan ke `DatabaseSeeder.php`

Tambahkan baris berikut di dalam method `run()` di `database/seeders/DatabaseSeeder.php`:

```php
$this->call(ProgulSeeder::class);
```

### 4.3. Cara Menjalankan Seeder

```bash
php artisan db:seed --class=ProgulSeeder
```

---

## 5. Filament Resources — Struktur File

```
app/Filament/Resources/
└── ProgulPrograms/
    ├── ProgulProgramResource.php               ← Resource utama Program Unggulan
    ├── Pages/
    │   ├── ListProgulPrograms.php
    │   ├── CreateProgulProgram.php
    │   └── EditProgulProgram.php
    ├── Schemas/
    │   └── ProgulProgramForm.php
    ├── Tables/
    │   └── ProgulProgramsTable.php
    └── RelationManagers/
        └── IndikatorRelationManager.php        ← Indikator dalam Program

└── ProgulIndikators/
    ├── ProgulIndikatorResource.php             ← Resource Indikator (standalone)
    ├── Pages/
    │   ├── ListProgulIndikators.php
    │   ├── CreateProgulIndikator.php
    │   └── EditProgulIndikator.php
    ├── Schemas/
    │   └── ProgulIndikatorForm.php
    ├── Tables/
    │   └── ProgulIndikatorsTable.php
    └── RelationManagers/
        └── RealisasiRelationManager.php        ← Realisasi dalam Indikator

└── ProgulRealisasis/
    ├── ProgulRealisasiResource.php             ← Resource Realisasi (standalone)
    ├── Pages/
    │   ├── ListProgulRealisasis.php
    │   ├── CreateProgulRealisasi.php
    │   └── EditProgulRealisasi.php
    ├── Schemas/
    │   └── ProgulRealisasiForm.php
    ├── Tables/
    │   └── ProgulRealisasisTable.php
    └── RelationManagers/
        └── BuktiDukungRelationManager.php      ← Bukti Dukung dalam Realisasi
```

---

## 6. Resource: Program Unggulan (`ProgulProgramResource`)

### 6.1. Resource Utama

**File:** `app/Filament/Resources/ProgulPrograms/ProgulProgramResource.php`

```php
<?php

namespace App\Filament\Resources\ProgulPrograms;

use App\Filament\Resources\ProgulPrograms\Pages\CreateProgulProgram;
use App\Filament\Resources\ProgulPrograms\Pages\EditProgulProgram;
use App\Filament\Resources\ProgulPrograms\Pages\ListProgulPrograms;
use App\Filament\Resources\ProgulPrograms\RelationManagers\IndikatorRelationManager;
use App\Filament\Resources\ProgulPrograms\Schemas\ProgulProgramForm;
use App\Filament\Resources\ProgulPrograms\Tables\ProgulProgramsTable;
use App\Models\ProgulProgram;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ProgulProgramResource extends Resource
{
    protected static ?string $model = ProgulProgram::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Star;
    protected static string|UnitEnum|null $navigationGroup = 'Program Unggulan';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Program Unggulan';
    protected static ?string $modelLabel = 'Program Unggulan';
    protected static ?string $pluralModelLabel = 'Program Unggulan';

    public static function form(Schema $schema): Schema
    {
        return ProgulProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgulProgramsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            IndikatorRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProgulPrograms::route('/'),
            'create' => CreateProgulProgram::route('/create'),
            'edit'   => EditProgulProgram::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return in_array($user->role, ['ADMIN', 'BAPPEDA']);
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->role === 'ADMIN';
    }
}
```

### 6.2. Form Schema Program

**File:** `app/Filament/Resources/ProgulPrograms/Schemas/ProgulProgramForm.php`

```php
<?php

namespace App\Filament\Resources\ProgulPrograms\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgulProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Program Unggulan')
                    ->schema([
                        TextInput::make('nama_program')
                            ->label('Nama Program Unggulan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Sungai Penuh Maju'),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi Program')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),

                        TextInput::make('urutan')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil tampil lebih dulu'),
                    ])
                    ->columns(2),
            ]);
    }
}
```

### 6.3. Table Schema Program

**File:** `app/Filament/Resources/ProgulPrograms/Tables/ProgulProgramsTable.php`

```php
<?php

namespace App\Filament\Resources\ProgulPrograms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProgulProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('urutan')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('nama_program')
                    ->label('Nama Program Unggulan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('indikators_count')
                    ->label('Jml. Indikator')
                    ->counts('indikators')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('urutan', 'asc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->role === 'ADMIN'),
                ]),
            ]);
    }
}
```

### 6.4. Pages Program

**File:** `app/Filament/Resources/ProgulPrograms/Pages/ListProgulPrograms.php`
```php
<?php
namespace App\Filament\Resources\ProgulPrograms\Pages;

use App\Filament\Resources\ProgulPrograms\ProgulProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListProgulPrograms extends ListRecords
{
    protected static string $resource = ProgulProgramResource::class;

    protected function getHeaderActions(): array
    {
        return Auth::user()?->role === 'ADMIN'
            ? [CreateAction::make()->label('Tambah Program')]
            : [];
    }
}
```

**File:** `app/Filament/Resources/ProgulPrograms/Pages/CreateProgulProgram.php`
```php
<?php
namespace App\Filament\Resources\ProgulPrograms\Pages;

use App\Filament\Resources\ProgulPrograms\ProgulProgramResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProgulProgram extends CreateRecord
{
    protected static string $resource = ProgulProgramResource::class;
}
```

**File:** `app/Filament/Resources/ProgulPrograms\Pages\EditProgulProgram.php`
```php
<?php
namespace App\Filament\Resources\ProgulPrograms\Pages;

use App\Filament\Resources\ProgulPrograms\ProgulProgramResource;
use Filament\Resources\Pages\EditRecord;

class EditProgulProgram extends EditRecord
{
    protected static string $resource = ProgulProgramResource::class;
}
```

### 6.5. RelationManager: Indikator dalam Program

**File:** `app/Filament/Resources/ProgulPrograms/RelationManagers/IndikatorRelationManager.php`

```php
<?php

namespace App\Filament\Resources\ProgulPrograms\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class IndikatorRelationManager extends RelationManager
{
    protected static string $relationship = 'indikators';
    protected static ?string $title = 'Indikator Kinerja Program';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('urutan')
                    ->label('No. Urut')
                    ->numeric()
                    ->default(0)
                    ->columnSpan(1),

                Textarea::make('nama_indikator')
                    ->label('Nama Indikator Kinerja')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('detail')
                    ->label('Detail / Deskripsi')
                    ->rows(4)
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_indikator')
            ->columns([
                TextColumn::make('urutan')
                    ->label('#')
                    ->badge()
                    ->color('gray')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('nama_indikator')
                    ->label('Indikator Kinerja')
                    ->searchable()
                    ->wrap()
                    ->limit(120),

                TextColumn::make('urusan')
                    ->label('Urusan')
                    ->getStateUsing(fn ($record) => $record->urusan->pluck('nama_urusan')->implode(', '))
                    ->wrap()
                    ->limit(80),

                TextColumn::make('realisasis_count')
                    ->label('Data Realisasi')
                    ->counts('realisasis')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->defaultSort('urutan', 'asc')
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Indikator')
                    ->visible(fn () => Auth::user()?->role === 'ADMIN'),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => Auth::user()?->role === 'ADMIN'),
                DeleteAction::make()
                    ->visible(fn () => Auth::user()?->role === 'ADMIN'),
            ]);
    }
}
```

---

## 7. Resource: Indikator Kinerja Program Unggulan (`ProgulIndikatorResource`)

### 7.1. Resource Utama

**File:** `app/Filament/Resources/ProgulIndikators/ProgulIndikatorResource.php`

```php
<?php

namespace App\Filament\Resources\ProgulIndikators;

use App\Filament\Resources\ProgulIndikators\Pages\CreateProgulIndikator;
use App\Filament\Resources\ProgulIndikators\Pages\EditProgulIndikator;
use App\Filament\Resources\ProgulIndikators\Pages\ListProgulIndikators;
use App\Filament\Resources\ProgulIndikators\RelationManagers\RealisasiRelationManager;
use App\Filament\Resources\ProgulIndikators\Schemas\ProgulIndikatorForm;
use App\Filament\Resources\ProgulIndikators\Tables\ProgulIndikatorsTable;
use App\Models\ProgulIndikator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ProgulIndikatorResource extends Resource
{
    protected static ?string $model = ProgulIndikator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;
    protected static string|UnitEnum|null $navigationGroup = 'Program Unggulan';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Indikator Kinerja';
    protected static ?string $modelLabel = 'Indikator Kinerja Program Unggulan';
    protected static ?string $pluralModelLabel = 'Indikator Kinerja';

    public static function form(Schema $schema): Schema
    {
        return ProgulIndikatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgulIndikatorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RealisasiRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProgulIndikators::route('/'),
            'create' => CreateProgulIndikator::route('/create'),
            'edit'   => EditProgulIndikator::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return in_array($user->role, ['ADMIN', 'BAPPEDA']);
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->role === 'ADMIN';
    }
}
```

### 7.2. Form Schema Indikator

**File:** `app/Filament/Resources/ProgulIndikators/Schemas/ProgulIndikatorForm.php`

```php
<?php

namespace App\Filament\Resources\ProgulIndikators\Schemas;

use App\Models\ProgulIndikator;
use App\Models\ProgulIndikatorUrusan;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgulIndikatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identitas Indikator')
                    ->schema([
                        Select::make('program_id')
                            ->label('Program Unggulan')
                            ->relationship('program', 'nama_program')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('urutan')
                            ->label('No. Urut')
                            ->numeric()
                            ->default(0),

                        Textarea::make('nama_indikator')
                            ->label('Nama Indikator Kinerja')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('detail')
                            ->label('Detail / Deskripsi')
                            ->rows(5)
                            ->nullable()
                            ->columnSpanFull()
                            ->helperText('Deskripsikan kegiatan dan sasaran indikator ini secara detail.'),
                    ])
                    ->columns(2),

                Section::make('Urusan yang Terkait')
                    ->description('Daftar urusan pemerintahan yang terkait dengan indikator ini')
                    ->schema([
                        Repeater::make('urusan')
                            ->relationship('urusan')
                            ->label('')
                            ->schema([
                                TextInput::make('nama_urusan')
                                    ->label('Nama Urusan')
                                    ->required()
                                    ->placeholder('Contoh: Urusan Pendidikan'),
                            ])
                            ->addActionLabel('Tambah Urusan')
                            ->reorderable()
                            ->collapsible()
                            ->columns(1),
                    ]),
            ]);
    }
}
```

### 7.3. Table Schema Indikator

**File:** `app/Filament/Resources/ProgulIndikators/Tables/ProgulIndikatorsTable.php`

```php
<?php

namespace App\Filament\Resources\ProgulIndikators\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProgulIndikatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('program.nama_program')
                    ->label('Program Unggulan')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_indikator')
                    ->label('Indikator Kinerja')
                    ->searchable()
                    ->wrap()
                    ->limit(150),

                TextColumn::make('urusan')
                    ->label('Urusan')
                    ->getStateUsing(fn ($record) => $record->urusan->pluck('nama_urusan')->implode(' | '))
                    ->wrap()
                    ->limit(80)
                    ->color('gray'),

                TextColumn::make('realisasis_count')
                    ->label('Realisasi Tersedia')
                    ->counts('realisasis')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('program_id')
                    ->label('Program Unggulan')
                    ->relationship('program', 'nama_program')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('program_id', 'asc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->role === 'ADMIN'),
                ]),
            ]);
    }
}
```

### 7.4. Pages Indikator

```php
// ListProgulIndikators.php
<?php
namespace App\Filament\Resources\ProgulIndikators\Pages;
use App\Filament\Resources\ProgulIndikators\ProgulIndikatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
class ListProgulIndikators extends ListRecords
{
    protected static string $resource = ProgulIndikatorResource::class;
    protected function getHeaderActions(): array
    {
        return Auth::user()?->role === 'ADMIN'
            ? [CreateAction::make()->label('Tambah Indikator')]
            : [];
    }
}
```
```php
// CreateProgulIndikator.php
<?php
namespace App\Filament\Resources\ProgulIndikators\Pages;
use App\Filament\Resources\ProgulIndikators\ProgulIndikatorResource;
use Filament\Resources\Pages\CreateRecord;
class CreateProgulIndikator extends CreateRecord
{
    protected static string $resource = ProgulIndikatorResource::class;
}
```
```php
// EditProgulIndikator.php
<?php
namespace App\Filament\Resources\ProgulIndikators\Pages;
use App\Filament\Resources\ProgulIndikators\ProgulIndikatorResource;
use Filament\Resources\Pages\EditRecord;
class EditProgulIndikator extends EditRecord
{
    protected static string $resource = ProgulIndikatorResource::class;
}
```

### 7.5. RelationManager: Realisasi dalam Indikator

**File:** `app/Filament/Resources/ProgulIndikators/RelationManagers/RealisasiRelationManager.php`

```php
<?php

namespace App\Filament\Resources\ProgulIndikators\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RealisasiRelationManager extends RelationManager
{
    protected static string $relationship = 'realisasis';
    protected static ?string $title = 'Target & Realisasi Tahunan (5 Tahun)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('skpd_id')
                    ->label('SKPD Pelaksana')
                    ->relationship('skpd', 'nama_skpd')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Kosongkan jika lintas SKPD / belum ditentukan'),

                Select::make('tahun')
                    ->label('Tahun')
                    ->options(array_combine(range(2025, 2029), range(2025, 2029)))
                    ->required(),

                TextInput::make('target_tahunan')
                    ->label('Target Tahunan')
                    ->placeholder('Contoh: 100%, 5 unit, 1.000 orang')
                    ->nullable(),

                TextInput::make('nilai_realisasi')
                    ->label('Realisasi')
                    ->placeholder('Isi setelah periode berjalan')
                    ->nullable(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draf'      => 'Draf',
                        'diajukan'  => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                    ])
                    ->default('draf')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tahun')
            ->columns([
                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD Pelaksana')
                    ->placeholder('- Lintas SKPD -')
                    ->wrap()
                    ->limit(40),

                TextColumn::make('target_tahunan')
                    ->label('Target')
                    ->placeholder('-')
                    ->alignCenter(),

                TextColumn::make('nilai_realisasi')
                    ->label('Realisasi')
                    ->placeholder('Belum diisi')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draf'      => 'gray',
                        'diajukan'  => 'warning',
                        'disetujui' => 'success',
                        'ditolak'   => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draf'      => 'Draf',
                        'diajukan'  => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                        default     => $state,
                    }),
            ])
            ->defaultSort('tahun', 'asc')
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Data Realisasi')
                    ->visible(fn () => in_array(Auth::user()?->role, ['ADMIN', 'BAPPEDA', 'SKPD'])),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn () => Auth::user()?->role === 'ADMIN'),
            ]);
    }
}
```

---

## 8. Resource: Realisasi Program Unggulan (`ProgulRealisasiResource`)

### 8.1. Resource Utama

**File:** `app/Filament/Resources/ProgulRealisasis/ProgulRealisasiResource.php`

```php
<?php

namespace App\Filament\Resources\ProgulRealisasis;

use App\Filament\Resources\ProgulRealisasis\Pages\CreateProgulRealisasi;
use App\Filament\Resources\ProgulRealisasis\Pages\EditProgulRealisasi;
use App\Filament\Resources\ProgulRealisasis\Pages\ListProgulRealisasis;
use App\Filament\Resources\ProgulRealisasis\RelationManagers\BuktiDukungRelationManager;
use App\Filament\Resources\ProgulRealisasis\Schemas\ProgulRealisasiForm;
use App\Filament\Resources\ProgulRealisasis\Tables\ProgulRealisasisTable;
use App\Models\ProgulRealisasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ProgulRealisasiResource extends Resource
{
    protected static ?string $model = ProgulRealisasi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCheck;
    protected static string|UnitEnum|null $navigationGroup = 'Program Unggulan';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Realisasi Program Unggulan';
    protected static ?string $modelLabel = 'Realisasi Program Unggulan';
    protected static ?string $pluralModelLabel = 'Realisasi Program Unggulan';

    public static function form(Schema $schema): Schema
    {
        return ProgulRealisasiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgulRealisasisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BuktiDukungRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProgulRealisasis::route('/'),
            'create' => CreateProgulRealisasi::route('/create'),
            'edit'   => EditProgulRealisasi::route('/{record}/edit'),
        ];
    }

    /**
     * SKPD hanya melihat data realisasi milik SKPD-nya sendiri.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        if ($user?->role === 'SKPD') {
            $query->where('skpd_id', $user->skpd_id);
        }

        return $query;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return in_array($user?->role, ['ADMIN', 'BAPPEDA', 'SKPD']);
    }

    public static function canCreate(): bool
    {
        return in_array(Auth::user()?->role, ['ADMIN', 'SKPD']);
    }
}
```

### 8.2. Form Schema Realisasi

**File:** `app/Filament/Resources/ProgulRealisasis/Schemas/ProgulRealisasiForm.php`

```php
<?php

namespace App\Filament\Resources\ProgulRealisasis\Schemas;

use App\Models\ProgulIndikator;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProgulRealisasiForm
{
    public static function configure(Schema $schema): Schema
    {
        $user   = Auth::user();
        $isSkpd = $user?->role === 'SKPD';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Identitas Realisasi')
                    ->schema([
                        Select::make('indikator_id')
                            ->label('Indikator Kinerja Program Unggulan')
                            ->options(
                                ProgulIndikator::with('program')
                                    ->get()
                                    ->mapWithKeys(fn ($ind) => [
                                        $ind->id => "[{$ind->program->nama_program}] {$ind->nama_indikator}",
                                    ])
                            )
                            ->searchable()
                            ->required()
                            ->disabled($isSkpd)
                            ->columnSpanFull(),

                        Select::make('skpd_id')
                            ->label('SKPD Pelaksana')
                            ->relationship('skpd', 'nama_skpd')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->disabled($isSkpd)
                            ->default($isSkpd ? $user->skpd_id : null)
                            ->helperText('Kosongkan jika lintas SKPD'),

                        Select::make('tahun')
                            ->label('Tahun')
                            ->options(array_combine(range(2025, 2029), range(2025, 2029)))
                            ->required()
                            ->disabled($isSkpd),

                        TextInput::make('target_tahunan')
                            ->label('Target Tahunan')
                            ->nullable()
                            ->placeholder('Contoh: 100%, 5 unit')
                            ->disabled($isSkpd),

                        TextInput::make('nilai_realisasi')
                            ->label('Capaian / Nilai Realisasi')
                            ->nullable()
                            ->placeholder('Isi nilai realisasi aktual'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draf'      => 'Draf',
                                'diajukan'  => 'Diajukan',
                                'disetujui' => 'Disetujui',
                                'ditolak'   => 'Ditolak',
                            ])
                            ->default('draf')
                            ->required()
                            ->disabled($isSkpd),
                    ])
                    ->columns(2),

                Section::make('Catatan Evaluasi Bappeda')
                    ->visible(fn ($record) => !$isSkpd || ($record && $record->catatan_bappeda))
                    ->schema([
                        Textarea::make('catatan_bappeda')
                            ->label('Catatan / Evaluasi Bappeda')
                            ->rows(3)
                            ->disabled($isSkpd)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
```

### 8.3. Table Schema Realisasi

**File:** `app/Filament/Resources/ProgulRealisasis/Tables/ProgulRealisasisTable.php`

```php
<?php

namespace App\Filament\Resources\ProgulRealisasis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProgulRealisasisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('indikator.program.nama_program')
                    ->label('Program Unggulan')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('indikator.nama_indikator')
                    ->label('Indikator Kinerja')
                    ->wrap()
                    ->limit(100)
                    ->searchable(),

                TextColumn::make('skpd.nama_skpd')
                    ->label('SKPD Pelaksana')
                    ->placeholder('- Lintas SKPD -')
                    ->limit(35)
                    ->wrap(),

                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('target_tahunan')
                    ->label('Target')
                    ->placeholder('-')
                    ->alignCenter(),

                TextColumn::make('nilai_realisasi')
                    ->label('Realisasi')
                    ->placeholder('Belum diisi')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draf'      => 'gray',
                        'diajukan'  => 'warning',
                        'disetujui' => 'success',
                        'ditolak'   => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draf'      => 'Draf',
                        'diajukan'  => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                        default     => $state,
                    }),

                TextColumn::make('buktiDukung_count')
                    ->label('Bukti')
                    ->counts('buktiDukung')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('program')
                    ->label('Program Unggulan')
                    ->relationship('indikator.program', 'nama_program')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(array_combine(range(2025, 2029), range(2025, 2029))),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draf'      => 'Draf',
                        'diajukan'  => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                    ]),

                SelectFilter::make('skpd_id')
                    ->label('SKPD Pelaksana')
                    ->relationship('skpd', 'nama_skpd')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('tahun', 'asc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->role === 'ADMIN'),
                ]),
            ]);
    }
}
```

### 8.4. Pages Realisasi

```php
// ListProgulRealisasis.php
<?php
namespace App\Filament\Resources\ProgulRealisasis\Pages;
use App\Filament\Resources\ProgulRealisasis\ProgulRealisasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
class ListProgulRealisasis extends ListRecords
{
    protected static string $resource = ProgulRealisasiResource::class;
    protected function getHeaderActions(): array
    {
        return in_array(Auth::user()?->role, ['ADMIN', 'SKPD'])
            ? [CreateAction::make()->label('Tambah Realisasi')]
            : [];
    }
}
```

```php
// CreateProgulRealisasi.php
<?php
namespace App\Filament\Resources\ProgulRealisasis\Pages;
use App\Filament\Resources\ProgulRealisasis\ProgulRealisasiResource;
use Filament\Resources\Pages\CreateRecord;
class CreateProgulRealisasi extends CreateRecord
{
    protected static string $resource = ProgulRealisasiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Jika role SKPD, paksa skpd_id dari user yang login
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user?->role === 'SKPD') {
            $data['skpd_id'] = $user->skpd_id;
        }
        return $data;
    }
}
```

```php
// EditProgulRealisasi.php
<?php
namespace App\Filament\Resources\ProgulRealisasis\Pages;
use App\Filament\Resources\ProgulRealisasis\ProgulRealisasiResource;
use Filament\Resources\Pages\EditRecord;
class EditProgulRealisasi extends EditRecord
{
    protected static string $resource = ProgulRealisasiResource::class;
}
```

### 8.5. RelationManager: Bukti Dukung dalam Realisasi

**File:** `app/Filament/Resources/ProgulRealisasis/RelationManagers/BuktiDukungRelationManager.php`

```php
<?php

namespace App\Filament\Resources\ProgulRealisasis\RelationManagers;

use App\Models\ProgulBuktiDukung;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BuktiDukungRelationManager extends RelationManager
{
    protected static string $relationship = 'buktiDukung';
    protected static ?string $title = 'Bukti Dukung Realisasi';
    protected static ?string $modelLabel = 'Bukti Dukung';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('keterangan')
                    ->label('Keterangan / Label Bukti')
                    ->placeholder('Contoh: Laporan Triwulan I, SK Walikota, dll.')
                    ->nullable()
                    ->columnSpanFull(),

                FileUpload::make('file_bukti')
                    ->label('Upload File Bukti')
                    ->disk('public')
                    ->directory('bukti-progul')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'])
                    ->maxSize(5120) // 5 MB
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('keterangan')
            ->columns([
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('Tanpa keterangan')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('status_verifikasi')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'belum_diupload'       => 'gray',
                        'menunggu_verifikasi'  => 'warning',
                        'disetujui'            => 'success',
                        'ditolak'              => 'danger',
                        default                => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'belum_diupload'       => 'Belum Diupload',
                        'menunggu_verifikasi'  => 'Menunggu Verifikasi',
                        'disetujui'            => 'Disetujui',
                        'ditolak'              => 'Ditolak',
                        default                => $state,
                    }),

                TextColumn::make('catatan_verifikasi')
                    ->label('Catatan')
                    ->limit(40)
                    ->placeholder('-'),

                TextColumn::make('file_bukti')
                    ->label('File')
                    ->formatStateUsing(fn (?string $state) => $state ? 'Lihat / Unduh' : '-')
                    ->url(fn (ProgulBuktiDukung $record): ?string => $record->file_bukti
                        ? url('storage/' . $record->file_bukti)
                        : null)
                    ->openUrlInNewTab()
                    ->color(fn (?string $state): ?string => $state ? 'primary' : 'gray')
                    ->icon(fn (?string $state): ?string => $state ? 'heroicon-o-document-arrow-down' : null),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Upload Bukti Dukung')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['status_verifikasi'] = 'menunggu_verifikasi';
                        return $data;
                    })
                    ->visible(fn () => in_array(Auth::user()?->role, ['ADMIN', 'SKPD'])),
            ])
            ->recordActions([
                Action::make('verifikasi')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Select::make('status_verifikasi')
                            ->label('Keputusan')
                            ->options([
                                'disetujui' => 'Setujui',
                                'ditolak'   => 'Tolak',
                            ])
                            ->required(),
                        Textarea::make('catatan_verifikasi')
                            ->label('Catatan Verifikasi')
                            ->required(fn (callable $get) => $get('status_verifikasi') === 'ditolak'),
                    ])
                    ->action(fn (ProgulBuktiDukung $record, array $data) => $record->update([
                        'status_verifikasi'  => $data['status_verifikasi'],
                        'catatan_verifikasi' => $data['catatan_verifikasi'] ?? null,
                    ]))
                    ->visible(fn () => in_array(Auth::user()?->role, ['ADMIN', 'BAPPEDA'])),

                EditAction::make()
                    ->visible(fn () => in_array(Auth::user()?->role, ['ADMIN', 'SKPD'])),

                DeleteAction::make()
                    ->before(function (ProgulBuktiDukung $record) {
                        if ($record->file_bukti && Storage::disk('public')->exists($record->file_bukti)) {
                            Storage::disk('public')->delete($record->file_bukti);
                        }
                    })
                    ->visible(fn () => in_array(Auth::user()?->role, ['ADMIN', 'SKPD'])),
            ]);
    }
}
```

---

## 9. Navigasi — Ringkasan Grup "Program Unggulan"

Di Admin Panel Filament, ketiga resource ini akan muncul dalam satu grup navigasi **"Program Unggulan"**:

| Urutan | Label Navigasi | Icon | Akses |
|---|---|---|---|
| 1 | Program Unggulan | `Heroicon::Star` | ADMIN, BAPPEDA |
| 2 | Indikator Kinerja | `Heroicon::ClipboardDocumentList` | ADMIN, BAPPEDA |
| 3 | Realisasi Program Unggulan | `Heroicon::DocumentCheck` | ADMIN, BAPPEDA, SKPD |

---

## 10. Panduan Eksekusi Langkah-demi-Langkah

AI Agent atau Software Engineer mengikuti urutan berikut:

### Langkah 1 — Buat File Migration (5 file)
Buat semua file migration sesuai bagian **2.2 s/d 2.6**, dengan nama file:
```
2026_10_20_000001_create_progul_programs_table.php
2026_10_20_000002_create_progul_indikators_table.php
2026_10_20_000003_create_progul_indikator_urusan_table.php
2026_10_20_000004_create_progul_realisasis_table.php
2026_10_20_000005_create_progul_bukti_dukung_table.php
```

### Langkah 2 — Jalankan Migration
```bash
php artisan migrate
```

### Langkah 3 — Buat 5 Model Eloquent
Buat semua model sesuai bagian **3.1 s/d 3.5**:
- `app/Models/ProgulProgram.php`
- `app/Models/ProgulIndikator.php`
- `app/Models/ProgulIndikatorUrusan.php`
- `app/Models/ProgulRealisasi.php`
- `app/Models/ProgulBuktiDukung.php`

### Langkah 4 — Buat Seeder dan Jalankan
Buat `database/seeders/ProgulSeeder.php` sesuai bagian **4.1**, kemudian:
```bash
php artisan db:seed --class=ProgulSeeder
```

### Langkah 5 — Buat Filament Resource: ProgulPrograms
Buat semua file di direktori `app/Filament/Resources/ProgulPrograms/` sesuai bagian **6**.

### Langkah 6 — Buat Filament Resource: ProgulIndikators
Buat semua file di direktori `app/Filament/Resources/ProgulIndikators/` sesuai bagian **7**.

### Langkah 7 — Buat Filament Resource: ProgulRealisasis
Buat semua file di direktori `app/Filament/Resources/ProgulRealisasis/` sesuai bagian **8**.

### Langkah 8 — Buat Storage Link (jika belum ada)
```bash
php artisan storage:link
```

### Langkah 9 — Clear Cache
```bash
php artisan optimize:clear
```

---

## 11. Checklist Pengujian & Verifikasi

### 11.1. Verifikasi Data Seeder
- [ ] Seeder berjalan tanpa error
- [ ] 8 program unggulan tersimpan di tabel `progul_programs`
- [ ] Semua indikator kinerja tersimpan di `progul_indikators`
- [ ] Semua urusan tersimpan di `progul_indikator_urusan`

### 11.2. Verifikasi Admin Panel — Role ADMIN
- [ ] Grup **"Program Unggulan"** muncul di sidebar dengan 3 menu
- [ ] **Program Unggulan**: bisa buka list, bisa Create/Edit/Delete program
- [ ] **Indikator Kinerja**: bisa buka list, filter per program, bisa Create/Edit/Delete indikator
- [ ] Saat edit indikator: RelationManager **Realisasi** tampil dan bisa tambah data realisasi
- [ ] Saat edit realisasi: RelationManager **Bukti Dukung** tampil dan bisa upload file PDF
- [ ] **Realisasi Program Unggulan**: list semua data, filter per program/tahun/status/SKPD

### 11.3. Verifikasi Admin Panel — Role BAPPEDA
- [ ] Grup **"Program Unggulan"** muncul di sidebar
- [ ] Bisa **melihat** data Program, Indikator, dan Realisasi
- [ ] **Tidak bisa** Create/Delete Program dan Indikator (tombol tersembunyi)
- [ ] Bisa melakukan **verifikasi** Bukti Dukung (setujui / tolak)

### 11.4. Verifikasi Admin Panel — Role SKPD
- [ ] Menu Program Unggulan dan Indikator **TIDAK tampil** di sidebar
- [ ] Menu **Realisasi Program Unggulan** tampil di sidebar
- [ ] List Realisasi hanya menampilkan data dengan `skpd_id = skpd user yang login`
- [ ] Bisa **mengisi nilai realisasi** dan **upload bukti dukung**

### 11.5. Verifikasi Fungsional
- [ ] Data 5 tahun (2025–2029) bisa dimasukkan per indikator per SKPD
- [ ] File bukti dukung tersimpan di `storage/app/public/bukti-progul/`
- [ ] Delete bukti dukung menghapus file fisik dari storage (tidak meninggalkan orphaned file)
- [ ] Filter tabel realisasi berfungsi: per program, per tahun, per status, per SKPD

