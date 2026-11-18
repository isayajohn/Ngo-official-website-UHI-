<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Department;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class UserAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_user_detail_page_shows_account_tabs_for_authorized_administrator(): void
    {
        $admin = User::where('email', 'admin@ughi.local')->firstOrFail();
        $this->actingAs($admin);
        $record = User::factory()->create(['name' => 'Samira Example', 'must_change_password' => false]);

        Livewire::test(ViewUser::class, ['record' => $record->getRouteKey()])
            ->assertSuccessful()
            ->assertSee('User account details')
            ->assertSee('Overview')
            ->assertSee('Access')
            ->assertSee('Departments')
            ->assertSee('Activity')
            ->assertSee('Security')
            ->assertSee('Samira Example');
    }

    public function test_users_table_view_action_opens_the_read_only_account_popup(): void
    {
        $admin = User::where('email', 'admin@ughi.local')->firstOrFail();
        $this->actingAs($admin);
        $record = User::factory()->create(['name' => 'Nuru Popup', 'must_change_password' => false]);

        Livewire::test(ListUsers::class)
            ->assertTableActionExists('view', fn ($action) => $action->hasModal(), (string) $record->getKey())
            ->mountTableAction('view', (string) $record->getKey())
            ->assertSet('mountedActions.0.name', 'view');
    }

    public function test_user_edit_action_is_a_multi_step_popup_and_saves_the_account(): void
    {
        $admin = User::where('email', 'admin@ughi.local')->firstOrFail();
        $admin->givePermissionTo(['user.assign_department', 'user.assign_role', 'user.activate']);
        $this->actingAs($admin);
        $department = Department::create(['code' => 'EDIT-TEST', 'name' => 'Edit Test Department', 'slug' => 'edit-test-department']);
        $record = User::factory()->create([
            'first_name' => 'Nuru',
            'last_name' => 'Before',
            'name' => 'Nuru Before',
            'must_change_password' => false,
        ]);

        Livewire::test(ListUsers::class)
            ->assertTableActionExists('edit', fn ($action) => $action->hasModal() && $action->isWizard(), (string) $record->getKey())
            ->mountTableAction('edit', (string) $record->getKey())
            ->assertSet('mountedActions.0.name', 'edit')
            ->fillForm([
                'first_name' => 'Nuru',
                'middle_name' => null,
                'last_name' => 'Updated',
                'username' => null,
                'email' => $record->email,
                'phone' => null,
                'team_member_id' => null,
                'primary_department_id' => $department->id,
                'additional_department_ids' => [],
                'status' => 'ACTIVE',
                'must_change_password' => false,
                'locale' => 'en',
                'timezone' => 'Africa/Dar_es_Salaam',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertSame('Nuru Updated', $record->fresh()->name);
        $this->assertTrue($record->departments()->where('departments.id', $department->id)->wherePivot('is_primary', true)->exists());
    }

    public function test_login_and_failed_login_events_update_account_security_history(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->givePermissionTo('admin.access');
        request()->server->set('REMOTE_ADDR', '192.0.2.20');

        Event::dispatch(new Login('web', $user, false));
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertSame('192.0.2.20', $user->fresh()->last_login_ip);

        for ($i = 0; $i < 8; $i++) {
            Event::dispatch(new Failed('web', $user->fresh(), ['email' => $user->email]));
        }

        $user->refresh();
        $this->assertSame('LOCKED', $user->status);
        $this->assertFalse($user->is_active);
        $this->assertNotNull($user->locked_until);
        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));

        $this->travel(16)->minutes();
        $this->assertTrue($user->fresh()->canAccessPanel(Filament::getPanel('admin')));
        $this->assertSame('ACTIVE', $user->fresh()->status);
    }

    public function test_user_accounts_are_not_deleted_from_the_admin_resource(): void
    {
        $admin = User::where('email', 'admin@ughi.local')->firstOrFail();
        $target = User::factory()->create();

        $this->assertFalse($admin->can('delete', $target));
    }

    public function test_admin_password_reset_sends_a_link_without_setting_a_password(): void
    {
        Notification::fake();
        $admin = User::where('email', 'admin@ughi.local')->firstOrFail();
        $target = User::factory()->create([
            'password' => 'Existing-Password!123',
            'must_change_password' => false,
        ]);
        $existingPasswordHash = $target->password;

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->mountTableAction('sendPasswordReset', (string) $target->getKey())
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertSame($existingPasswordHash, $target->fresh()->password);
        Notification::assertSentTo($target, \App\Notifications\UghiResetPasswordNotification::class);
    }
}
