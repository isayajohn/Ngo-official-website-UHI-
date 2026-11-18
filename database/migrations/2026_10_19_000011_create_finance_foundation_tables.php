<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_years', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 16)->default('OPEN')->index();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['start_date', 'end_date']);
        });

        Schema::create('accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('code', 32)->unique();
            $table->string('name', 160);
            $table->string('type', 16)->index();
            $table->string('normal_balance', 8);
            $table->text('description')->nullable();
            $table->boolean('allow_transactions')->default(true);
            $table->boolean('active')->default(true)->index();
            $table->unsignedInteger('display_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['parent_id', 'active', 'display_order']);
        });

        Schema::create('bank_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->unique()->constrained('accounts')->restrictOnDelete();
            $table->string('bank_name', 160);
            $table->string('account_name', 160);
            $table->string('masked_account_number', 80)->nullable();
            $table->char('currency', 3)->default('TZS')->index();
            $table->string('account_type', 40)->nullable();
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->boolean('active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->unique()->constrained('accounts')->restrictOnDelete();
            $table->string('name', 160);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->char('currency', 3)->default('TZS')->index();
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->boolean('active')->default(true)->index();
            $table->foreignId('custodian_team_member_id')->nullable()->constrained('team_members')->nullOnDelete();
            $table->timestamps();
            $table->index(['department_id', 'active']);
        });

        Schema::create('finance_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 100)->index();
            $table->nullableMorphs('subject');
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index(['created_at', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_audit_logs');
        Schema::dropIfExists('cash_accounts');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('financial_years');
    }
};
