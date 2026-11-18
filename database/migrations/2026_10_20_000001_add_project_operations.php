<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('code')->nullable()->unique();
            $table->foreignId('project_manager_team_member_id')->nullable()->constrained('team_members')->nullOnDelete();
            // Grants are introduced in the later Donors & Grants phase, so this remains
            // an indexed nullable reference until that table exists.
            $table->unsignedBigInteger('grant_id')->nullable()->index();
        });

        Schema::create('project_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('activity_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('PLANNED')->index();
            $table->foreignId('responsible_team_member_id')->nullable()->constrained('team_members')->nullOnDelete();
            $table->unsignedBigInteger('budget_line_id')->nullable()->index();
            $table->string('location')->nullable();
            $table->unsignedInteger('target_participants')->nullable();
            $table->unsignedInteger('actual_participants')->nullable();
            $table->unsignedTinyInteger('completion_percentage')->default(0);
            $table->timestamps();
            $table->index(['project_id', 'start_date']);
        });

        Schema::create('project_milestones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->string('status', 30)->default('UPCOMING')->index();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('project_risks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('likelihood')->default(1);
            $table->unsignedTinyInteger('impact')->default(1);
            $table->string('risk_level', 20)->default('LOW')->index();
            $table->text('mitigation')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('OPEN')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_risks');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('project_activities');
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['project_manager_team_member_id']);
            $table->dropUnique(['code']);
            $table->dropIndex(['grant_id']);
            $table->dropColumn(['code', 'project_manager_team_member_id', 'grant_id']);
        });
    }
};
