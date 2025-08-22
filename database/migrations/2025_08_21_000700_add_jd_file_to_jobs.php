<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('jobs','jd_file')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->string('jd_file',255)->nullable()->after('approved_by_admin');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jobs','jd_file')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->dropColumn('jd_file');
            });
        }
    }
};

