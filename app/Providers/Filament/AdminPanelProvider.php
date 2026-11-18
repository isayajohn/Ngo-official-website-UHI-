<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\EditProfile;
use App\Filament\Pages\Homepage;
use App\Filament\Pages\PolicySettings;
use App\Filament\Pages\Settings;
use App\Filament\Pages\DepartmentControlPanel;
use App\Filament\Pages\OrganizationDashboard;
use App\Filament\Pages\MyWork;
use App\Filament\Pages\FinanceDashboard;
use App\Filament\Pages\PerformanceDashboard;
use App\Filament\Pages\MelDashboard;
use App\Filament\Pages\SessionManagement;
use App\Http\Middleware\EnsurePasswordChanged;
use Filament\FontProviders\LocalFontProvider;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
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
        CreateAction::configureUsing(fn (CreateAction $action) => $action
            ->modal()
            ->modalWidth(Width::SevenExtraLarge)
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->closeModalByClickingAway(false));

        DatePicker::configureUsing(fn (DatePicker $datePicker) => $datePicker
            ->native(false)
            ->prefixIcon('heroicon-o-calendar-days')
            ->prefixIconColor('primary'));

        return $panel->default()->id('admin')->path('admin')->login()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->profile(EditProfile::class, isSimple: false)
            ->brandName('UHI · Ustawi Health Initiative')
            ->brandLogo(asset('images/ughi-logo.svg'))
            ->brandLogoHeight('2.5rem')
            ->colors(['primary' => Color::hex('#0870BD')])
            ->maxContentWidth('full')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18.5rem')
            ->collapsedSidebarWidth('4.75rem')
            ->font('Inter', url: asset('fonts/inter.css'), provider: LocalFontProvider::class)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([Dashboard::class, Homepage::class, Settings::class, PolicySettings::class, DepartmentControlPanel::class, OrganizationDashboard::class, MyWork::class, FinanceDashboard::class, PerformanceDashboard::class, MelDashboard::class, SessionManagement::class])
            ->navigationGroups(['Content', 'Our Work', 'Organization', 'Finance', 'Monitoring & Evaluation', 'Reports & Performance', 'System'])
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.admin-sidebar-style'))
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn () => view('filament.auth-login-link'))
            ->middleware([
                EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class,
                AuthenticateSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class,
                SubstituteBindings::class, DisableBladeIconComponents::class, DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class, EnsurePasswordChanged::class], isPersistent: true);
    }
}
