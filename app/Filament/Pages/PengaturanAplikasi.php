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

