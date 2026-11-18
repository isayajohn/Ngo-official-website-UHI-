<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->unsignedBigInteger('head_staff_id')->nullable();
            $table->string('code', 30)->unique();
            $table->string('name', 160);
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('office_location')->nullable();
            $table->string('icon', 40)->nullable();
            $table->string('featured_image')->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->boolean('show_on_website')->default(true)->index();
            $table->boolean('accepts_public_messages')->default(true)->index();
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['parent_id', 'active', 'display_order']);
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('reports_to_position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('title', 160);
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();
            $table->string('level', 80)->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['department_id', 'active', 'display_order']);
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 160);
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('short_bio')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->string('photo_alt')->nullable();
            $table->string('employment_type', 50)->nullable();
            $table->unsignedInteger('display_order')->default(0)->index();
            $table->boolean('show_on_website')->default(true)->index();
            $table->boolean('active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['department_id', 'active', 'display_order']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('head_staff_id')->references('id')->on('team_members')->nullOnDelete();
        });

        Schema::create('department_programme', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['department_id', 'programme_id']);
        });

        Schema::create('department_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_lead')->default(false);
            $table->timestamps();
            $table->unique(['department_id', 'project_id']);
            $table->index(['project_id', 'is_lead']);
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 160);
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status', 30)->default('NEW')->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['department_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('department_project');
        Schema::dropIfExists('department_programme');
        Schema::table('departments', fn (Blueprint $table) => $table->dropForeign(['head_staff_id']));
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
