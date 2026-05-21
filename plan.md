# Front-Facing Dashboard Implementation Plan (with Repository Pattern)

## 1. Context & Objective
- **Project Context:** Aplikasi web berbasis Laravel untuk sistem perencanaan dan realisasi perencanaan daerah. Bagian admin/backend dikelola menggunakan FilamentPHP.
- **Objective:** Membuat halaman dashboard depan (public-facing) untuk visualisasi data statistik, kemajuan rencana kerja, dan pendapatan daerah.
- **Design Reference:** Desain UI/UX mengacu pada platform Stitch/v0 dengan ID: `3780839767116184844`.
- **Architecture Standard:** Menggunakan **Repository Pattern** untuk menjembatani query data antara database/Model dan komponen Frontend.
- **Tech Stack Frontend:** - **Laravel Livewire** (State management & reaktivitas utama).
  - **Alpine.js** (Interaksi UI lokal di sisi klien).
  - **Tailwind CSS** (Framework styling).

## 2. Target Models
Pengambilan data terpusat melalui repository akan mencakup model-model berikut:
- `Skpd`
- `RenjaSkpd`
- `RealisasiKegiatan`
- `TargetPendapatan`
- `TransaksiPendapatan`

## 3. Execution Steps (Untuk AI Agent)

### Phase 1: Repository Pattern Setup
1. **Buat Interface Repository:**
   - **File:** `app/Repositories/DashboardRepositoryInterface.php`
   - **Tugas:** Definisikan metode kontrak untuk mengambil data statistik, seperti:
     - `getSkpdCount(): int`
     - `getRealisasiKeuanganTotal(): float`
     - `getTargetPendapatanTotal(): float`
     - `getChartDataPendapatan(): array`
2. **Buat Implementasi Eloquent Repository:**
   - **File:** `app/Repositories/Eloquent/DashboardRepository.php`
   - **Tugas:** Implementasikan interface di atas dengan menulis query Eloquent ORM secara spesifik dan optimal (gunakan eager loading untuk menghindari masalah N+1 query).
3. **Daftarkan Repository ke Service Provider:**
   - **File:** `app/Providers/AppServiceProvider.php`
   - **Tugas:** Lakukan binding antara `DashboardRepositoryInterface` dengan `DashboardRepository` di dalam metode `register()`.

### Phase 2: Frontend Layout & Routing
1. **Buat Layout Blade Publik:**
   - **File:** `resources/views/layouts/frontend.blade.php`
   - **Tugas:** Setup HTML5 boilerplate terpisah dari Filament admin. Sertakan `@livewireStyles`, `@livewireScripts`, dan asset kompilasi Vite (`@vite(...)`). Sediakan space untuk navbar, main content container (`{{ $slot }}`), dan footer.
2. **Konfigurasi Routing:**
   - **File:** `routes/web.php`
   - **Tugas:** Daftarkan route untuk halaman depan (`/` atau `/dashboard-publik`) yang mengarah ke komponen Livewire utama. Pastikan tidak tumpang tindih dengan route admin Filament.

### Phase 3: Livewire Component & Dependency Injection
1. **Generate Komponen Livewire:**
   - **Command:** `php artisan make:livewire PublicDashboard`
2. **Injeksi Repository ke Komponen:**
   - **File:** `app/Livewire/PublicDashboard.php`
   - **Tugas:** Gunakan *Method Injection* pada fungsi `render()` atau konstruktor untuk memanggil `DashboardRepositoryInterface`. 
   - Komponen hanya bertugas menerima data dari Repository dan meneruskannya ke view, tanpa menulis query database langsung di dalam controller Livewire.

### Phase 4: UI Implementation (Stitch ID: 3780839767116184844)
1. **Bangun Tampilan Dashboard:**
   - **File:** `resources/views/livewire/public-dashboard.blade.php`
   - **Tugas:** Terapkan styling Tailwind CSS mengikuti referensi desain ID `3780839767116184844`.
   - Susun komponen kartu metrik (Summary Cards) untuk ringkasan target vs realisasi secara rapi dan responsif (`grid grid-cols-1 md:grid-cols-3`).
2. **Reaktivitas UI (Livewire + Alpine.js):**
   - Jika terdapat filter (seperti pemilihan tahun anggaran atau kategori SKPD), gunakan `wire:model` untuk memperbarui parameter pada pemanggilan metode di Repository.
   - Gunakan **Alpine.js** untuk komponen mikro yang membutuhkan kecepatan respons di browser, seperti toggle menu navigasi mobile, tabs dropdown, atau modal detail.

### Phase 5: Optimization & Loading State
1. Tambahkan skeleton loading atau spinner menggunakan directive `wire:loading` ketika data sedang di-refresh melalui repository.
2. Pastikan properti yang dikirim dari repository ke dalam grafik/chart sudah berbentuk format array/JSON bersih yang siap dibaca oleh komponen visual.

## 4. Agent Rules & Constraints
- **CRITICAL:** Dilarang mengubah struktur atau file di dalam `app/Filament/` karena fungsionalitas admin panel sudah berjalan dengan baik.
- Terapkan pemisahan logika yang tegas: query database hanya berada di dalam file Repository, sedangkan penanganan state halaman berada di Livewire.
- Pastikan kode aman, bersih, dan mengikuti standar Laravel terbaru.