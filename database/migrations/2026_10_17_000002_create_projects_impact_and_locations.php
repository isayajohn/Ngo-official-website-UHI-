<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->char('code', 2)->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['country_id', 'name']);
        });
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->string('slug')->unique();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['region_id', 'name']);
        });
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->restrictOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->restrictOnDelete();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('short_description');
            $table->json('description');
            foreach (['challenge', 'objectives', 'activities', 'target_groups'] as $field) {
                $table->json($field)->nullable();
            }
            $table->string('featured_image')->nullable();
            $table->string('image_alt')->nullable();
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('PLANNED')->index();
            $table->string('location')->nullable();
            $table->string('funding_partner')->nullable();
            $table->decimal('project_budget', 18, 2)->nullable();
            $table->unsignedBigInteger('people_reached')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->index(['is_published', 'published_at']);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('image');
            $table->string('alt_text');
            $table->string('caption')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });
        Schema::create('impact_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('metric', 50)->index();
            $table->json('label');
            $table->unsignedBigInteger('value')->default(0);
            $table->string('suffix', 5)->default('');
            $table->string('icon', 30)->default('users');
            $table->date('as_of_date')->nullable()->index();
            $table->text('verification_source')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(false);
            $table->index(['is_active', 'verified_at']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['impact_statistics', 'project_images', 'projects', 'districts', 'regions', 'countries'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
