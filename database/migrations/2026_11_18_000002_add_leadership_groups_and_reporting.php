<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table): void {
            $table->string('leadership_group', 40)->default('other')->index()->after('leadership_role');
            $table->foreignId('leadership_parent_id')->nullable()->after('leadership_group')->constrained('team_members')->nullOnDelete();
            $table->index(['leadership_group', 'leadership_parent_id', 'is_leader'], 'team_members_leadership_tree_idx');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table): void {
            $table->dropIndex('team_members_leadership_tree_idx');
            $table->dropConstrainedForeignId('leadership_parent_id');
            $table->dropIndex(['leadership_group']);
            $table->dropColumn('leadership_group');
        });
    }
};
