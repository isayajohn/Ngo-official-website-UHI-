<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_invitations', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->index();
            $table->foreignId('team_member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invited_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('primary_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('token_hash');
            $table->string('status', 20)->default('PENDING')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'status']);
        });

        Schema::create('user_invitation_departments', function (Blueprint $table): void {
            $table->foreignId('user_invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_invitation_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_invitation_departments');
        Schema::dropIfExists('user_invitations');
    }
};
