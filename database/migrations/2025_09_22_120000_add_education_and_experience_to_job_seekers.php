<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            if (!Schema::hasColumn('job_seekers','education_level')) {
                $table->string('education_level', 100)->nullable()->after('specialities');
                $table->index('education_level');
            }
            if (!Schema::hasColumn('job_seekers','experience_level')) {
                $table->string('experience_level', 100)->nullable()->after('education_level');
                $table->index('experience_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            if (Schema::hasColumn('job_seekers','experience_level')) {
                $table->dropIndex(['experience_level']);
                $table->dropColumn('experience_level');
            }
            if (Schema::hasColumn('job_seekers','education_level')) {
                $table->dropIndex(['education_level']);
                $table->dropColumn('education_level');
            }
        });
    }
};

