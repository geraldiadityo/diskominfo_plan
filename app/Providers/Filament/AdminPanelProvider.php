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
