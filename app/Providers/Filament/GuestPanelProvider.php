<?php

namespace App\Providers\Filament;

// use App\Filament\Pages\PublicTransaction;
use App\Filament\Guest\Pages\DashboardQueue;
use App\Filament\Guest\Pages\PublicTransaction;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Hasnayeen\Themes\ThemesPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Guest\Pages\PublicFeedback;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class GuestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('guest')
            ->path('')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Guest/Resources'), for: 'App\\Filament\\Guest\\Resources')
            ->discoverPages(in: app_path('Filament/Guest/Pages'), for: 'App\\Filament\\Guest\\Pages')
            ->pages([
                // PublicTransaction::class,
                // PublicFeedback::route('/f/{uuid}'),   // ✅ dynamic param here
            ])
            // ->viteTheme('resources\css\filament\guest\theme.css')
            ->routes(function (Panel $panel) {   // ✅ accept Panel, not Router
                Route::get('/f/{uuid}', PublicFeedback::class)
                    ->name('filament.' . $panel->getId() . '.feedback.public'); // => filament.guest.feedback.public
            })
            ->discoverWidgets(in: app_path('Filament/Guest/Widgets'), for: 'App\\Filament\\Guest\\Widgets')
            ->widgets([
                Widgets\FilamentInfoWidget::class,
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
            ->authMiddleware([])
            ->navigation(false)
            ->topbar(false)
            ->darkMode(false)
            ;

    }
}
