<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('results_framework_items')) {
            Schema::create('results_framework_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('programme_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('results_framework_items')->nullOnDelete();
                $table->string('level', 24)->index();
                $table->string('code', 80)->nullable()->index();
                $table->string('title', 180);
                $table->text('description')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->unsignedInteger('display_order')->default(0);
                $table->boolean('active')->default(true)->index();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['programme_id', 'project_id', 'level', 'active'], 'rf_items_scope_level_active_idx');
            });
        }

        if (Schema::hasTable('reporting_periods')) {
            Schema::table('reporting_periods', function (Blueprint $table): void {
                if (! Schema::hasColumn('reporting_periods', 'data_entry_start')) $table->date('data_entry_start')->nullable()->after('end_date');
                if (! Schema::hasColumn('reporting_periods', 'data_entry_deadline')) $table->date('data_entry_deadline')->nullable()->after('data_entry_start');
                if (! Schema::hasColumn('reporting_periods', 'verification_deadline')) $table->date('verification_deadline')->nullable()->after('data_entry_deadline');
            });
        }

        if (Schema::hasTable('performance_indicators')) {
            Schema::table('performance_indicators', function (Blueprint $table): void {
                if (! Schema::hasColumn('performance_indicators', 'short_name')) $table->string('short_name', 100)->nullable()->after('name');
                if (! Schema::hasColumn('performance_indicators', 'results_framework_item_id')) $table->foreignId('results_framework_item_id')->nullable()->after('description')->constrained('results_framework_items')->nullOnDelete();
                if (! Schema::hasColumn('performance_indicators', 'indicator_type')) $table->string('indicator_type', 20)->nullable()->after('results_framework_item_id')->index();
                if (! Schema::hasColumn('performance_indicators', 'calculation_type')) $table->string('calculation_type', 20)->nullable()->after('measurement_type');
                if (! Schema::hasColumn('performance_indicators', 'calculation_description')) $table->text('calculation_description')->nullable()->after('calculation_type');
                if (! Schema::hasColumn('performance_indicators', 'numerator_definition')) $table->text('numerator_definition')->nullable()->after('calculation_description');
                if (! Schema::hasColumn('performance_indicators', 'denominator_definition')) $table->text('denominator_definition')->nullable()->after('numerator_definition');
                if (! Schema::hasColumn('performance_indicators', 'collection_method')) $table->string('collection_method', 180)->nullable()->after('data_source');
                if (! Schema::hasColumn('performance_indicators', 'responsible_user_id')) $table->foreignId('responsible_user_id')->nullable()->after('department_id')->constrained('users')->nullOnDelete();
                if (! Schema::hasColumn('performance_indicators', 'verification_method')) $table->text('verification_method')->nullable()->after('collection_method');
                if (! Schema::hasColumn('performance_indicators', 'baseline_value')) $table->decimal('baseline_value', 18, 4)->nullable()->after('maximum_value');
                if (! Schema::hasColumn('performance_indicators', 'baseline_date')) $table->date('baseline_date')->nullable()->after('baseline_value');
                if (! Schema::hasColumn('performance_indicators', 'definition_notes')) $table->text('definition_notes')->nullable()->after('description');
            });
        }

        if (Schema::hasTable('performance_targets') && Schema::hasTable('districts')) {
            Schema::table('performance_targets', function (Blueprint $table): void {
                if (! Schema::hasColumn('performance_targets', 'district_id')) $table->foreignId('district_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
            });
        }

        if (Schema::hasTable('performance_targets')) {
            Schema::table('performance_targets', function (Blueprint $table): void {
                if (! Schema::hasColumn('performance_targets', 'created_by_user_id')) $table->foreignId('created_by_user_id')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('performance_targets')) {
            Schema::table('performance_targets', function (Blueprint $table): void {
                if (Schema::hasColumn('performance_targets', 'district_id')) $table->dropConstrainedForeignId('district_id');
                if (Schema::hasColumn('performance_targets', 'created_by_user_id')) $table->dropConstrainedForeignId('created_by_user_id');
            });
        }
        if (Schema::hasTable('performance_indicators')) {
            Schema::table('performance_indicators', function (Blueprint $table): void {
                foreach (['responsible_user_id', 'results_framework_item_id'] as $foreign) {
                    if (Schema::hasColumn('performance_indicators', $foreign)) $table->dropConstrainedForeignId($foreign);
                }
                foreach (['short_name', 'indicator_type', 'calculation_type', 'calculation_description', 'numerator_definition', 'denominator_definition', 'collection_method', 'verification_method', 'baseline_value', 'baseline_date', 'definition_notes'] as $column) {
                    if (Schema::hasColumn('performance_indicators', $column)) $table->dropColumn($column);
                }
            });
        }
        if (Schema::hasTable('reporting_periods')) {
            Schema::table('reporting_periods', function (Blueprint $table): void {
                foreach (['data_entry_start', 'data_entry_deadline', 'verification_deadline'] as $column) {
                    if (Schema::hasColumn('reporting_periods', $column)) $table->dropColumn($column);
                }
            });
        }
        Schema::dropIfExists('results_framework_items');
    }
};
