<?php

namespace App\Filament\Pages;

use App\Services\DepartmentActivity;
use App\Services\UserSessionManager;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class SessionManagement extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $title = 'Active sessions';
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.session-management';

    public string $password = '';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('profile')->label('Back to profile')->icon('heroicon-o-user-circle')->url(EditProfile::getUrl())];
    }

    protected function getViewData(): array
    {
        $manager = app(UserSessionManager::class);

        return [
            'sessionsAvailable' => $manager->available(),
            'sessions' => $manager->forUser(auth()->user(), request()->session()->getId()),
        ];
    }

    public function revokeSession(string $sessionId): void
    {
        $user = auth()->user();
        $revoked = app(UserSessionManager::class)->revokeOne($user, $sessionId, request()->session()->getId());

        if ($revoked) {
            DepartmentActivity::record(null, $user->getKey(), 'user.session_revoked', $user);
            Notification::make()->title('Session ended')->success()->send();

            return;
        }

        Notification::make()->title('Session could not be ended')->body('The current session cannot be ended here, or the session is no longer available.')->warning()->send();
    }

    public function revokeOtherSessions(): void
    {
        $this->validate(['password' => ['required', 'string']]);

        $user = auth()->user();
        if (! Hash::check($this->password, $user->getAuthPassword())) {
            $this->addError('password', 'The password did not match your account.');

            return;
        }

        $manager = app(UserSessionManager::class);
        if (! $manager->available()) {
            Notification::make()->title('Session management unavailable')->body('Configure the database session driver before using this control.')->warning()->send();

            return;
        }

        $count = $manager->revokeOthers($user, request()->session()->getId());
        $this->reset('password');
        DepartmentActivity::record(null, $user->getKey(), 'user.other_sessions_revoked', $user, ['count' => $count]);

        Notification::make()
            ->title($count ? 'Other sessions ended' : 'No other active sessions')
            ->body($count ? "Signed out {$count} other session(s). This session remains active." : 'Your current session is still active.')
            ->success()
            ->send();
    }
}
