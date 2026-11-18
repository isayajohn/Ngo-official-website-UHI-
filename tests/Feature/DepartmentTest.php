<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\Position;
use App\Models\Programme;
use App\Models\Project;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_starter_departments_are_editable_and_hierarchical(): void
    {
        $this->assertDatabaseCount('departments', 9);
        $secretariat = Department::where('code', 'SEC')->firstOrFail();
        $this->assertCount(8, $secretariat->children);
        $secretariat->update(['name' => 'Secretariat Office']);
        $this->assertSame('Secretariat Office', $secretariat->fresh()->name);
    }

    public function test_department_cannot_be_its_own_parent(): void
    {
        $department = Department::create(['name' => 'Test Department', 'code' => 'TEST', 'active' => true]);
        $this->expectException(ValidationException::class);
        $department->update(['parent_id' => $department->id]);
    }

    public function test_positions_and_team_members_link_to_departments(): void
    {
        $department = Department::where('code', 'PROG')->firstOrFail();
        $director = Position::create(['department_id' => $department->id, 'title' => 'Test Director', 'active' => true]);
        $officer = Position::create(['department_id' => $department->id, 'title' => 'Test Officer', 'reports_to_position_id' => $director->id, 'active' => true]);
        $member = TeamMember::create(['department_id' => $department->id, 'position_id' => $officer->id, 'name' => 'Test Team Member', 'active' => true]);
        $this->assertTrue($department->teamMembers->contains($member));
        $this->assertSame($department->id, $member->position->department_id);
    }

    public function test_programme_and_project_department_pivots_preserve_lead_flag(): void
    {
        $department = Department::where('code', 'PROG')->firstOrFail();
        $programme = Programme::firstOrFail();
        $department->programmes()->attach($programme);
        $project = Project::create(['programme_id' => $programme->id, 'title' => ['en' => 'Test project'], 'short_description' => ['en' => 'Test'], 'description' => ['en' => 'Test'], 'status' => ProjectStatus::Active, 'is_published' => false]);
        $department->projects()->attach($project, ['is_lead' => true]);
        $this->assertTrue($programme->departments->contains($department));
        $this->assertTrue((bool) $department->projects()->first()->pivot->is_lead);
        $other = Department::where('code', 'MEL')->firstOrFail();
        $this->expectException(ValidationException::class);
        $other->projects()->attach($project, ['is_lead' => true]);
    }

    public function test_only_public_departments_are_listed_and_shown(): void
    {
        $visible = Department::where('code', 'PROG')->firstOrFail();
        $hidden = Department::create(['name' => 'Test Hidden Department', 'code' => 'HIDDEN', 'active' => false, 'show_on_website' => true]);
        $this->get('/about/departments')->assertOk()->assertSee($visible->name)->assertDontSee($hidden->name);
        $this->get('/about/departments/'.$visible->slug)->assertOk();
        $this->get('/about/departments/'.$hidden->slug)->assertNotFound();
    }

    public function test_leadership_page_uses_published_leader_profiles(): void
    {
        $department = Department::where('code', 'EXEC')->firstOrFail();
        TeamMember::create([
            'department_id' => $department->id,
            'name' => 'Public Executive Director',
            'leadership_role' => 'Executive Director',
            'is_leader' => true,
            'active' => true,
            'show_on_website' => true,
        ]);
        TeamMember::create([
            'department_id' => $department->id,
            'name' => 'Internal Staff Record',
            'is_leader' => false,
            'active' => true,
            'show_on_website' => true,
        ]);

        $this->get('/about/leadership')
            ->assertOk()
            ->assertSee('Public Executive Director')
            ->assertSee('Executive Director')
            ->assertDontSee('Internal Staff Record');
    }

    public function test_contact_message_routes_to_an_active_department(): void
    {
        $department = Department::where('code', 'COMMS')->firstOrFail();
        $this->post('/contact', ['name' => 'Test Visitor', 'email' => 'visitor@example.org', 'department_id' => $department->id, 'subject' => 'Test enquiry', 'message' => 'Hello UGHI'])->assertSessionHas('contact_success');
        $this->assertDatabaseHas('contact_messages', ['email' => 'visitor@example.org', 'department_id' => $department->id, 'status' => 'NEW']);
    }

    public function test_department_permissions_are_scoped(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->assignRole('Administrator');
        $this->assertTrue($admin->can('department.create'));
        $this->assertTrue($admin->can('department.assign_project'));
        $content = User::factory()->create(['must_change_password' => false]);
        $content->assignRole('Content Manager');
        $this->assertFalse($content->can('department.create'));
    }
}
