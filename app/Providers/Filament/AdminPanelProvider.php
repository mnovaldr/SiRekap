<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <style>
                        ::-webkit-scrollbar { width: 5px; height: 5px; }
                        ::-webkit-scrollbar-track { background: transparent; }
                        ::-webkit-scrollbar-thumb { background: rgba(203, 213, 225, 0.5); border-radius: 10px; }
                        ::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.8); }
                        .dark ::-webkit-scrollbar-thumb { background: rgba(51, 65, 85, 0.5); }
                        .dark ::-webkit-scrollbar-thumb:hover { background: rgba(71, 85, 105, 0.8); }
                    </style>
                '),
            )
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->favicon(asset('favicon.png'))
            ->colors([
                'primary' => Color::hex('#4f46e5'), // Indigo
                'danger' => Color::hex('#e63946'),  
                'success' => Color::hex('#0d9488'), // Teal
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\DuplicateAlert::class,
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\EventsChart::class,
                \App\Filament\Widgets\BidangDistributionChart::class,
                \App\Filament\Widgets\KomersialChart::class,
                \App\Filament\Widgets\TopJenisKegiatanChart::class,
                \App\Filament\Widgets\TopOrganisasiWidget::class,
                \App\Filament\Widgets\TopLokasiWidget::class,
                \App\Filament\Widgets\RekapBulananWidget::class,
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
            ])
            ->brandName('SiREKAP MCC')
            ->brandLogo(asset('images/logo.png'))
            ->darkModeBrandLogo(asset('images/logo-white.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('favicon.ico'))
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications();
    }
}
