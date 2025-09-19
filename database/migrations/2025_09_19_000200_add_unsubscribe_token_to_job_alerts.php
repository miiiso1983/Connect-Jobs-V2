<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_alerts', function (Blueprint $table) {
            $table->string('unsubscribe_token', 64)->nullable()->unique()->after('enabled');
        });
    }

    public function down(): void
    {
        Schema::table('job_alerts', function (Blueprint $table) {
            $table->dropUnique(['unsubscribe_token']);
            $table->dropColumn('unsubscribe_token');
        });
    }
};

