<?php

namespace Tests\Feature;

use App\Enums\UserInvitationStatus;
use App\Filament\Resources\UserInvitationResource\Pages\ListUserInvitations;
use App\Models\Department;
use App\Models\User;
use App\Models\UserInvitation;
use App\Notifications\UghiResetPasswordNotification;
use App\Notifications\UserInvitationNotification;
use App\Services\UserInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountRecoveryAndInvitationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_forgot_password_response_does_not_disclose_unknown_emails(): void
    {
        Notification::fake();

        $this->get('/forgot-password')->assertOk()->assertSee('Forgot your password?');
        $this->post('/forgot-password', ['email' => 'unknown@example.org'])
            ->assertRedirect(route('password.email.sent'));
        $this->get(route('password.email.sent'))
            ->assertOk()
            ->assertSee('If an account exists for the email address you provided');

        Notification::assertNothingSent();
    }

    public function test_password_reset_updates_password_rotates_remember_token_and_unlocks_user(): void
    {
        $user = User::factory()->create([
            'password' => 'Old-Password!123',
            'status' => 'LOCKED',
            'is_active' => false,
            'locked_until' => now()->addMinutes(10),
            'failed_login_attempts' => 8,
            'remember_token' => 'old-remember-token',
        ]);
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'New-Password!456',
            'password_confirmation' => 'New-Password!456',
        ]);

        $response->assertRedirect(route('filament.admin.auth.login'))
            ->assertSessionHas('status', 'Your password has been updated successfully. You can now sign in.');
        $user->refresh();

        $this->assertTrue(Hash::check('New-Password!456', $user->password));
        $this->assertNotSame('old-remember-token', $user->remember_token);
        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
        $this->assertSame('ACTIVE', $user->status);
        $this->assertNotNull($user->password_changed_at);
    }

    public function test_existing_account_reset_link_is_sent_without_changing_unknown_email_response(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect(route('password.email.sent'));

        Notification::assertSentTo($user, UghiResetPasswordNotification::class);
    }

    public function test_invitation_acceptance_assigns_role_departments_and_email_verification_once(): void
    {
        $actor = User::where('email', 'admin@ughi.local')->firstOrFail();
        $department = Department::create(['code' => 'INVITE-PRIMARY', 'name' => 'Invitation Primary', 'slug' => 'invitation-primary']);
        $additionalDepartment = Department::create(['code' => 'INVITE-ADDITIONAL', 'name' => 'Invitation Additional', 'slug' => 'invitation-additional']);
        $role = Role::query()->where('name', 'Programme Officer')->firstOrFail();
        $token = 'secure-invitation-token-for-feature-test';
        $invitation = UserInvitation::query()->create([
            'first_name' => 'Amina',
            'last_name' => 'Example',
            'email' => 'amina.invited@example.org',
            'invited_by_user_id' => $actor->id,
            'role_id' => $role->id,
            'primary_department_id' => $department->id,
            'token_hash' => Hash::make($token),
            'status' => UserInvitationStatus::Pending,
            'expires_at' => now()->addHours(48),
        ]);
        $invitation->departments()->attach($additionalDepartment);

        $user = app(UserInvitationService::class)->accept($invitation, $token, 'Secure-Password!123');

        $this->assertTrue(Hash::check('Secure-Password!123', $user->password));
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue($user->hasRole('Programme Officer'));
        $this->assertTrue($user->departments()->whereKey($department->id)->wherePivot('is_primary', true)->exists());
        $this->assertTrue($user->departments()->whereKey($additionalDepartment->id)->wherePivot('is_primary', false)->exists());
        $this->assertSame(UserInvitationStatus::Accepted, $invitation->fresh()->status);
        $this->assertFalse(Hash::check($token, $invitation->fresh()->token_hash));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(UserInvitationService::class)->accept($invitation->fresh(), $token, 'Secure-Password!123');
    }

    public function test_invitation_token_is_stored_as_a_hash_and_expired_links_are_rejected(): void
    {
        $actor = User::where('email', 'admin@ughi.local')->firstOrFail();
        $department = Department::create(['code' => 'INVITE-EXP', 'name' => 'Invitation Expiry', 'slug' => 'invitation-expiry']);
        $invitation = UserInvitation::query()->create([
            'first_name' => 'Juma',
            'last_name' => 'Example',
            'email' => 'juma.invited@example.org',
            'invited_by_user_id' => $actor->id,
            'primary_department_id' => $department->id,
            'token_hash' => Hash::make('old-token'),
            'status' => UserInvitationStatus::Pending,
            'expires_at' => now()->subMinute(),
        ]);

        $this->get(route('invitations.accept.show', [$invitation, 'old-token']))
            ->assertOk()
            ->assertSee('This invitation is invalid, has expired, or has already been used.');
    }

    public function test_admin_invitation_service_rejects_duplicate_pending_invites(): void
    {
        Notification::fake();
        $actor = User::where('email', 'admin@ughi.local')->firstOrFail();
        $actor->givePermissionTo(['user.invite', 'user.assign_role', 'user.assign_department']);
        $department = Department::create(['code' => 'INVITE-DUP', 'name' => 'Invitation Duplicate', 'slug' => 'invitation-duplicate']);
        $role = Role::query()->where('name', 'Programme Officer')->firstOrFail();
        $data = [
            'first_name' => 'Mariam',
            'last_name' => 'Example',
            'email' => 'mariam.invited@example.org',
            'role_id' => $role->id,
            'primary_department_id' => $department->id,
            'department_ids' => [],
        ];

        [$invitation, $sent] = app(UserInvitationService::class)->invite($data, $actor);

        $this->assertTrue($sent);
        $this->assertNotSame('raw-token', $invitation->token_hash);
        $this->assertSame(UserInvitationStatus::Pending, $invitation->status);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(UserInvitationService::class)->invite($data, $actor);
    }

    public function test_invitation_management_page_renders_for_authorized_administrators(): void
    {
        $admin = User::where('email', 'admin@ughi.local')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(ListUserInvitations::class)
            ->assertSuccessful()
            ->assertSee('Invitations')
            ->assertSee('Invite User');
    }

    public function test_ughi_account_emails_render_branded_content_without_credentials(): void
    {
        $user = User::factory()->create(['first_name' => 'Neema']);
        $resetEmail = (new UghiResetPasswordNotification('reset-token'))
            ->toMail($user)
            ->render();

        $this->assertStringContainsString('Ustawi Global Health Initiative', $resetEmail);
        $this->assertStringContainsString('Reset Your UGHI Password', $resetEmail);
        $this->assertStringContainsString('never share the link', $resetEmail);
        $this->assertStringContainsString('reset-token', $resetEmail);

        $invitation = UserInvitation::query()->create([
            'first_name' => 'Neema',
            'last_name' => 'Invited',
            'email' => 'neema.invited@example.org',
            'invited_by_user_id' => User::where('email', 'admin@ughi.local')->value('id'),
            'token_hash' => Hash::make('invitation-token'),
            'status' => UserInvitationStatus::Pending,
            'expires_at' => now()->addHours(48),
        ])->load(['role', 'primaryDepartment', 'inviter']);

        $invitationEmail = (new UserInvitationNotification($invitation, 'invitation-token'))
            ->toMail($user)
            ->render();

        $this->assertStringContainsString('You Have Been Invited to UGHI', $invitationEmail);
        $this->assertStringContainsString('Accept Invitation', $invitationEmail);
        $this->assertStringContainsString('invitation-token', $invitationEmail);
        $this->assertNotSame('invitation-token', $invitation->token_hash);
        $this->assertTrue(Hash::check('invitation-token', $invitation->token_hash));
    }
}
