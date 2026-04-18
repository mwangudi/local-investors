<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Plugins\DisableHtmlValidation;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
            ->domain(null)
            ->homeUrl('/admin/dashboard')
            ->authGuard('web')
            ->login()
            ->brandLogo(asset('images/logo.png'))
            ->favicon(asset('images/logo-mini.png'))
            ->brandLogoHeight('4rem')
            ->maxContentWidth('full')
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Menus',
                'Chama Finance',
                'Settings',
            ])
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Amber,
                'success' => Color::Green,
                'warning' => Color::Orange,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn(): string => \Illuminate\Support\Facades\Blade::render('@vite(\'resources/css/filament/admin/custom.css\')'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
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
            ->plugins([
                DisableHtmlValidation::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
