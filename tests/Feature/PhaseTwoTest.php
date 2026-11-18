<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Filament\Resources\CountryResource\Pages\CreateCountry;
use App\Filament\Resources\DistrictResource\Pages\CreateDistrict;
use App\Filament\Resources\ImpactStatisticResource\Pages\CreateImpactStatistic;
use App\Filament\Resources\ImpactStatisticResource\Pages\EditImpactStatistic;
use App\Filament\Resources\ProjectResource\Pages\CreateProject;
use App\Filament\Resources\ProjectResource\Pages\EditProject;
use App\Filament\Resources\RegionResource\Pages\CreateRegion;
use App\Filament\Widgets\ImpactByProgramme;
use App\Filament\Widgets\ProjectsByStatus;
use App\Models\Country;
use App\Models\District;
use App\Models\Department;
use App\Models\DepartmentLeadership;
use App\Models\ImpactStatistic;
use App\Models\Programme;
use App\Models\Project;
use App\Models\Region;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Models\User;
use Database\Seeders\PhaseTwoSeeder;
use Database\Seeders\ProgrammeSeeder;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::withoutLazyLoading();
    }

    private function user(string $role = 'Super Admin'): User
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->assignRole($role);

        return $user;
    }

    private function project(array $overrides = []): Project
    {
        return Project::create(array_replace([
            'programme_id' => Programme::first()->id, 'region_id' => Region::first()->id, 'district_id' => District::first()->id,
            'title' => ['en' => 'TEST ONLY — Community care'], 'short_description' => ['en' => 'Synthetic project for automated tests.'],
            'description' => ['en' => 'Test overview.'], 'status' => ProjectStatus::Active, 'start_date' => '2026-01-01', 'is_published' => true,
        ], $overrides));
    }

    private function statistic(array $overrides = [], bool $published = true): ImpactStatistic
    {
        $stat = ImpactStatistic::create(array_replace([
            'metric' => 'people_reached', 'label' => ['en' => 'TEST ONLY reach'], 'value' => 1234,
            'as_of_date' => today()->subDay(), 'verification_source' => 'INTERNAL TEST EVIDENCE', 'is_active' => false,
        ], $overrides));
        if ($published) {
            $stat->verify($this->user());
            $stat->update(['is_active' => true]);
        }

        return $stat;
    }

    public function test_seeds_are_safe_idempotent_and_preserve_archived_locations(): void
    {
        $this->assertDatabaseCount('countries', 1);
        $this->assertDatabaseCount('regions', 1);
        $this->assertDatabaseCount('districts', 1);
        $this->assertDatabaseCount('projects', 0);
        $this->assertSame(0, ImpactStatistic::published()->count());
        Region::first()->delete();
        $this->seed(PhaseTwoSeeder::class);
        $this->assertDatabaseCount('regions', 1);
        $this->assertSame(0, Region::published()->count());
        $this->assertSame(0, District::published()->count());
        $this->assertSame(5, ImpactStatistic::count());
    }

    public function test_empty_public_modules_render_without_fake_figures(): void
    {
        $this->get('/projects')->assertOk()->assertSee('No projects to display yet');
        $this->get('/impact')->assertOk()->assertSee('Verified impact updates are on the way')->assertDontSee('TEST ONLY reach');
        $this->get('/where-we-work')->assertOk()->assertSee('Tanzania')->assertSee('Ruvuma')->assertSee('Namtumbo');
    }

    public function test_only_published_projects_are_visible_and_scheduled_content_waits(): void
    {
        $live = $this->project();
        $draft = $this->project(['title' => ['en' => 'PRIVATE DRAFT'], 'is_published' => false]);
        $scheduled = $this->project(['title' => ['en' => 'PRIVATE SCHEDULED'], 'published_at' => now()->addDay()]);
        $this->get('/projects')->assertSee('TEST ONLY')->assertDontSee('PRIVATE DRAFT')->assertDontSee('PRIVATE SCHEDULED');
        $this->get('/projects/'.$live->slug)->assertOk();
        $this->get('/projects/'.$draft->slug)->assertNotFound();
        $this->get('/projects/'.$scheduled->slug)->assertNotFound();
        $this->travel(2)->days();
        $this->get('/projects/'.$scheduled->slug)->assertOk();
    }

    public function test_project_filter_search_programme_status_region_and_year(): void
    {
        $target = $this->project(['title' => ['en' => 'TEST ONLY — Maternal outreach']]);
        $other = $this->project(['title' => ['en' => 'OTHER PROJECT'], 'programme_id' => Programme::skip(1)->first()->id, 'status' => ProjectStatus::Completed, 'start_date' => '2025-01-01']);
        $this->get('/projects?q=Maternal&programme='.$target->programme->slug.'&status=ACTIVE&region=ruvuma&year=2026')->assertOk()->assertSee('Maternal outreach')->assertDontSee('OTHER PROJECT');
        $this->get('/projects?status=COMPLETED')->assertSee('OTHER PROJECT')->assertDontSee('Maternal outreach');
        $this->getJson('/projects?status=INVALID&year=not-a-year')->assertUnprocessable()->assertJsonValidationErrors(['status', 'year']);
        $this->getJson('/projects?q='.str_repeat('a', 121))->assertUnprocessable()->assertJsonValidationErrors('q');
    }

    public function test_projects_are_paginated_and_filters_survive_pagination(): void
    {
        for ($i = 0; $i < 14; $i++) {
            $this->project(['title' => ['en' => 'TEST ONLY — Page '.$i]]);
        }
        $response = $this->get('/projects?status=ACTIVE');
        $response->assertOk()->assertViewHas('projects', fn ($p) => $p->count() === 12 && $p->total() === 14);
        $this->assertStringContainsString('status=ACTIVE', $response->viewData('projects')->nextPageUrl());
    }

    public function test_project_details_hide_empty_sections_and_internal_figures(): void
    {
        $project = $this->project(['project_budget' => 987654321, 'people_reached' => 87654321, 'challenge' => ['en' => '<script>bad()</script>']]);
        $this->get('/projects/'.$project->slug)->assertOk()->assertSee('Overview')->assertSee('The Challenge')->assertSee('&lt;script&gt;bad()&lt;/script&gt;', false)->assertDontSee('<script>bad()</script>', false)->assertDontSee('Objectives')->assertDontSee('Project Gallery')->assertDontSee('987654321')->assertDontSee('87654321');
        $this->get('/sitemap.xml')->assertSee('/projects/'.$project->slug);
        $project->delete();
        $this->get('/projects/'.$project->slug)->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee('/projects/'.$project->slug);
        $project->restore();
        $this->get('/projects/'.$project->slug)->assertOk();
    }

    public function test_project_translation_falls_back_to_english(): void
    {
        $project = $this->project(['title' => ['en' => 'English project', 'sw' => 'Mradi wa majaribio']]);
        $this->withSession(['locale' => 'sw'])->get('/projects/'.$project->slug)->assertSee('Mradi wa majaribio')->assertSee('Test overview.');
    }

    public function test_location_and_programme_visibility_cascades_to_public_projects(): void
    {
        $project = $this->project();
        $project->programme->update(['is_active' => false]);
        $this->get('/projects/'.$project->slug)->assertNotFound();
        $project->programme->update(['is_active' => true]);
        Country::first()->update(['is_active' => false]);
        $this->get('/projects/'.$project->slug)->assertNotFound();
        $this->get('/where-we-work')->assertDontSee('Ruvuma')->assertDontSee('Namtumbo');
        $this->get('/')->assertDontSee('Namtumbo');
        Country::first()->update(['is_active' => true]);
        District::first()->delete();
        $this->get('/projects/'.$project->slug)->assertNotFound();
    }

    public function test_project_model_rejects_mismatched_geography_and_dates(): void
    {
        $region = Region::create(['country_id' => Country::first()->id, 'name' => 'TEST ONLY — Other Region']);
        try {
            $this->project(['region_id' => $region->id]);
            $this->fail('Mismatched geography was accepted.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('data.district_id', $e->errors());
        }
        $this->expectException(ValidationException::class);
        $this->project(['end_date' => '2025-01-01']);
    }

    public function test_cms_can_create_publish_feature_and_archive_projects(): void
    {
        $this->actingAs($this->user());
        Livewire::test(CreateProject::class)->assertOk()->fillForm([
            'programme_id' => Programme::first()->id, 'title' => ['en' => 'TEST ONLY — CMS project'], 'short_description' => ['en' => 'Test summary'], 'description' => ['en' => 'Test overview'],
            'region_id' => Region::first()->id, 'district_id' => District::first()->id, 'status' => 'ACTIVE', 'is_published' => true, 'is_featured' => true,
        ])->call('create')->assertHasNoFormErrors();
        $project = Project::firstOrFail();
        $this->assertTrue($project->is_published);
        $this->get('/')->assertSee('TEST ONLY — CMS project');
        $this->get('/programmes/'.$project->programme->slug)->assertSee('TEST ONLY — CMS project');
        Livewire::test(EditProject::class, ['record' => $project->slug])->callAction('delete');
        $this->assertSoftDeleted($project);
        Livewire::test(EditProject::class, ['record' => $project->slug])->callAction('restore');
        $this->assertNotSoftDeleted($project);
    }

    public function test_programme_officer_cannot_publish_by_forging_form_state(): void
    {
        $officer = $this->user('Programme Officer');
        $this->actingAs($officer);
        $this->assertTrue(auth()->user()->can('project.create'));
        Livewire::test(CreateProject::class)
            ->set('data.programme_id', Programme::first()->id)
            ->set('data.title.en', 'TEST ONLY — Officer draft')
            ->set('data.short_description.en', 'Test summary')
            ->set('data.description.en', 'Test overview')
            ->set('data.status', 'PLANNED')
            ->set('data.is_published', true)->set('data.published_at', now()->toDateTimeString())
            ->call('create')->assertHasNoFormErrors();
        $this->assertFalse(Project::first()->is_published);
        $department = Department::where('code', 'PROG')->firstOrFail();
        $officer->givePermissionTo('department.manage_leadership');
        $officer->departments()->attach($department, ['active' => true]);
        $member = TeamMember::create(['department_id' => $department->id, 'user_id' => $officer->id, 'name' => 'Test scoped project manager', 'active' => true]);
        DepartmentLeadership::create(['department_id' => $department->id, 'team_member_id' => $member->id, 'leadership_role' => 'MANAGER', 'appointment_status' => 'ACTIVE', 'can_manage_projects' => true, 'active' => true]);
        $published = $this->project();
        $published->departments()->attach($department, ['is_lead' => true]);
        Livewire::test(EditProject::class, ['record' => $published->slug])->set('data.title.en', 'TEST ONLY — Edited by officer')->call('save')->assertHasNoFormErrors();
        $this->assertFalse($published->fresh()->is_published);
        $this->get('/projects/'.$published->slug)->assertNotFound();
    }

    public function test_admin_location_forms_validate_and_persist_hierarchy(): void
    {
        $this->actingAs($this->user());
        Livewire::test(CreateCountry::class)->fillForm(['name' => 'TEST ONLY country', 'code' => 'XY', 'is_active' => true])->call('create')->assertHasNoFormErrors();
        $country = Country::where('code', 'XY')->firstOrFail();
        Livewire::test(CreateRegion::class)->fillForm(['name' => 'TEST ONLY region', 'country_id' => $country->id, 'display_order' => 1, 'is_active' => true])->call('create')->assertHasNoFormErrors();
        $region = Region::where('country_id', $country->id)->firstOrFail();
        Livewire::test(CreateDistrict::class)->fillForm(['name' => 'TEST ONLY district', 'region_id' => $region->id, 'display_order' => 1, 'is_active' => true])->call('create')->assertHasNoFormErrors();
        $this->get('/where-we-work')->assertSee('TEST ONLY region')->assertSee('TEST ONLY district');
        Livewire::test(CreateRegion::class)->fillForm(['name' => 'TEST ONLY region', 'country_id' => $country->id])->call('create')->assertHasFormErrors(['name']);
    }

    public function test_permission_boundaries_for_all_phase_two_resources(): void
    {
        $user = $this->user('Membership Officer');
        $this->actingAs($user);
        foreach (['projects', 'impact-statistics', 'countries', 'regions', 'districts'] as $path) {
            $this->get('/admin/'.$path)->assertForbidden();
        }
        $officer = $this->user('Programme Officer');
        $this->assertTrue(Gate::forUser($officer)->allows('create', Project::class));
        $this->assertFalse($officer->can('project.publish'));
        $this->assertFalse(Gate::forUser($officer)->allows('create', Country::class));
        $this->assertTrue($this->user('M&E Officer')->can('impact.verify'));
        $this->assertFalse($this->user('M&E Officer')->can('impact.publish'));
    }

    public function test_impact_publication_requires_verification_and_private_sources_stay_private(): void
    {
        $draft = $this->statistic(['label' => ['en' => 'UNVERIFIED FIGURE']], false);
        $this->get('/impact')->assertDontSee('UNVERIFIED FIGURE');
        try {
            $draft->update(['is_active' => true]);
            $this->fail('Unverified figure published.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('data.is_active', $e->errors());
        }
        $published = $this->statistic();
        $this->get('/impact')->assertSee('TEST ONLY reach')->assertSee('1,234')->assertDontSee('INTERNAL TEST EVIDENCE');
        $this->get('/')->assertSee('1,234')->assertDontSee('INTERNAL TEST EVIDENCE');
        $published->update(['value' => 2345]);
        $this->assertNull($published->fresh()->verified_at);
        $this->assertFalse($published->fresh()->is_active);
        $this->get('/impact')->assertDontSee('TEST ONLY reach');
    }

    public function test_impact_verification_requires_permission_and_supporting_source(): void
    {
        $stat = $this->statistic(['verification_source' => null], false);
        try {
            $stat->verify($this->user());
            $this->fail('Missing evidence accepted.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('data.verification_source', $e->errors());
        }
        $this->expectException(AuthorizationException::class);
        $stat->verify($this->user('Programme Officer'));
    }

    public function test_project_and_programme_impact_scopes_and_filters(): void
    {
        $project = $this->project();
        $stat = $this->statistic(['project_id' => $project->id, 'label' => ['en' => 'TEST PROJECT REACH']]);
        $this->assertSame($project->programme_id, $stat->programme_id);
        $this->get('/projects/'.$project->slug)->assertSee('TEST PROJECT REACH')->assertSee('Verified Impact');
        $this->get('/impact?programme='.$project->programme->slug.'&metric=people_reached')->assertSee('TEST PROJECT REACH');
        $project->update(['is_published' => false]);
        $this->get('/impact')->assertDontSee('TEST PROJECT REACH');
        $this->expectException(ValidationException::class);
        $this->statistic(['project_id' => $project->id, 'programme_id' => Programme::skip(1)->first()->id], false);
    }

    public function test_impact_cms_verifies_then_publishes_and_editing_revokes_verification(): void
    {
        $this->actingAs($this->user());
        Livewire::test(CreateImpactStatistic::class)->fillForm(['metric' => 'people_reached', 'label' => ['en' => 'TEST CMS REACH'], 'value' => 10000, 'suffix' => 'K+', 'icon' => 'users', 'as_of_date' => today()->subDay()->toDateString(), 'verification_source' => 'TEST SOURCE', 'display_order' => 1, 'is_active' => false])->call('create')->assertHasNoFormErrors();
        $stat = ImpactStatistic::where('label->en', 'TEST CMS REACH')->firstOrFail();
        Livewire::test(EditImpactStatistic::class, ['record' => $stat->id])->callAction('verify')->assertHasNoErrors();
        $this->assertNotNull($stat->fresh()->verified_at);
        Livewire::test(EditImpactStatistic::class, ['record' => $stat->id])->fillForm(['is_active' => true])->call('save')->assertHasNoFormErrors();
        $this->assertTrue($stat->fresh()->is_active);
        $this->get('/impact')->assertSee('10')->assertSee('K+');
        Livewire::test(EditImpactStatistic::class, ['record' => $stat->id])->fillForm(['value' => 11000])->call('save')->assertHasNoFormErrors();
        $this->assertNull($stat->fresh()->verified_at);
        $this->assertFalse($stat->fresh()->is_active);
    }

    public function test_me_officer_cannot_publish_or_forge_verification_fields(): void
    {
        $this->actingAs($this->user('M&E Officer'));
        Livewire::test(CreateImpactStatistic::class)->fillForm(['metric' => 'people_reached', 'label' => ['en' => 'TEST M&E DRAFT'], 'value' => 10, 'icon' => 'users', 'display_order' => 1])
            ->set('data.is_active', true)->set('data.verified_at', now()->toDateTimeString())->call('create')->assertHasNoFormErrors();
        $stat = ImpactStatistic::where('label->en', 'TEST M&E DRAFT')->firstOrFail();
        $this->assertFalse($stat->is_active);
        $this->assertNull($stat->verified_at);
    }

    public function test_homepage_selection_falls_back_when_chosen_project_is_unpublished(): void
    {
        $first = $this->project(['title' => ['en' => 'FEATURED FALLBACK'], 'is_featured' => true]);
        $selected = $this->project(['title' => ['en' => 'SELECTED HERO']]);
        SiteSetting::first()->update(['data' => array_replace(SiteSetting::first()->data, ['featured_project_id' => $selected->id])]);
        $this->get('/')->assertSee('SELECTED HERO');
        $selected->update(['is_published' => false]);
        app()->forgetScopedInstances();
        $this->get('/')->assertSee('FEATURED FALLBACK')->assertDontSee('SELECTED HERO');
    }

    public function test_dashboard_charts_have_empty_states_and_use_real_records(): void
    {
        $this->actingAs($this->user());
        Livewire::test(ProjectsByStatus::class)->assertSee('No projects yet');
        Livewire::test(ImpactByProgramme::class)->assertSee('No verified programme-level reach figures');
        $this->project();
        $this->statistic(['programme_id' => Programme::first()->id]);
        Livewire::test(ProjectsByStatus::class)->assertDontSee('No projects yet');
        Livewire::test(ImpactByProgramme::class)->assertDontSee('No verified programme-level reach figures');
    }

    public function test_changing_project_programme_invalidates_its_scoped_impact(): void
    {
        $project = $this->project();
        $stat = $this->statistic(['project_id' => $project->id]);
        $project->update(['programme_id' => Programme::skip(1)->first()->id]);
        $this->assertSame($project->programme_id, $stat->fresh()->programme_id);
        $this->assertNull($stat->fresh()->verified_at);
        $this->assertFalse($stat->fresh()->is_active);
    }

    public function test_referenced_district_cannot_move_to_an_inconsistent_project_region(): void
    {
        $project = $this->project();
        $region = Region::create(['country_id' => Country::first()->id, 'name' => 'TEST ONLY other region']);
        $this->expectException(ValidationException::class);
        $project->district->update(['region_id' => $region->id]);
    }

    public function test_reference_seeders_preserve_renamed_and_archived_content(): void
    {
        $region = Region::first();
        $region->update(['name' => 'Edited region']);
        $district = District::first();
        $district->update(['name' => 'Edited district']);
        Programme::first()->delete();
        $this->seed(PhaseTwoSeeder::class);
        $this->seed(ProgrammeSeeder::class);
        $this->assertDatabaseCount('regions', 1);
        $this->assertDatabaseCount('districts', 1);
        $this->assertDatabaseCount('programmes', 7);
        $this->assertSame('Edited region', $region->fresh()->name);
        $this->assertSame('Edited district', $district->fresh()->name);
        $this->assertSame(6, Programme::published()->count());
    }

    public function test_distinct_custom_indicators_are_not_collapsed_into_one_homepage_metric(): void
    {
        $this->statistic(['metric' => 'other', 'label' => ['en' => 'TEST OUTREACH SESSIONS'], 'value' => 12]);
        $this->statistic(['metric' => 'other', 'label' => ['en' => 'TEST SCREENING VISITS'], 'value' => 25]);
        $this->get('/')->assertSee('TEST OUTREACH SESSIONS')->assertSee('TEST SCREENING VISITS');
        $this->get('/impact?metric=other')->assertOk()->assertSee('TEST SCREENING VISITS');
    }
}
