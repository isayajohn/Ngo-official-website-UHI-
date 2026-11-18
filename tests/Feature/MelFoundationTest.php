<?php

namespace Tests\Feature;

use App\Enums\IndicatorCalculationType;
use App\Enums\IndicatorType;
use App\Enums\ResultsFrameworkLevel;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceTarget;
use App\Models\ReportingPeriod;
use App\Models\ResultsFrameworkItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MelFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_results_framework_hierarchy_and_indicator_links_are_available(): void
    {
        $impact = ResultsFrameworkItem::create(['level' => ResultsFrameworkLevel::Impact, 'title' => 'Improved community health']);
        $outcome = ResultsFrameworkItem::create(['level' => ResultsFrameworkLevel::Outcome, 'title' => 'Improved access', 'parent_id' => $impact->id]);
        $output = ResultsFrameworkItem::create(['level' => ResultsFrameworkLevel::Output, 'title' => 'Services delivered', 'parent_id' => $outcome->id]);
        $period = ReportingPeriod::create(['name' => 'M&E Q1 2026', 'period_type' => 'QUARTERLY', 'start_date' => '2026-01-01', 'end_date' => '2026-03-31', 'data_entry_deadline' => '2026-04-10', 'verification_deadline' => '2026-04-20', 'status' => 'OPEN']);
        $indicator = PerformanceIndicator::create([
            'code' => 'MEL-001', 'name' => 'Children reached', 'level' => 'ORGANIZATION', 'indicator_type' => IndicatorType::Output,
            'results_framework_item_id' => $output->id, 'measurement_type' => 'NUMBER', 'calculation_type' => IndicatorCalculationType::Sum,
            'direction' => 'HIGHER_IS_BETTER', 'weight' => 0, 'reporting_frequency' => 'QUARTERLY', 'data_source' => 'MANUAL', 'active' => true,
        ]);

        $this->assertTrue($impact->children->contains($outcome));
        $this->assertTrue($outcome->children->contains($output));
        $this->assertTrue($output->indicators->contains($indicator));
        $this->assertSame('2026-04-10', $period->data_entry_deadline->toDateString());
    }

    public function test_results_framework_rejects_circular_parent_relationships(): void
    {
        $first = ResultsFrameworkItem::create(['level' => ResultsFrameworkLevel::Output, 'title' => 'First output']);
        $second = ResultsFrameworkItem::create(['level' => ResultsFrameworkLevel::Activity, 'title' => 'Child activity', 'parent_id' => $first->id]);

        $this->expectException(ValidationException::class);
        $first->update(['parent_id' => $second->id]);
    }

    public function test_targets_are_unique_by_indicator_period_and_scope(): void
    {
        $period = ReportingPeriod::create(['name' => 'M&E Q2 2026', 'period_type' => 'QUARTERLY', 'start_date' => '2026-04-01', 'end_date' => '2026-06-30', 'status' => 'OPEN']);
        $indicator = PerformanceIndicator::create(['code' => 'MEL-002', 'name' => 'Households reached', 'level' => 'ORGANIZATION', 'indicator_type' => IndicatorType::Outcome, 'measurement_type' => 'NUMBER', 'calculation_type' => IndicatorCalculationType::Count, 'direction' => 'HIGHER_IS_BETTER', 'weight' => 0, 'data_source' => 'MANUAL', 'active' => true]);
        PerformanceTarget::create(['performance_indicator_id' => $indicator->id, 'reporting_period_id' => $period->id, 'target_value' => 100]);

        $this->expectException(ValidationException::class);
        PerformanceTarget::create(['performance_indicator_id' => $indicator->id, 'reporting_period_id' => $period->id, 'target_value' => 120]);
    }

    public function test_m_and_e_resources_require_permissions(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->givePermissionTo('admin.access');

        $this->actingAs($user)->get('/admin/results-frameworks')->assertForbidden();

        $user->givePermissionTo('mel.results_framework.view');
        $this->actingAs($user)->get('/admin/results-frameworks')->assertOk()->assertSee('Results Framework');
    }

    public function test_m_and_e_dashboard_renders_real_foundation_counts_for_authorized_users(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->givePermissionTo(['admin.access', 'organization.full_access', 'mel.dashboard.view']);
        ResultsFrameworkItem::create(['level' => ResultsFrameworkLevel::Impact, 'title' => 'Improved health']);
        PerformanceIndicator::create(['code' => 'MEL-DASH-01', 'name' => 'People reached', 'level' => 'MEL', 'indicator_type' => IndicatorType::Output, 'measurement_type' => 'NUMBER', 'calculation_type' => IndicatorCalculationType::Count, 'direction' => 'HIGHER_IS_BETTER', 'weight' => 0, 'data_source' => 'MANUAL', 'active' => true]);

        $this->actingAs($user)->get('/admin/mel-dashboard')->assertOk()->assertSee('Results framework and indicator management')->assertSee('1');
    }

    public function test_technical_administrator_does_not_receive_m_and_e_write_permissions(): void
    {
        $administrator = Role::query()->where('name', 'Administrator')->firstOrFail();
        $meOfficer = Role::query()->where('name', 'M&E Officer')->firstOrFail();

        foreach (['mel.results_framework.manage', 'mel.indicator.create', 'mel.target.manage', 'performance.manage_kpi', 'performance.manage_target', 'performance.enter_result', 'performance.verify_result'] as $permission) {
            $this->assertFalse($administrator->hasPermissionTo($permission));
        }
        $this->assertTrue($meOfficer->hasPermissionTo('mel.indicator.create'));
        $this->assertTrue($meOfficer->hasPermissionTo('mel.target.manage'));
    }
}
