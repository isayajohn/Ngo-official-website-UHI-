<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\DepartmentTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_organization_overview_requires_organization_wide_access(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->givePermissionTo('admin.access');

        $this->actingAs($user)->get('/admin/organization-dashboard')->assertForbidden();
    }

    public function test_executive_overview_shows_operational_sections_and_department_filter(): void
    {
        $department = Department::where('code', 'PROG')->firstOrFail();
        $anotherDepartment = Department::where('code', 'MEL')->firstOrFail();
        $user = User::factory()->create(['must_change_password' => false]);
        $user->givePermissionTo(['admin.access', 'organization.full_access']);
        DepartmentTask::create([
            'department_id' => $department->id,
            'title' => 'Prepare quarterly report',
            'assigned_by_user_id' => $user->id,
            'priority' => 'HIGH',
            'status' => 'PENDING',
            'due_date' => today()->addDays(5),
        ]);

        $this->actingAs($user)->get('/admin/organization-dashboard')
            ->assertOk()
            ->assertSee('Department performance')
            ->assertSee('Projects by status')
            ->assertSee('Tasks by status')
            ->assertSee('Task completion · last 6 months')
            ->assertSee('Approvals awaiting action')
            ->assertSee('Upcoming deadlines')
            ->assertSee('Recent department activity')
            ->assertSee($anotherDepartment->name);

        $this->get('/admin/organization-dashboard?department='.$department->id)
            ->assertOk()
            ->assertSee('Prepare quarterly report')
            ->assertSee($department->name);
    }
}
