<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('involvement_applications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 24)->index();
            $table->string('name', 160);
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('organization', 180)->nullable();
            $table->string('interest_area', 180)->nullable();
            $table->string('availability', 180)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 24)->default('NEW')->index();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->timestamp('submitted_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('involvement_applications');
    }
};
