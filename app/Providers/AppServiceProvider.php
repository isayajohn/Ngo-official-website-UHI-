<?php

namespace App\Providers;

use App\Models\Country;
use App\Models\ContentPage;
use App\Models\District;
use App\Models\Department;
use App\Models\DepartmentTask;
use App\Models\DepartmentLeadership;
use App\Models\FinancialYear;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\Position;
use App\Models\TeamMember;
use App\Models\ContactMessage;
use App\Models\ImpactStatistic;
use App\Models\EditorialItem;
use App\Models\InvolvementApplication;
use App\Models\MediaAsset;
use App\Models\Partner;
use App\Models\Programme;
use App\Models\Project;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceTarget;
use App\Models\PerformanceResult;
use App\Models\PerformanceRatingScale;
use App\Models\ReportingPeriod;
use App\Models\ResultsFrameworkItem;
use App\Models\Region;
use App\Models\User;
use App\Policies\ImpactStatisticPolicy;
use App\Policies\EditorialItemPolicy;
use App\Policies\InvolvementApplicationPolicy;
use App\Policies\ContentPagePolicy;
use App\Policies\MediaAssetPolicy;
use App\Policies\PartnerPolicy;
use App\Policies\LocationPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DepartmentTaskPolicy;
use App\Policies\DepartmentLeadershipPolicy;
use App\Policies\FinancialYearPolicy;
use App\Policies\AccountPolicy;
use App\Policies\BankAccountPolicy;
use App\Policies\CashAccountPolicy;
use App\Policies\PositionPolicy;
use App\Policies\TeamMemberPolicy;
use App\Policies\ContactMessagePolicy;
use App\Policies\ProgrammePolicy;
use App\Policies\ProjectPolicy;
use App\Policies\PerformanceIndicatorPolicy;
use App\Policies\PerformanceTargetPolicy;
use App\Policies\PerformanceResultPolicy;
use App\Policies\PerformanceRatingScalePolicy;
use App\Policies\ReportingPeriodPolicy;
use App\Policies\ResultsFrameworkItemPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(SiteSettings::class);
    }

    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                User::whereKey($event->user->getKey())->update([
                    'last_login_at' => now(),
                    'last_login_ip' => request()->ip(),
                    'failed_login_attempts' => 0,
                ]);
                \App\Services\DepartmentActivity::record(null, $event->user->getKey(), 'user.login', $event->user, ['ip' => request()->ip()]);
            }
        });

        Event::listen(Failed::class, function (Failed $event): void {
            if (! $event->user instanceof User) return;
            $user = $event->user;
            $attempts = (int) $user->failed_login_attempts + 1;
            $updates = ['failed_login_attempts' => $attempts];
            if ($attempts >= (int) config('auth.user_lockout_attempts', 8) && $user->status === 'ACTIVE') {
                $updates['status'] = 'LOCKED';
                $updates['is_active'] = false;
                $updates['locked_until'] = now()->addMinutes((int) config('auth.user_lockout_minutes', 15));
            }
            User::whereKey($user->getKey())->update($updates);
            if (($updates['status'] ?? null) === 'LOCKED') {
                $count = app(\App\Services\UserSessionManager::class)->revokeAll($user);
                \App\Services\DepartmentActivity::record(null, null, 'user.sessions_revoked_after_lockout', $user, ['count' => $count]);
            }
        });

        Event::listen(PasswordReset::class, function (PasswordReset $event): void {
            if ($event->user instanceof User) {
                $count = app(\App\Services\UserSessionManager::class)->revokeAll($event->user);
                \App\Services\DepartmentActivity::record(null, $event->user->getKey(), 'user.sessions_revoked_after_password_reset', $event->user, ['count' => $count]);
                \App\Services\DepartmentActivity::record(null, $event->user->getKey(), 'user.password_reset', $event->user);
            }
        });

        Event::listen(PasswordResetLinkSent::class, function (PasswordResetLinkSent $event): void {
            if ($event->user instanceof User) {
                \App\Services\DepartmentActivity::record(null, $event->user->getKey(), 'user.password_reset_requested', $event->user);
            }
        });

        Gate::policy(Programme::class, ProgrammePolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(PerformanceIndicator::class, PerformanceIndicatorPolicy::class);
        Gate::policy(PerformanceTarget::class, PerformanceTargetPolicy::class);
        Gate::policy(PerformanceResult::class, PerformanceResultPolicy::class);
        Gate::policy(PerformanceRatingScale::class, PerformanceRatingScalePolicy::class);
        Gate::policy(ReportingPeriod::class, ReportingPeriodPolicy::class);
        Gate::policy(ResultsFrameworkItem::class, ResultsFrameworkItemPolicy::class);
        Gate::policy(ImpactStatistic::class, ImpactStatisticPolicy::class);
        Gate::policy(EditorialItem::class, EditorialItemPolicy::class);
        Gate::policy(InvolvementApplication::class, InvolvementApplicationPolicy::class);
        Gate::policy(ContentPage::class, ContentPagePolicy::class);
        Gate::policy(Partner::class, PartnerPolicy::class);
        Gate::policy(MediaAsset::class, MediaAssetPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(DepartmentTask::class, DepartmentTaskPolicy::class);
        Gate::policy(DepartmentLeadership::class, DepartmentLeadershipPolicy::class);
        Gate::policy(FinancialYear::class, FinancialYearPolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(BankAccount::class, BankAccountPolicy::class);
        Gate::policy(CashAccount::class, CashAccountPolicy::class);
        Gate::policy(Position::class, PositionPolicy::class);
        Gate::policy(TeamMember::class, TeamMemberPolicy::class);
        Gate::policy(ContactMessage::class, ContactMessagePolicy::class);
        foreach ([Country::class, Region::class, District::class] as $model) {
            Gate::policy($model, LocationPolicy::class);
        }
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        View::composer(['components.*', 'pages.*'], function ($view) {
            $view->with('site', app(SiteSettings::class));
        });
    }
}
