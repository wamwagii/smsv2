<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectToOnboarding;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->passwordReset()
            ->emailVerification()
            ->emailChangeVerification()
            ->profile()
            ->colors([
    'primary' => Color::Amber,
])
            ->brandName(config('school.name'))
            ->sidebarCollapsibleOnDesktop()
            ->unsavedChangesAlerts()
            ->databaseNotifications()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->widgets([
                AccountWidget::class,
            ])
            ->userMenuItems([
                // Group 1 — quick actions
                [
                    Action::make('sendSms')
                        ->label('Send SMS')
                        ->url(fn() => \App\Filament\Pages\SendSms::getUrl())
                        ->icon('heroicon-o-paper-airplane'),

                    Action::make('smsHistory')
                        ->label('SMS History')
                        ->url(fn() => \App\Filament\Resources\SmsBatches\SmsBatchResource::getUrl())
                        ->icon('heroicon-o-chat-bubble-left-right'),

                    Action::make('attendances')
                        ->label('Attendance')
                        ->url(fn() => \App\Filament\Resources\Attendances\AttendanceResource::getUrl())
                        ->icon('heroicon-o-calendar'),
                ],

                // Group 2 — external links
                [
                    Action::make('documentation')
                        ->label('Documentation')
                        ->url('https://filamentphp.com/docs')
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-book-open'),

                    Action::make('support')
                        ->label('Contact support')
                        ->url('mailto:' . config('school.email'))
                        ->icon('heroicon-o-lifebuoy'),

                    'logout' => fn (Action $action) => $action->label('Logout'),
                ],
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
                //RedirectToOnboarding::class,   // ← added — runs after Authenticate
            ])
            ->multiFactorAuthentication([
                AppAuthentication::make()
                    ->recoverable()
                    ->brandName(config('school.name')),
            ], isRequired: false);   // ← was true — changed to optional
    }
}