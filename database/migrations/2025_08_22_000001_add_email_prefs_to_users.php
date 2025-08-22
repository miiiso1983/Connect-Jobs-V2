<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users','email_opt_in')) {
                $table->boolean('email_opt_in')->default(true)->after('email');
            }
            if (!Schema::hasColumn('users','job_alerts_opt_in')) {
                $table->boolean('job_alerts_opt_in')->default(true)->after('email_opt_in');
            }
            if (!Schema::hasColumn('users','application_notifications_opt_in')) {
                $table->boolean('application_notifications_opt_in')->default(true)->after('job_alerts_opt_in');
            }
            if (!Schema::hasColumn('users','email_unsubscribe_token')) {
                // place after remember_token if it exists, otherwise after password
                $after = Schema::hasColumn('users','remember_token') ? 'remember_token' : 'password';
                $table->string('email_unsubscribe_token',64)->nullable()->unique()->after($after);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users','email_opt_in')) {
                $table->dropColumn('email_opt_in');
            }
            if (Schema::hasColumn('users','job_alerts_opt_in')) {
                $table->dropColumn('job_alerts_opt_in');
            }
            if (Schema::hasColumn('users','application_notifications_opt_in')) {
                $table->dropColumn('application_notifications_opt_in');
            }
            if (Schema::hasColumn('users','email_unsubscribe_token')) {
                $table->dropUnique('users_email_unsubscribe_token_unique');
                $table->dropColumn('email_unsubscribe_token');
            }
        });
    }
};

