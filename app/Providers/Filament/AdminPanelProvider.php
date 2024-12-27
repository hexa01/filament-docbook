<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\ChangePassword;
use App\Filament\Pages\EditPassword;
use App\Filament\Pages\EditProfile;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
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
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            // ->globalSearch()
            

            ->default()
            ->id('admin')
            ->path('admin')
            ->profile(EditProfile::class)
            ->login()
            ->registration(Register::class)
            ->colors([
                'primary' => Color::Amber,
                'yellow' => Color::Yellow,
                'action' => Color::Purple,
                'blue' => Color::Blue,
                'viewButton' => Color::Teal,
            ])
            ->favicon(asset('image/favicon.png'))
            ->userMenuItems([
            MenuItem::make()
                ->label('Change Password')
                ->url(fn (): string => EditProfile::getUrl(['mode' => 'password'])) // Open Edit Profile page with password mode
                ->icon('heroicon-o-lock-closed'),

            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
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
