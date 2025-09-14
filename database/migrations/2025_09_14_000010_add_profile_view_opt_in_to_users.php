<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_view_notifications_opt_in')) {
                $table->boolean('profile_view_notifications_opt_in')->default(true)->after('application_notifications_opt_in');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'profile_view_notifications_opt_in')) {
                $table->dropColumn('profile_view_notifications_opt_in');
            }
        });
    }
};

