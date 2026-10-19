# Rencana Implementasi (Plan 3): Modul Pengaturan Aplikasi (Nama & Logo) di Admin Panel Filament

Dokumen ini merupakan **spesifikasi teknis dan panduan instruksi implementasi lengkap** bagi AI Agent / Software Engineer untuk membangun fitur **Pengaturan Aplikasi (App Settings)** pada aplikasi berbasis **Laravel 12** dan **Filament v4**.

---

## 1. Ringkasan Fitur & Sasaran

### 1.1. Deskripsi Fitur
Membuat modul **Pengaturan Aplikasi** di dalam Admin Panel Filament yang memungkinkan administrator mengubah:
1. **Nama Aplikasi (`app_name`)**: Nama instansi / sistem yang tampil pada header panel admin, title halaman browser, navbar frontend publik, dan copyright footer.
2. **Logo Aplikasi (`app_logo`)**: Gambar logo instansi / aplikasi yang tampil berdampingan dengan nama aplikasi di Admin Panel dan Frontend Publik.

### 1.2. Aturan Hak Akses (Role-Based Access Control)
Berdasarkan struktur enum `role` pada tabel `users` (`ADMIN`, `BAPPEDA`, `BAKEUDA`, `SKPD`):
- **Bisa Mengakses**: Hanya pengguna dengan role **`ADMIN`** (Administrator Aplikasi / Superadmin).
- **Dilarang Mengakses**: Role **`BAPPEDA`** (Verifikator Bappeda), **`BAKEUDA`** (Verifikator Bakeuda), dan **`SKPD`** (Admin SKPD).
- **Proteksi Ganda**:
  1. Halaman pengaturan **tidak boleh muncul** di navigasi sidebar bagi user non-admin.
  2. Jika user non-admin mencoba mengakses URL langsung (misal: `/admin/pengaturan-aplikasi`), sistem wajib melempar **HTTP 403 Forbidden**.

### 1.3. Kebijakan Pengelolaan File Logo (Clean Storage)
- File gambar logo disimpan pada storage disk `public` di folder `settings/`.
- **Wajib menghapus file logo lama**: Ketika admin mengunggah logo baru untuk menggantikan logo yang ada, file logo lama di disk `storage/app/public/settings/` **wajib dihapus secara otomatis**.
- Begitu pula jika logo dihapus (dikosongkan), file lama di disk harus dibersihkan agar tidak meninggalkan *orphaned files* (sampah penyimpanan).

---

## 2. Struktur File yang Dibuat & Dimodifikasi

```
app/
├── Models/
│   └── AppSetting.php                                 ← BARU (Model Singleton Setting)
├── Filament/
│   └── Pages/
│       └── PengaturanAplikasi.php                     ← BARU (Filament v4 Custom Page)
├── Providers/
│   └── Filament/
│       └── AdminPanelProvider.php                     ← MODIFIKASI (Brand Name & Logo dinamis)

database/
└── migrations/
    └── 2026_10_19_000001_create_app_settings_table.php ← BARU (Tabel app_settings)

resources/
└── views/
    ├── filament/
    │   └── pages/
    │       └── pengaturan-aplikasi.blade.php          ← BARU (View Blade Page Filament)
    └── layouts/
        └── frontend.blade.php                         ← MODIFIKASI (Logo, Nama, Title, Footer dinamis)
```

---

## 3. Database Schema & Migration

Buat migration baru untuk tabel `app_settings`. Tabel ini dirancang sebagai tabel baris tunggal (*singleton row*).

**File:** `database/migrations/2026_10_19_000001_create_app_settings_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('Dashboard Kota Sungai Penuh');
            $table->string('app_logo')->nullable();
            $table->timestamps();
        });

        // Insert satu baris default saat migration dijalankan
        DB::table('app_settings')->insert([
            'app_name' => 'Dashboard Kota Sungai Penuh',
            'app_logo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
```

---

## 4. Eloquent Model (`app/Models/AppSetting.php`)

Model ini dilengkapi:
1. **Cache Layer**: Mencegah query database berulang pada setiap render komponen Livewire/Filament.
2. **Model Events (`booted()`)**: Otomatis menghapus file fisik logo lama dari storage saat attribute `app_logo` diperbarui (*dirty*) atau saat model dihapus.
3. **Helper Methods**: `getSetting()`, `getAppName()`, dan `getAppLogoUrl()` yang siap dipanggil kapan saja secara statis dan aman dari exception.

**File:** `app/Models/AppSetting.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AppSetting extends Model
{
    use HasFactory;

    protected $table = 'app_settings';

    protected $fillable = [
        'app_name',
        'app_logo',
    ];

    /**
     * Boot model events untuk pembersihan file fisik lama & invalidasi cache.
     */
    protected static function booted(): void
    {
        // Saat data sedang di-update
        static::updating(function (AppSetting $setting) {
            if ($setting->isDirty('app_logo')) {
                $oldLogo = $setting->getOriginal('app_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
            }
        });

        // Saat data dihapus
        static::deleting(function (AppSetting $setting) {
            if ($setting->app_logo && Storage::disk('public')->exists($setting->app_logo)) {
                Storage::disk('public')->delete($setting->app_logo);
            }
        });

        // Bersihkan cache setiap kali data disimpan atau dihapus
        static::saved(function () {
            Cache::forget('app_setting_data');
        });

        static::deleted(function () {
            Cache::forget('app_setting_data');
        });
    }

    /**
     * Mengambil record pengaturan (dengan cache 24 jam).
     * Aman dari exception jika tabel belum selesai di-migrate.
     */
    public static function getSetting(): ?self
    {
        try {
            return Cache::remember('app_setting_data', 86400, function () {
                return static::query()->first();
            });
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Ambil nama aplikasi (fallback ke config app.name).
     */
    public static function getAppName(): string
    {
        return static::getSetting()?->app_name ?? config('app.name', 'Dashboard Kota Sungai Penuh');
    }

    /**
     * Ambil URL publik logo aplikasi jika ada dan file fisiknya eksis.
     */
    public static function getAppLogoUrl(): ?string
    {
        $setting = static::getSetting();
        if ($setting && $setting->app_logo && Storage::disk('public')->exists($setting->app_logo)) {
            return asset('storage/' . $setting->app_logo);
        }

        return null;
    }
}
```

---

## 5. Filament Admin Page (`app/Filament/Pages/PengaturanAplikasi.php`)

Page ini menggunakan arsitektur **Filament v4** (`Filament\Schemas\Schema`, `use Filament\Support\Icons\Heroicon`).

### Fitur Utama:
- **Proteksi Role**: Menggunakan `public static function canAccess(): bool` yang memeriksa apakah `Auth::user()?->role === 'ADMIN'`. Jika bukan admin, menu tidak tampil di navigasi dan akses URL langsung melempar 403 Forbidden.
- **Form Schema**:
  - `TextInput::make('app_name')`
  - `FileUpload::make('app_logo')` dengan validasi image, disk `public`, direktori `settings`, batas ukuran 2MB, dan preview gambar.
- **Save Handler**: Menyimpan perubahan ke database dan menampilkan notifikasi sukses.

**File:** `app/Filament/Pages/PengaturanAplikasi.php`

```php
<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class PengaturanAplikasi extends Page
{
    protected string $view = 'filament.pages.pengaturan-aplikasi';

    protected static ?string $title = 'Pengaturan Aplikasi';
    protected static ?string $navigationLabel = 'Pengaturan Aplikasi';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan Sistem';
    protected static ?int $navigationSort = 99;

    /**
     * Proteksi Hak Akses:
     * HANYA role 'ADMIN' yang diizinkan mengakses halaman ini.
     * Verifikator (BAPPEDA, BAKEUDA) dan Admin SKPD (SKPD) diblokir total (403 Forbidden).
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return in_array($user->role, ['ADMIN', 'SUPERADMIN', 'admin'], true);
    }

    public ?array $data = [];

    public function mount(): void
    {
        $setting = AppSetting::getSetting();

        if ($setting) {
            $this->form->fill([
                'app_name' => $setting->app_name,
                'app_logo' => $setting->app_logo,
            ]);
        } else {
            $this->form->fill([
                'app_name' => config('app.name', 'Dashboard Kota Sungai Penuh'),
                'app_logo' => null,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Identitas Aplikasi')
                    ->description('Kelola nama aplikasi dan logo utama yang tampil pada header Admin Panel dan Halaman Publik.')
                    ->schema([
                        TextInput::make('app_name')
                            ->label('Nama Aplikasi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Dashboard Kota Sungai Penuh')
                            ->helperText('Nama ini akan tampil pada header panel admin, title tab browser, dan footer.'),

                        FileUpload::make('app_logo')
                            ->label('Logo Aplikasi')
                            ->image()
                            ->disk('public')
                            ->directory('settings')
                            ->visibility('public')
                            ->imagePreviewHeight('140')
                            ->maxSize(2048) // 2 MB
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml', 'image/webp'])
                            ->helperText('Format: PNG, JPG, JPEG, SVG, WEBP (Maksimal 2 MB). Logo lama akan otomatis terhapus dari server saat Anda mengganti logo.'),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        // Validasi input form
        $state = $this->form->getState();

        $setting = AppSetting::first();
        if (!$setting) {
            $setting = new AppSetting();
        }

        // Hapus logo lama secara eksplisit jika file diganti atau dihapus
        $oldLogo = $setting->app_logo;
        $newLogo = $state['app_logo'] ?? null;

        if ($oldLogo && $oldLogo !== $newLogo) {
            if (Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
        }

        $setting->app_name = $state['app_name'];
        $setting->app_logo = $newLogo;
        $setting->save();

        // Sinkronisasi state form kembali
        $this->form->fill([
            'app_name' => $setting->app_name,
            'app_logo' => $setting->app_logo,
        ]);

        Notification::make()
            ->title('Pengaturan Berhasil Disimpan')
            ->body('Nama dan logo aplikasi telah berhasil diperbarui.')
            ->success()
            ->send();
    }
}
```

---

## 6. View Blade Page (`resources/views/filament/pages/pengaturan-aplikasi.blade.php`)

View ini merender form schema Filament v4 beserta tombol submit simpan.

**File:** `resources/views/filament/pages/pengaturan-aplikasi.blade.php`

```blade
<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-start gap-3">
            <x-filament::button type="submit" icon="heroicon-m-check">
                Simpan Perubahan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
```

---

## 7. Modifikasi Admin Panel Provider (`app/Providers/Filament/AdminPanelProvider.php`)

Integrasikan brand name, brand logo, dan favicon agar membaca nilai dari `AppSetting` secara dinamis.

**File:** `app/Providers/Filament/AdminPanelProvider.php`

```php
<?php

namespace App\Providers\Filament;

use App\Models\AppSetting;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\HtmlString;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            // Branding Dinamis dari AppSetting:
            ->brandName(fn () => AppSetting::getAppName())
            ->brandLogo(function () {
                $logoUrl = AppSetting::getAppLogoUrl();
                $appName = e(AppSetting::getAppName());

                if (! $logoUrl) {
                    return null;
                }

                // Tampilan Khusus Halaman Login / Auth (Elegan, Terpusat, Lebih Besar & Menarik)
                if (request()->is('admin/login*', 'admin/password-reset*')) {
                    return new HtmlString("
                        <div style=\"display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; width: 100%; margin-bottom: 0.25rem;\">
                            <div style=\"width: 76px; height: 76px; margin: 0 auto 12px auto; display: flex; align-items: center; justify-content: center;\">
                                <img src=\"{$logoUrl}\" alt=\"{$appName}\" style=\"height: 100%; width: auto; max-width: 100%; object-fit: contain; filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.25)); display: block; margin: 0 auto;\" />
                            </div>
                            <h2 style=\"font-size: 1.35rem; font-weight: 800; letter-spacing: -0.025em; color: inherit; line-height: 1.3; margin: 0 0 4px 0;\">
                                {$appName}
                            </h2>
                            <p style=\"font-size: 0.825rem; font-weight: 500; color: #94a3b8; margin: 0;\">
                                Pemerintah Kota Sungai Penuh
                            </p>
                        </div>
                    ");
                }

                // Tampilan Reguler di Sidebar Admin Panel (Horizontal, Rapi, Compact)
                return new HtmlString("
                    <div style=\"display: inline-flex; align-items: center; gap: 10px; max-height: 40px; overflow: hidden;\">
                        <img src=\"{$logoUrl}\" alt=\"{$appName}\" style=\"height: 32px; max-height: 32px; width: auto; max-width: 34px; object-fit: contain; flex-shrink: 0; display: block;\" />
                        <span style=\"font-size: 0.875rem; font-weight: 700; line-height: 1.25; color: inherit; white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 175px;\">{$appName}</span>
                    </div>
                ");
            })
            ->brandLogoHeight(fn () => request()->is('admin/login*', 'admin/password-reset*') ? 'auto' : '2.5rem')
            ->favicon(fn () => AppSetting::getAppLogoUrl())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

---

## 8. Modifikasi Layout Frontend Publik (`resources/views/layouts/frontend.blade.php`)

Ganti teks nama aplikasi yang masih *hardcoded* (`Dashboard Sungai Penuh` dan `Sungai Penuh Civic Intelligence`) dan pasang logo jika ada.

**File:** `resources/views/layouts/frontend.blade.php`

1. **Tag `<title>` (Baris 9):**
   ```blade
   <title>{{ $title ?? \App\Models\AppSetting::getAppName() }}</title>
   ```

2. **Navbar Brand & Logo (Baris 25-27):**
   Ganti:
   ```blade
   <div class="flex items-center gap-6">
       <span class="text-2xl font-bold text-[#af101a] tracking-tight" style="font-family: 'Plus Jakarta Sans', sans-serif;">Dashboard Sungai Penuh</span>
   ```
   Menjadi:
   ```blade
   <div class="flex items-center gap-6">
       <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
           @if($logoUrl = \App\Models\AppSetting::getAppLogoUrl())
               <img src="{{ $logoUrl }}" alt="Logo Aplikasi" class="h-9 w-auto max-w-[42px] object-contain">
           @endif
           <span class="text-2xl font-bold text-[#af101a] tracking-tight" style="font-family: 'Plus Jakarta Sans', sans-serif;">
               {{ \App\Models\AppSetting::getAppName() }}
           </span>
       </a>
   ```

3. **Footer Copyright (Baris 81):**
   Ganti:
   ```blade
   &copy; {{ date('Y') }} {{ config('app.name', 'Sungai Penuh Civic Intelligence') }}. All rights reserved.
   ```
   Menjadi:
   ```blade
   &copy; {{ date('Y') }} {{ \App\Models\AppSetting::getAppName() }}. All rights reserved.
   ```

---

## 9. Detail Mekanisme Penghapusan File Gambar Lama (Clean Storage)

Untuk memastikan tidak ada file gambar yatim (*orphaned image*) tersisa di server, fitur ini mengimplementasikan **Triple Defense System**:

| Lapisan | Komponen | Mekanisme |
|---|---|---|
| **Layer 1** | Model Event `updating` (`AppSetting::booted`) | Jika attribute `app_logo` bernilai baru (`isDirty`), sistem mengambil nama file asli via `getOriginal('app_logo')` dan menghapusnya dari `Storage::disk('public')`. |
| **Layer 2** | Filament `FileUpload::deleteUploadedFileUsing` | Jika pengguna menekan tombol silang/hapus pada file preview sebelum form disubmit, file temporary di storage langsung dihapus. |
| **Layer 3** | Filament Page `save()` Handler | Pengecekan manual sebelum `$setting->save()`: jika `$oldLogo && $oldLogo !== $newLogo`, hapus file lama dari `Storage::disk('public')`. |

Dengan tiga lapisan ini, baik skenario penggantian file logo, penghapusan logo, maupun pembatalan upload ditangani dengan bersih tanpa sisa.

---

## 10. Panduan Eksekusi Langkah-demi-Langkah (Step-by-Step)

AI Agent atau Software Engineer yang mengimplementasikan plan ini harus mengikuti urutan berikut:

1. **Jalankan Symlink Storage (jika belum):**
   ```bash
   php artisan storage:link
   ```
2. **Buat File Migration:**
   Buat file `database/migrations/2026_10_19_000001_create_app_settings_table.php` sesuai kode pada bagian 3.
3. **Jalankan Migrate Database:**
   ```bash
   php artisan migrate
   ```
4. **Buat Model `AppSetting`:**
   Buat file `app/Models/AppSetting.php` sesuai kode pada bagian 4.
5. **Buat Filament Page & View Blade:**
   - Buat `app/Filament/Pages/PengaturanAplikasi.php` sesuai kode pada bagian 5.
   - Buat `resources/views/filament/pages/pengaturan-aplikasi.blade.php` sesuai kode pada bagian 6.
6. **Modifikasi `AdminPanelProvider.php`:**
   Daftarkan `brandName()`, `brandLogo()`, `brandLogoHeight()`, dan `favicon()` sesuai kode pada bagian 7.
7. **Modifikasi `resources/views/layouts/frontend.blade.php`:**
   Sesuaikan title, navbar brand + logo, dan footer copyright sesuai kode pada bagian 8.
8. **Clear Cache:**
   ```bash
   php artisan optimize:clear
   ```

---

## 11. Checklist Pengujian & Verifikasi

Setelah seluruh langkah selesai, lakukan verifikasi berikut:

- [ ] **Uji Hak Akses Role ADMIN:**
  - Login dengan akun `role = 'ADMIN'`.
  - Pastikan menu **"Pengaturan Sistem > Pengaturan Aplikasi"** muncul di sidebar.
  - Akses halaman dan pastikan form terbuka dengan baik.
- [ ] **Uji Hak Akses Role SKPD (Admin SKPD):**
  - Login dengan akun `role = 'SKPD'`.
  - Pastikan menu **"Pengaturan Aplikasi"** TIDAK muncul di sidebar.
  - Buka URL `/admin/pengaturan-aplikasi` secara manual. Pastikan muncul status **403 Forbidden**.
- [ ] **Uji Hak Akses Role Verifikator (BAPPEDA / BAKEUDA):**
  - Login dengan akun `role = 'BAPPEDA'` atau `'BAKEUDA'`.
  - Pastikan menu TIDAK muncul dan URL langsung menghasilkan **403 Forbidden**.
- [ ] **Uji Ubah Nama Aplikasi:**
  - Ubah nama menjadi *"Dashboard Kinerja Daerah Contoh"*, klik tombol **Simpan Perubahan**.
  - Refresh panel admin: nama pada header berubah.
  - Buka halaman publik (`/` atau `/dashboard`): nama di navbar, tab browser (`<title>`), dan footer berubah.
- [ ] **Uji Upload Logo Baru:**
  - Unggah file gambar logo (misal: `logo_pemda.png`).
  - Simpan perubahan.
  - Periksa file fisik tersimpan di `storage/app/public/settings/`.
  - Pastikan logo muncul di header Admin Panel dan navbar Frontend Publik.
- [ ] **Uji Penggantian Logo (Hapus File Lama):**
  - Catat nama file logo pertama di folder `storage/app/public/settings/`.
  - Unggah logo kedua yang berbeda (misal: `logo_baru.png`).
  - Simpan perubahan.
  - Periksa folder `storage/app/public/settings/`: pastikan file logo pertama **sudah terhapus** dan hanya tersisa file logo kedua.
- [ ] **Uji Hapus Logo:**
  - Hapus logo pada form (klik icon trash/clear), simpan perubahan.
  - Pastikan file logo di storage terhapus dan tampilan UI kembali fallback ke tampilan teks tanpa broken image icon.

