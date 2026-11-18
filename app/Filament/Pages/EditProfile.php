<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\DepartmentActivity;
use App\Support\PasswordRules;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    protected bool $passwordWasChanged = false;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (blank($data['first_name'] ?? null) || blank($data['last_name'] ?? null)) {
            $parts = preg_split('/\s+/', trim((string) ($data['name'] ?? '')), 2) ?: [];
            $data['first_name'] = $data['first_name'] ?? ($parts[0] ?? '');
            $data['last_name'] = $data['last_name'] ?? ($parts[1] ?? '');
        }

        return $data;
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->required(fn () => $this->getUser()->must_change_password)
            ->rule(PasswordRules::strength())
            ->different('currentPassword')
            ->helperText('Use at least 12 characters, upper and lower case, a number and a symbol.');
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('sessions')->label('Manage sessions')->icon('heroicon-o-computer-desktop')->url(SessionManagement::getUrl())];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Personal details')->description('Update your contact details and profile photo.')->schema([
                TextInput::make('first_name')->required()->maxLength(100),
                TextInput::make('middle_name')->maxLength(100),
                TextInput::make('last_name')->required()->maxLength(100),
                TextInput::make('phone')->tel()->maxLength(50),
                FileUpload::make('profile_photo')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->disk('public')->directory('user-profiles')->visibility('public')->maxSize(2048)
                    ->placeholder('Click the button below to upload<br><span class="filepond--label-action">Browse file</span>')->helperText('JPEG, PNG or WebP. Maximum file size: 2 MB.'),
                TextInput::make('email')->label('Email address')->disabled()->dehydrated(false),
            ])->columns(2),
            Section::make('Preferences')->schema([
                Select::make('locale')->options(['en' => 'English', 'sw' => 'Kiswahili'])->required(),
                Select::make('timezone')->options(['Africa/Dar_es_Salaam' => 'Africa / Dar es Salaam', 'UTC' => 'UTC', 'Africa/Nairobi' => 'Africa / Nairobi', 'Africa/Kampala' => 'Africa / Kampala'])->required(),
                CheckboxList::make('notification_preferences')->label('Notifications to receive')->options([
                    'tasks' => 'Task assignments and deadlines',
                    'approvals' => 'Approval requests and decisions',
                    'account_security' => 'Account security alerts',
                ])->columns(1)->columnSpanFull(),
            ])->columns(2),
            Section::make('Change password')->description('Leave blank to keep your current password.')->schema([
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ])->columns(2),
        ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->passwordWasChanged = filled($data['password'] ?? null);
        if ($this->passwordWasChanged) {
            $data['must_change_password'] = false;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->passwordWasChanged) {
            $count = app(\App\Services\UserSessionManager::class)->revokeOthers($this->getUser(), request()->session()->getId());
            DepartmentActivity::record(null, $this->getUser()->getKey(), 'user.other_sessions_revoked_after_password_change', $this->getUser(), ['count' => $count]);
        }

        DepartmentActivity::record(null, $this->getUser()->getKey(), $this->passwordWasChanged ? 'user.profile_and_password_updated' : 'user.profile_updated', $this->getUser());
    }

    protected function getRedirectUrl(): ?string
    {
        return Filament::getUrl();
    }
}
