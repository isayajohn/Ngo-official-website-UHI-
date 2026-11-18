<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('donor_name', 160);
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('TZS');
            $table->string('frequency', 20)->default('one_time')->index();
            $table->string('purpose', 80)->default('where_needed')->index();
            $table->string('payment_method', 40)->default('contact_me');
            $table->string('payment_reference')->nullable();
            $table->string('status', 20)->default('PENDING')->index();
            $table->timestamp('received_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
