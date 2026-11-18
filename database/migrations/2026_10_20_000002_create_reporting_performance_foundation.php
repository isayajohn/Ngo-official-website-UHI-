<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reporting_periods')) Schema::create('reporting_periods', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('period_type', 24)->index();
            $table->foreignId('financial_year_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('submission_deadline')->nullable();
            $table->string('status', 20)->default('UPCOMING')->index();
            $table->timestamps();
            $table->index(['start_date', 'end_date']);
        });

        if (! Schema::hasTable('performance_indicators')) Schema::create('performance_indicators', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->string('level', 24)->index();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('programme_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            // No internal MEL indicator table exists yet. This nullable reference is
            // reserved for a later FK when that module is introduced.
            $table->unsignedBigInteger('indicator_id')->nullable()->index();
            $table->string('measurement_type', 24)->default('NUMBER');
            $table->string('unit', 40)->nullable();
            $table->string('direction', 24)->default('HIGHER_IS_BETTER');
            $table->decimal('weight', 6, 2)->default(0);
            $table->decimal('target_value', 18, 4)->nullable();
            $table->decimal('minimum_value', 18, 4)->nullable();
            $table->decimal('maximum_value', 18, 4)->nullable();
            $table->string('reporting_frequency', 24)->default('QUARTERLY');
            $table->string('data_source', 32)->default('MANUAL')->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['level', 'department_id', 'active']);
            $table->index(['level', 'programme_id', 'active']);
            $table->index(['level', 'project_id', 'active']);
        });

        if (! Schema::hasTable('performance_targets')) Schema::create('performance_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('performance_indicator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporting_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('programme_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_member_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('target_value', 18, 4);
            $table->decimal('weight_override', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['reporting_period_id', 'department_id']);
            $table->index(['reporting_period_id', 'programme_id']);
            $table->index(['reporting_period_id', 'project_id']);
            $table->index(['performance_indicator_id', 'reporting_period_id'], 'perf_targets_kpi_period_idx');
        });

        if (! Schema::hasTable('performance_results')) Schema::create('performance_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('performance_indicator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performance_target_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reporting_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('programme_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_member_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('actual_value', 18, 4);
            $table->decimal('achievement_percentage', 8, 2)->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->string('status', 20)->default('DRAFT')->index();
            $table->string('source_type', 32)->default('MANUAL');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('entered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->index(['reporting_period_id', 'department_id', 'status'], 'perf_results_period_dept_status_idx');
            $table->index(['reporting_period_id', 'programme_id', 'status'], 'perf_results_period_prog_status_idx');
            $table->index(['reporting_period_id', 'project_id', 'status'], 'perf_results_period_project_status_idx');
            $table->index(['performance_indicator_id', 'reporting_period_id', 'status'], 'perf_results_kpi_period_status_idx');
            $table->index(['team_member_id', 'reporting_period_id', 'status'], 'perf_results_member_period_status_idx');
        });

        if (! Schema::hasTable('performance_rating_scales')) Schema::create('performance_rating_scales', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120)->default('UGHI Performance Rating');
            $table->decimal('minimum_score', 8, 2);
            $table->decimal('maximum_score', 8, 2);
            $table->string('label', 80);
            $table->text('description')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->index(['minimum_score', 'maximum_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_rating_scales');
        Schema::dropIfExists('performance_results');
        Schema::dropIfExists('performance_targets');
        Schema::dropIfExists('performance_indicators');
        Schema::dropIfExists('reporting_periods');
    }
};
