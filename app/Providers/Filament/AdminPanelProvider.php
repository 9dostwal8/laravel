<?php

namespace App\Providers\Filament;

use Filament\Pages\Dashboard;
use App\Filament\Auth\Login;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Backups;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticationPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->profile(EditProfile::class)
            ->passwordReset()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //
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
            ->sidebarCollapsibleOnDesktop()
            //->viteTheme('resources/css/filament/admin/theme.css')
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentSpatieLaravelBackupPlugin::make()
                    ->usingPage(Backups::class)
                    ->noTimeout(),
                TwoFactorAuthenticationPlugin::make()
                    ->enableTwoFactorAuthentication() // Enable Google 2FA
                   // ->enablePasskeyAuthentication() // Enable Passkey
                 //   ->addTwoFactorMenuItem() // Add 2FA menu item
                    ->forceTwoFactorSetup(),
                     // Force 2FA setup

            ])->unsavedChangesAlerts();

        if (str_contains(request()->url(), 'admin/login')) {
            $panel
                ->brandLogo(fn () => view('filament.admin.logo'))
                ->brandLogoHeight('6rem');
        }

        // TODO Had to wrap the font setting in a view composer since normally not able to get current locale - it returns default
        view()->composer('*', function () use ($panel) {
            $panel->brandName(trans('panel.brand_name'));

            if ($this->app->getLocale() !== 'en') {
                $panel
                    ->font(
                        'speda',
                        url: Vite::asset('resources/css/fonts.css'),
//                        provider: LocalFontProvider::class,
                    );
            }
        });

        return $panel;
    }
}
