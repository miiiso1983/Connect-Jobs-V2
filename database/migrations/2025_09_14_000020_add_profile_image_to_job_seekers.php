<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            if (!Schema::hasColumn('job_seekers', 'profile_image')) {
                $table->string('profile_image', 255)->nullable()->after('cv_file');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            if (Schema::hasColumn('job_seekers', 'profile_image')) {
                $table->dropColumn('profile_image');
            }
        });
    }
};

