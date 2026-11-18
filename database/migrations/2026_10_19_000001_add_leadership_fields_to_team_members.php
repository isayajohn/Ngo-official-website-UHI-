<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table): void {
            $table->boolean('is_leader')->default(false)->after('active')->index();
            $table->string('leadership_role', 160)->nullable()->after('is_leader');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table): void {
            $table->dropIndex(['is_leader']);
            $table->dropColumn(['is_leader', 'leadership_role']);
        });
    }
};
