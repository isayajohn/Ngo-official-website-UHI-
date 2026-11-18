<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_leadership', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            $table->string('leadership_role', 32);
            $table->boolean('is_primary')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('appointment_status', 24)->default('ACTIVE');
            $table->boolean('can_approve')->default(false);
            $table->boolean('can_assign_tasks')->default(false);
            $table->boolean('can_manage_staff')->default(false);
            $table->boolean('can_manage_projects')->default(false);
            $table->boolean('can_view_reports')->default(true);
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['department_id', 'leadership_role', 'active']);
            $table->index(['team_member_id', 'active']);
        });

        Schema::create('user_departments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->string('department_role', 100)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'department_id']);
            $table->index(['department_id', 'active']);
        });

        Schema::create('department_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->foreignId('assigned_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to_team_member_id')->nullable()->constrained('team_members')->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('priority', 16)->default('MEDIUM');
            $table->string('status', 20)->default('PENDING');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_note')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['department_id', 'status', 'due_date']);
            $table->index(['assigned_to_user_id', 'status']);
        });

        Schema::create('department_task_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('department_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->text('comment');
            $table->timestamps();
        });

        Schema::create('department_task_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('department_tasks')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('disk', 40)->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });

        Schema::create('approval_workflows', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('code', 80)->unique();
            $table->string('module', 80)->index();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('approval_workflow_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('name', 160);
            $table->string('approver_type', 32);
            $table->string('required_permission')->nullable();
            $table->string('required_leadership_role', 32)->nullable();
            $table->foreignId('specific_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('department_scoped')->default(true);
            $table->boolean('can_return')->default(true);
            $table->boolean('can_reject')->default(true);
            $table->timestamps();
            $table->unique(['workflow_id', 'step_order']);
        });

        Schema::create('approval_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->restrictOnDelete();
            $table->morphs('approvable');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('current_step')->default(1);
            $table->string('status', 24)->default('DRAFT')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['department_id', 'status']);
        });

        Schema::create('approval_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('approval_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_step_id')->nullable()->constrained('approval_workflow_steps')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 24);
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index(['approval_request_id', 'created_at']);
        });

        Schema::create('department_activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 100)->index();
            $table->nullableMorphs('subject');
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index(['department_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_activity_logs');
        Schema::dropIfExists('approval_actions');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_workflow_steps');
        Schema::dropIfExists('approval_workflows');
        Schema::dropIfExists('department_task_attachments');
        Schema::dropIfExists('department_task_comments');
        Schema::dropIfExists('department_tasks');
        Schema::dropIfExists('user_departments');
        Schema::dropIfExists('department_leadership');
    }
};
