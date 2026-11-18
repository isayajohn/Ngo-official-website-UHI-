<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceRatingScale;
use App\Models\PerformanceResult;
use App\Models\PerformanceTarget;
use App\Models\ReportingPeriod;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Services\PerformanceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PerformanceFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_reporting_period_rejects_an_end_date_before_its_start_date(): void
    {
        $this->expectException(ValidationException::class);
        ReportingPeriod::create(['name' => 'Invalid period', 'period_type' => 'QUARTERLY', 'start_date' => '2026-04-01', 'end_date' => '2026-03-31']);
    }

    public function test_calculator_handles_higher_lower_range_and_weighted_measurements(): void
    {
        $calculator = app(PerformanceCalculationService::class);

        $this->assertSame(75.0, $calculator->calculateAchievement(75, 100, 'HIGHER_IS_BETTER'));
        $this->assertSame(100.0, $calculator->calculateAchievement(8, 10, 'LOWER_IS_BETTER'));
        $this->assertSame(50.0, $calculator->calculateAchievement(120, null, 'TARGET_RANGE', 90, 110));
        $this->assertSame(85.0, $calculator->calculateWeightedScore([
            ['achievement_percentage' => 100, 'weight' => 25],
            ['achievement_percentage' => 80, 'weight' => 75],
        ]));
        $this->assertNull($calculator->calculateWeightedScore([['achievement_percentage' => null, 'weight' => 100]]));
    }

    public function test_rating_scale_returns_the_configured_band(): void
    {
        $scale = PerformanceRatingScale::where('label', 'Excellent')->firstOrFail();
        $this->assertSame($scale->id, app(PerformanceCalculationService::class)->ratingForScore(95)?->id);
        $this->assertNull(app(PerformanceCalculationService::class)->ratingForScore(null));
    }

    public function test_technical_administrator_roles_do_not_get_staff_appraisal_access(): void
    {
        foreach (['Super Admin', 'Administrator'] as $roleName) {
            $role = Role::where('name', $roleName)->firstOrFail();
            $this->assertFalse($role->hasPermissionTo('staff_performance.view'));
            $this->assertFalse($role->hasPermissionTo('staff_performance.review'));
            $this->assertFalse($role->hasPermissionTo('staff_performance.approve'));
        }
    }

    public function test_department_target_must_match_its_indicator_scope(): void
    {
        $departments = Department::whereIn('code', ['PROG', 'MEL'])->get()->keyBy('code');
        $period = ReportingPeriod::create(['name' => 'Test Q1', 'period_type' => 'QUARTERLY', 'start_date' => '2026-01-01', 'end_date' => '2026-03-31', 'status' => 'ACTIVE']);
        $indicator = PerformanceIndicator::create(['code' => 'TEST-DEPT-KPI', 'name' => 'Department KPI', 'level' => 'DEPARTMENT', 'department_id' => $departments['PROG']->id, 'measurement_type' => 'NUMBER', 'direction' => 'HIGHER_IS_BETTER', 'weight' => 100, 'data_source' => 'MANUAL', 'active' => true]);

        $this->expectException(ValidationException::class);
        PerformanceTarget::create(['performance_indicator_id' => $indicator->id, 'reporting_period_id' => $period->id, 'department_id' => $departments['MEL']->id, 'target_value' => 10]);
    }

    public function test_verified_result_cannot_be_changed_after_verification(): void
    {
        $department = Department::where('code', 'PROG')->firstOrFail();
        $reviewer = User::factory()->create();
        $period = ReportingPeriod::create(['name' => 'Test Q2', 'period_type' => 'QUARTERLY', 'start_date' => '2026-04-01', 'end_date' => '2026-06-30', 'status' => 'ACTIVE']);
        $indicator = PerformanceIndicator::create(['code' => 'TEST-IMMUTABLE-KPI', 'name' => 'Immutable KPI', 'level' => 'DEPARTMENT', 'department_id' => $department->id, 'measurement_type' => 'NUMBER', 'direction' => 'HIGHER_IS_BETTER', 'weight' => 100, 'data_source' => 'MANUAL', 'active' => true]);
        $target = PerformanceTarget::create(['performance_indicator_id' => $indicator->id, 'reporting_period_id' => $period->id, 'department_id' => $department->id, 'target_value' => 10]);
        $result = PerformanceResult::create(['performance_indicator_id' => $indicator->id, 'performance_target_id' => $target->id, 'reporting_period_id' => $period->id, 'department_id' => $department->id, 'actual_value' => 10, 'status' => 'VERIFIED', 'source_type' => 'MANUAL', 'verified_by_user_id' => $reviewer->id, 'verified_at' => now()]);

        $this->expectException(ValidationException::class);
        $result->update(['actual_value' => 11]);
    }

    public function test_performance_dashboard_requires_view_permission_and_renders_for_authorized_user(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->givePermissionTo('admin.access');
        $this->actingAs($user)->get('/admin/performance-dashboard')->assertForbidden();

        $user->givePermissionTo('performance.view_organization');
        $this->actingAs($user)->get('/admin/performance-dashboard')->assertOk()->assertSee('Performance overview');
    }
}
