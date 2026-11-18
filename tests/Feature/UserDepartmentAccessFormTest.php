<?php

namespace Tests\Feature;

use App\Filament\Resources\UserInvitationResource\Pages\ListUserInvitations;
use App\Filament\Resources\UserResource;
use App\Models\Department;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Livewire\Livewire;
use Tests\TestCase;

class UserDepartmentAccessFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_user_accounts_are_created_through_invitations(): void
    {
        $this->assertFalse(UserResource::canCreate());
    }

    public function test_invitation_modal_saves_role_and_department_until_the_user_accepts(): void
    {
        Notification::fake();
        $admin = User::where('email', 'admin@ughi.local')->firstOrFail();
        $admin->givePermissionTo(['admin.access', 'user.invite', 'user.assign_role', 'user.assign_department']);
        $this->actingAs($admin);
        $department = Department::create(['code' => 'HR-TEST', 'name' => 'Human Resources Test', 'slug' => 'human-resources-test']);
        $role = Role::query()->where('name', 'Programme Officer')->firstOrFail();

        Livewire::test(ListUserInvitations::class)
            ->mountTableAction('invite')
            ->fillForm([
                'first_name' => 'Asha', 'last_name' => 'Test', 'email' => 'asha.test@example.org',
                'role_id' => $role->id,
                'primary_department_id' => $department->id, 'department_ids' => [],
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $invitation = \App\Models\UserInvitation::where('email', 'asha.test@example.org')->firstOrFail();
        $this->assertSame('Programme Officer', $invitation->role->name);
        $this->assertSame($department->id, $invitation->primary_department_id);
        $this->assertDatabaseMissing('users', ['email' => 'asha.test@example.org']);
    }
}
