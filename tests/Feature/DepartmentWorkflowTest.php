<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\Department;
use App\Models\DepartmentLeadership;
use App\Models\DepartmentTask;
use App\Models\Programme;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use App\Services\DepartmentAccessService;
use App\Services\DepartmentLeadershipService;
use App\Services\DepartmentTaskService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DepartmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_can_belong_to_multiple_departments_and_primary_is_a_pivot_attribute(): void
    {
        $user = User::factory()->create();
        $departments = Department::take(2)->get();
        $user->departments()->attach($departments[0]->id, ['is_primary' => true, 'department_role' => 'Officer', 'active' => true]);
        $user->departments()->attach($departments[1]->id, ['is_primary' => false, 'department_role' => 'Volunteer', 'active' => true]);

        $this->assertCount(2, $user->fresh()->departments);
        $this->assertSame($departments[0]->id, $user->fresh()->primaryDepartment->first()->id);
    }

    public function test_primary_head_is_unique_but_multiple_non_head_appointments_are_allowed(): void
    {
        $department = Department::firstOrFail();
        $first = TeamMember::create(['department_id' => $department->id, 'name' => 'First Head', 'active' => true]);
        $second = TeamMember::create(['department_id' => $department->id, 'name' => 'Second Head', 'active' => true]);
        app(DepartmentLeadershipService::class)->appoint($department, $first, ['leadership_role' => 'HEAD', 'is_primary' => true, 'appointment_status' => 'ACTIVE', 'active' => true]);
        $this->expectException(ValidationException::class);
        app(DepartmentLeadershipService::class)->appoint($department, $second, ['leadership_role' => 'HEAD', 'is_primary' => true, 'appointment_status' => 'ACTIVE', 'active' => true]);
    }

    public function test_task_assignment_is_department_scoped_and_overdue_is_computed(): void
    {
        $department = Department::firstOrFail();
        $other = Department::whereKeyNot($department->id)->firstOrFail();
        $user = User::factory()->create();
        $user->departments()->attach($department, ['active' => true]);
        $user->givePermissionTo('department.assign_task');
        $member = TeamMember::create(['department_id' => $department->id, 'name' => 'Task recipient', 'active' => true]);
        $task = app(DepartmentTaskService::class)->assign($user, $department, ['title' => 'Late report', 'assigned_to_team_member_id' => $member->id, 'due_date' => today()->subDay(), 'priority' => 'HIGH', 'status' => 'PENDING']);
        $this->assertSame('OVERDUE', $task->effective_status);
        $this->assertDatabaseHas('department_tasks', ['id' => $task->id, 'status' => 'PENDING']);
        $this->assertFalse(app(DepartmentAccessService::class)->canAccessDepartment($user, $other));
        $completed = app(DepartmentTaskService::class)->complete($user, $task, 'Finished');
        $this->assertSame('COMPLETED', $completed->status);
        $this->assertSame('COMPLETED', $completed->effective_status);
    }

    public function test_task_assignment_rejects_staff_from_another_department(): void
    {
        $department = Department::firstOrFail();
        $other = Department::whereKeyNot($department->id)->firstOrFail();
        $user = User::factory()->create();
        $user->departments()->attach($department, ['active' => true]);
        $user->givePermissionTo('department.assign_task');
        $member = TeamMember::create(['department_id' => $other->id, 'name' => 'Other department staff', 'active' => true]);

        $this->expectException(ValidationException::class);
        app(DepartmentTaskService::class)->assign($user, $department, ['title' => 'Cross department task', 'assigned_to_team_member_id' => $member->id, 'priority' => 'MEDIUM', 'status' => 'PENDING']);
    }

    public function test_supporting_department_can_view_project_but_does_not_receive_edit_rights(): void
    {
        $lead = Department::where('code', 'PROG')->firstOrFail();
        $support = Department::where('code', 'MEL')->firstOrFail();
        $project = Project::create(['programme_id' => Programme::firstOrFail()->id, 'title' => ['en' => 'Scoped Project'], 'short_description' => ['en' => 'Description'], 'description' => ['en' => 'Full description'], 'status' => ProjectStatus::Active]);
        $project->departments()->attach($lead->id, ['is_lead' => true]);
        $project->departments()->attach($support->id, ['is_lead' => false]);
        $user = User::factory()->create();
        $user->departments()->attach($support->id, ['active' => true]);
        $user->givePermissionTo(['project.view', 'project.update']);
        $access = app(DepartmentAccessService::class);
        $this->assertTrue($access->canViewProject($user, $project));
        $this->assertFalse($access->canManageProject($user, $project));
        $this->assertFalse($access->canAccessDepartment($user, $lead));
    }

    public function test_approval_progression_return_reject_and_final_approval_preserve_action_history(): void
    {
        $department = Department::firstOrFail();
        $headUser = User::factory()->create();
        $headUser->givePermissionTo('department.approve');
        $headUser->departments()->attach($department, ['active' => true]);
        $member = TeamMember::create(['department_id' => $department->id, 'user_id' => $headUser->id, 'name' => 'Appointed Head', 'active' => true]);
        DepartmentLeadership::create(['department_id' => $department->id, 'team_member_id' => $member->id, 'leadership_role' => 'HEAD', 'is_primary' => true, 'appointment_status' => 'ACTIVE', 'can_approve' => true, 'active' => true]);
        $workflow = ApprovalWorkflow::create(['name' => 'Test Workflow', 'code' => 'TEST_APPROVAL', 'module' => 'test', 'active' => true]);
        $workflow->steps()->create(['step_order' => 1, 'name' => 'Head Review', 'approver_type' => 'DEPARTMENT_HEAD', 'required_leadership_role' => 'HEAD']);
        $workflow->steps()->create(['step_order' => 2, 'name' => 'Executive Review', 'approver_type' => 'EXECUTIVE_SECRETARY']);
        $subject = Project::create(['programme_id' => Programme::firstOrFail()->id, 'title' => ['en' => 'Approval Project'], 'short_description' => ['en' => 'Description'], 'description' => ['en' => 'Full description']]);
        $service = app(ApprovalWorkflowService::class);
        $request = $service->submit($workflow, $subject, $headUser, $department->id);
        $this->assertSame('UNDER_REVIEW', $request->status);
        $request = $service->act($request, $headUser, 'APPROVED');
        $this->assertSame(2, $request->current_step);
        $executive = User::factory()->create();
        $executive->givePermissionTo('organization.full_access');
        $request = $service->act($request, $executive, 'RETURNED', 'Please add the budget.');
        $this->assertSame('RETURNED', $request->status);
        $this->assertDatabaseCount('approval_actions', 3);

        $request->update(['status' => 'UNDER_REVIEW', 'current_step' => 1, 'completed_at' => null]);
        $request = $service->act($request, $headUser, 'APPROVED');
        $request = $service->act($request, $executive, 'APPROVED');
        $this->assertSame('APPROVED', $request->status);
        $this->assertSame(5, $request->actions()->count());
    }

    public function test_approval_uses_exact_leadership_role_and_advances_to_next_configured_step(): void
    {
        $department = Department::firstOrFail();
        $headUser = User::factory()->create();
        $managerUser = User::factory()->create();
        $headUser->givePermissionTo('department.approve');
        $managerUser->givePermissionTo('department.approve');
        foreach ([[$headUser, 'HEAD'], [$managerUser, 'MANAGER']] as [$user, $role]) {
            $member = TeamMember::create(['department_id' => $department->id, 'user_id' => $user->id, 'name' => $role.' Approver', 'active' => true]);
            DepartmentLeadership::create(['department_id' => $department->id, 'team_member_id' => $member->id, 'leadership_role' => $role, 'is_primary' => $role === 'HEAD', 'appointment_status' => 'ACTIVE', 'can_approve' => true, 'active' => true]);
        }

        $workflow = ApprovalWorkflow::create(['name' => 'Sparse Steps', 'code' => 'SPARSE_STEPS', 'module' => 'test', 'active' => true]);
        $workflow->steps()->create(['step_order' => 10, 'name' => 'Manager Review', 'approver_type' => 'DEPARTMENT_MANAGER']);
        $workflow->steps()->create(['step_order' => 30, 'name' => 'Executive Review', 'approver_type' => 'EXECUTIVE_SECRETARY']);
        $subject = Project::create(['programme_id' => Programme::firstOrFail()->id, 'title' => ['en' => 'Sparse Step Project'], 'short_description' => ['en' => 'Description'], 'description' => ['en' => 'Full description']]);
        $service = app(ApprovalWorkflowService::class);
        $request = $service->submit($workflow, $subject, User::factory()->create(), $department->id);

        $this->assertSame(10, $request->current_step);
        $this->assertFalse(app(DepartmentAccessService::class)->canApprove($headUser, $request));
        $this->assertTrue(app(DepartmentAccessService::class)->canApprove($managerUser, $request));
        $request = $service->act($request, $managerUser, 'APPROVED');
        $this->assertSame(30, $request->current_step);

        $executive = User::factory()->create();
        $executive->givePermissionTo('organization.full_access');
        $request = $service->act($request, $executive, 'APPROVED');
        $this->assertSame('APPROVED', $request->status);
    }

    public function test_department_work_pages_render_task_actions_and_dashboard_sections(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $department = Department::firstOrFail();
        $user = User::factory()->create(['must_change_password' => false]);
        $user->givePermissionTo(['admin.access', 'department.view', 'department.assign_task', 'organization.full_access']);
        $user->departments()->attach($department, ['active' => true]);
        $member = TeamMember::create(['department_id' => $department->id, 'user_id' => $user->id, 'name' => 'Assigned User', 'active' => true]);
        $task = DepartmentTask::create(['department_id' => $department->id, 'title' => 'An overdue task', 'assigned_by_user_id' => $user->id, 'assigned_to_user_id' => $user->id, 'assigned_to_team_member_id' => $member->id, 'priority' => 'HIGH', 'status' => 'PENDING', 'due_date' => today()->subDay()]);

        $this->actingAs($user)->get('/admin/my-work')->assertOk()->assertSee('Overdue tasks')->assertSee('Recent activity')->assertSee('An overdue task')->assertSee('Complete');
        $this->post(route('department-tasks.complete', $task), ['completion_note' => 'Completed by assignee'])->assertRedirect();
        $this->assertDatabaseHas('department_tasks', ['id' => $task->id, 'status' => 'COMPLETED', 'completion_note' => 'Completed by assignee']);
        $this->get('/admin/department-control-panel')->assertOk()->assertSee('Tasks by status')->assertSee('Projects by status')->assertSee('Task completion by month');
        $this->get('/admin/approval-requests')->assertOk()->assertSee('Pending')->assertSee('Returned')->assertSee('Completed');
    }

    public function test_my_work_approval_actions_progress_and_require_comments_for_return(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $department = Department::firstOrFail();
        $approver = User::factory()->create(['must_change_password' => false]);
        $approver->givePermissionTo(['admin.access', 'department.approve']);
        $approver->departments()->attach($department, ['active' => true]);
        $member = TeamMember::create(['department_id' => $department->id, 'user_id' => $approver->id, 'name' => 'My Work Reviewer', 'active' => true]);
        DepartmentLeadership::create(['department_id' => $department->id, 'team_member_id' => $member->id, 'leadership_role' => 'HEAD', 'is_primary' => true, 'appointment_status' => 'ACTIVE', 'can_approve' => true, 'active' => true]);

        $workflow = ApprovalWorkflow::create(['name' => 'My Work Approval', 'code' => 'MY_WORK_APPROVAL', 'module' => 'test', 'active' => true]);
        $workflow->steps()->create(['step_order' => 1, 'name' => 'Department Head', 'approver_type' => 'DEPARTMENT_HEAD', 'required_leadership_role' => 'HEAD']);
        $subject = Project::create(['programme_id' => Programme::firstOrFail()->id, 'title' => ['en' => 'My Work Approval Project'], 'short_description' => ['en' => 'Description'], 'description' => ['en' => 'Approval item']]);
        $request = app(ApprovalWorkflowService::class)->submit($workflow, $subject, User::factory()->create(), $department->id);

        $this->actingAs($approver)->get('/admin/my-work')->assertOk()->assertSee('Pending my approval')->assertSee('My Work Approval Project')->assertSee('Approve');
        $this->post(route('my-work.approval-action', $request), ['action' => 'RETURNED'])->assertSessionHasErrors('comment');
        $this->assertSame('UNDER_REVIEW', $request->fresh()->status);

        $this->post(route('my-work.approval-action', $request), ['action' => 'RETURNED', 'comment' => 'Please add supporting details.'])->assertRedirect();
        $this->assertSame('RETURNED', $request->fresh()->status);
        $this->assertDatabaseHas('approval_actions', ['approval_request_id' => $request->id, 'action' => 'RETURNED', 'comment' => 'Please add supporting details.']);
    }

    public function test_department_control_panel_shows_leadership_workload_projects_and_milestone_deadlines(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $department = Department::firstOrFail();
        $user = User::factory()->create(['must_change_password' => false]);
        $user->givePermissionTo(['admin.access', 'department.view', 'organization.full_access', 'department.manage_projects']);
        $user->departments()->attach($department, ['active' => true]);
        $member = TeamMember::create(['department_id' => $department->id, 'user_id' => $user->id, 'name' => 'Panel Department Head', 'active' => true]);
        DepartmentLeadership::create(['department_id' => $department->id, 'team_member_id' => $member->id, 'leadership_role' => 'HEAD', 'is_primary' => true, 'appointment_status' => 'ACTIVE', 'can_manage_projects' => true, 'active' => true]);
        $project = Project::create(['programme_id' => Programme::firstOrFail()->id, 'title' => ['en' => 'Panel active project'], 'short_description' => ['en' => 'Summary'], 'description' => ['en' => 'Description'], 'status' => ProjectStatus::Active]);
        $project->departments()->attach($department, ['is_lead' => true]);
        ProjectMilestone::create(['project_id' => $project->id, 'title' => 'Panel milestone deadline', 'due_date' => today()->addDays(4), 'status' => 'UPCOMING']);
        DepartmentTask::create(['department_id' => $department->id, 'title' => 'Panel workload task', 'assigned_by_user_id' => $user->id, 'assigned_to_team_member_id' => $member->id, 'priority' => 'MEDIUM', 'status' => 'PENDING', 'due_date' => today()->addDays(2)]);

        $this->actingAs($user)->get('/admin/department-control-panel?department='.$department->id)
            ->assertOk()
            ->assertSee('Panel Department Head')
            ->assertSee('Panel active project')
            ->assertSee('Panel workload task')
            ->assertSee('Staff workload')
            ->assertSee('Panel milestone deadline')
            ->assertSee('Department leadership')
            ->assertSee('Upcoming deadlines');
    }
}
