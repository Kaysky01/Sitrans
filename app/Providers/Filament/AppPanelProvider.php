<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\RateLimitedLogin;
use App\Http\Middleware\EnsureTenantIsActive;
use App\Http\Middleware\ResolveTenant;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->viteTheme('resources/css/filament/app/theme.css')
            ->brandName('Sitrans')
            ->brandLogo(asset('images/sitrans-logo.png'))
            ->brandLogoHeight('3rem')
            ->login(RateLimitedLogin::class)
            ->passwordReset()
            ->profile()
            ->colors([
                // Explicit brand scale (generated from #0b2545) — avoids Filament's
                // auto-generated washed blues so buttons/badges stay on-brand.
                'primary' => [
                    50 => '#f2f6fc',
                    100 => '#dfeaf6',
                    200 => '#c2d3e8',
                    300 => '#9ab4d8',
                    400 => '#6087b3',
                    500 => '#16406f',
                    600 => '#0b2545',
                    700 => '#09203c',
                    800 => '#071a30',
                    900 => '#051222',
                    950 => '#001026',
                ],
                'gray' => Color::Slate,
            ])
            ->font('Plus Jakarta Sans')
            ->darkMode(false)
            // Di desktop navigasi selalu terlihat dan hanya dapat diciutkan;
            // hamburger dipakai otomatis oleh Filament pada layar kecil.
            ->sidebarCollapsibleOnDesktop()
            // Urutan mengikuti alur kerja harian: layani transaksi, kelola menu,
            // pantau persediaan, lalu atur operasional usaha.
            ->navigationGroups([
                'Transaksi',
                'Menu',
                'Inventaris',
                'Pengaturan',
            ])
            // Konten mengisi lebar jendela, bukan berhenti di kotak tengah.
            ->maxContentWidth('full')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => view('filament.app.auth.login-register-link')->render(),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): string => Blade::render("@livewire('outlet-switcher')"),
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn () => view('filament.app.impersonation-banner'),
            )
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\Filament\App\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\Filament\App\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\Filament\App\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                ResolveTenant::class,
                EnsureTenantIsActive::class,
            ]);
    }
}
