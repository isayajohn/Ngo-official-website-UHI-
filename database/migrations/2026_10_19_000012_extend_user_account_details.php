<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('middle_name', 100)->nullable()->after('first_name');
            $table->string('last_name', 100)->nullable()->after('middle_name');
            $table->string('username', 100)->nullable()->unique()->after('last_name');
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('profile_photo')->nullable()->after('phone');
            $table->foreignId('team_member_id')->nullable()->unique()->after('profile_photo')->constrained()->nullOnDelete();
            $table->string('status', 20)->default('ACTIVE')->index()->after('is_active');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->unsignedSmallInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->string('locale', 8)->default('en');
            $table->string('timezone', 64)->default('Africa/Dar_es_Salaam');
            $table->json('notification_preferences')->nullable();
        });

        DB::table('users')->orderBy('id')->chunkById(200, function ($users): void {
            foreach ($users as $user) {
                $parts = preg_split('/\s+/', trim($user->name), 2) ?: [];
                DB::table('users')->where('id', $user->id)->update([
                    'first_name' => $parts[0] ?? '',
                    'last_name' => $parts[1] ?? '',
                    'status' => $user->is_active ? 'ACTIVE' : 'INACTIVE',
                ]);
            }
        });

        // Preserve existing optional TeamMember -> User associations as the new
        // one-to-one User -> TeamMember link where each member has one account.
        $linkedMemberIds = DB::table('team_members')->select('user_id')->whereNotNull('user_id')->groupBy('user_id')->havingRaw('COUNT(*) = 1')->pluck('user_id');
        foreach ($linkedMemberIds as $userId) {
            $memberId = DB::table('team_members')->where('user_id', $userId)->value('id');
            DB::table('users')->where('id', $userId)->whereNull('team_member_id')->update(['team_member_id' => $memberId]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['team_member_id']);
            $table->dropUnique(['team_member_id']);
            $table->dropUnique(['username']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'first_name', 'middle_name', 'last_name', 'username', 'phone', 'profile_photo', 'team_member_id', 'status',
                'phone_verified_at', 'last_login_ip', 'failed_login_attempts', 'locked_until', 'password_changed_at',
                'locale', 'timezone', 'notification_preferences',
            ]);
        });
    }
};
