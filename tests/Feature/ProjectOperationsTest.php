<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Filament\Resources\ProjectResource\Pages\CreateProject;
use App\Models\Department;
use App\Models\Programme;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectMilestone;
use App\Models\ProjectRisk;
use App\Models\User;
use App\Services\DepartmentAccessService;
use App\Services\ProjectDepartmentService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ProjectOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_project_department_assignment_tracks_one_lead_and_supporting_departments(): void
    {
        $lead = Department::where('code', 'PROG')->firstOrFail();
        $support = Department::where('code', 'MEL')->firstOrFail();
        $user = User::factory()->create();
        $user->departments()->attach([$lead->id => ['active' => true], $support->id => ['active' => true]]);
        $user->givePermissionTo(['project.create', 'project.update', 'project.view', 'department.assign_project', 'department.manage_projects']);
        $project = Project::create(['programme_id' => Programme::firstOrFail()->id, 'title' => ['en' => 'Operational Project'], 'short_description' => ['en' => 'Summary'], 'description' => ['en' => 'Description'], 'status' => ProjectStatus::Active]);

        app(ProjectDepartmentService::class)->sync($project, $user, $lead->id, [$support->id]);

        $this->assertTrue((bool) $project->departments()->whereKey($lead->id)->firstOrFail()->pivot->is_lead);
        $this->assertFalse((bool) $project->departments()->whereKey($support->id)->firstOrFail()->pivot->is_lead);
        $this->assertTrue(app(DepartmentAccessService::class)->canManageProject($user, $project));
    }

    public function test_project_department_assignment_rejects_departments_outside_user_scope(): void
    {
        $lead = Department::where('code', 'PROG')->firstOrFail();
        $other = Department::where('code', 'MEL')->firstOrFail();
        $user = User::factory()->create();
        $user->departments()->attach($lead, ['active' => true]);
        $user->givePermissionTo(['project.create', 'department.assign_project']);
        $project = Project::create(['programme_id' => Programme::firstOrFail()->id, 'title' => ['en' => 'Scoped assignment'], 'short_description' => ['en' => 'Summary'], 'description' => ['en' => 'Description']]);

        $this->expectException(HttpException::class);
        app(ProjectDepartmentService::class)->sync($project, $user, $lead->id, [$other->id]);
    }

    public function test_project_operations_compute_risk_and_keep_activity_and_milestone_data(): void
    {
        $department = Department::firstOrFail();
        $user = User::factory()->create();
        $user->departments()->attach($department, ['active' => true]);
        $user->givePermissionTo(['project.view', 'project.update', 'department.manage_projects']);
        $project = Project::create(['programme_id' => Programme::firstOrFail()->id, 'title' => ['en' => 'Operations'], 'short_description' => ['en' => 'Summary'], 'description' => ['en' => 'Description']]);
        $project->departments()->attach($department, ['is_lead' => true]);

        $this->actingAs($user);
        $activity = ProjectActivity::create(['project_id' => $project->id, 'title' => 'Community training', 'status' => 'IN_PROGRESS', 'completion_percentage' => 40]);
        $milestone = ProjectMilestone::create(['project_id' => $project->id, 'title' => 'Baseline completed', 'due_date' => today()->addWeek(), 'status' => 'UPCOMING']);
        $risk = ProjectRisk::create(['project_id' => $project->id, 'title' => 'Delayed supplies', 'likelihood' => 4, 'impact' => 5, 'status' => 'OPEN']);

        $this->assertSame(40, $activity->fresh()->completion_percentage);
        $this->assertSame('UPCOMING', $milestone->fresh()->status);
        $this->assertSame('CRITICAL', $risk->fresh()->risk_level);
        $this->assertSame(1, $project->activities()->count());
        $this->assertSame(1, $project->milestones()->count());
        $this->assertSame(1, $project->risks()->count());
    }

    public function test_supporting_department_can_view_but_cannot_change_project_operations(): void
    {
        $lead = Department::where('code', 'PROG')->firstOrFail();
        $support = Department::where('code', 'MEL')->firstOrFail();
        $project = Project::create(['programme_id' => Programme::firstOrFail()->id, 'title' => ['en' => 'Scoped operations'], 'short_description' => ['en' => 'Summary'], 'description' => ['en' => 'Description']]);
        $project->departments()->attach($lead->id, ['is_lead' => true]);
        $project->departments()->attach($support->id, ['is_lead' => false]);
        $user = User::factory()->create();
        $user->departments()->attach($support->id, ['active' => true]);
        $user->givePermissionTo(['project.view', 'project.update']);
        $this->actingAs($user);

        $this->assertTrue(app(DepartmentAccessService::class)->canViewProject($user, $project));
        $this->assertFalse(app(DepartmentAccessService::class)->canManageProject($user, $project));
        $this->expectException(HttpException::class);
        ProjectActivity::create(['project_id' => $project->id, 'title' => 'Unauthorized change']);
    }

    public function test_project_admin_page_displays_operations_tabs(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $department = Department::firstOrFail();
        $user = User::factory()->create(['must_change_password' => false]);
        $user->givePermissionTo(['admin.access', 'project.view', 'project.update', 'department.manage_projects']);
        $user->departments()->attach($department, ['active' => true]);
        $project = Project::create(['programme_id' => Programme::firstOrFail()->id, 'title' => ['en' => 'Project operations screen'], 'short_description' => ['en' => 'Summary'], 'description' => ['en' => 'Description']]);
        $project->departments()->attach($department, ['is_lead' => true]);

        $this->actingAs($user)->get('/admin/projects/'.$project->slug)
            ->assertOk()
            ->assertSee('Activities')
            ->assertSee('Milestones')
            ->assertSee('Risks');
    }

    public function test_project_create_form_persists_lead_department_assignment(): void
    {
        $department = Department::firstOrFail();
        $user = User::factory()->create(['must_change_password' => false]);
        $user->givePermissionTo(['admin.access', 'project.create', 'department.assign_project', 'organization.full_access']);
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateProject::class)->fillForm([
            'programme_id' => Programme::firstOrFail()->id,
            'title' => ['en' => 'Assigned project'],
            'short_description' => ['en' => 'Project summary'],
            'description' => ['en' => 'Project details'],
            'lead_department_id' => $department->id,
            'supporting_department_ids' => [],
        ])->call('create')->assertHasNoFormErrors();

        $project = Project::where('title->en', 'Assigned project')->firstOrFail();
        $this->assertTrue((bool) $project->departments()->whereKey($department->id)->firstOrFail()->pivot->is_lead);
    }
}
