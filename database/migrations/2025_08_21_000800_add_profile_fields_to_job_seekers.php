<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            if (!Schema::hasColumn('job_seekers','cv_file')) {
                $table->string('cv_file',255)->nullable()->after('profile_completed');
            }
            if (!Schema::hasColumn('job_seekers','summary')) {
                $table->text('summary')->nullable()->after('cv_file');
            }
            if (!Schema::hasColumn('job_seekers','qualifications')) {
                $table->text('qualifications')->nullable()->after('summary');
            }
            if (!Schema::hasColumn('job_seekers','experiences')) {
                $table->text('experiences')->nullable()->after('qualifications');
            }
            if (!Schema::hasColumn('job_seekers','languages')) {
                $table->text('languages')->nullable()->after('experiences');
            }
            if (!Schema::hasColumn('job_seekers','skills')) {
                $table->text('skills')->nullable()->after('languages');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            foreach (['cv_file','summary','qualifications','experiences','languages','skills'] as $col) {
                if (Schema::hasColumn('job_seekers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

