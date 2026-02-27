<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('job_seekers')) {
            return;
        }

        Schema::table('job_seekers', function (Blueprint $table) {
            // Education fields
            if (! Schema::hasColumn('job_seekers', 'university_name')) {
                $table->string('university_name', 255)->nullable()->after('experience_level');
            }
            if (! Schema::hasColumn('job_seekers', 'college_name')) {
                $table->string('college_name', 255)->nullable()->after('university_name');
            }
            if (! Schema::hasColumn('job_seekers', 'department_name')) {
                $table->string('department_name', 255)->nullable()->after('college_name');
            }
            if (! Schema::hasColumn('job_seekers', 'graduation_year')) {
                $table->unsignedSmallInteger('graduation_year')->nullable()->after('department_name');
            }

            // Fresh graduates
            if (! Schema::hasColumn('job_seekers', 'is_fresh_graduate')) {
                $table->boolean('is_fresh_graduate')->default(false)->after('graduation_year');
            }

            // CV verification
            if (! Schema::hasColumn('job_seekers', 'cv_verified')) {
                $table->boolean('cv_verified')->default(false)->after('cv_file');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('job_seekers')) {
            return;
        }

        Schema::table('job_seekers', function (Blueprint $table) {
            foreach ([
                'cv_verified',
                'is_fresh_graduate',
                'graduation_year',
                'department_name',
                'college_name',
                'university_name',
            ] as $col) {
                if (Schema::hasColumn('job_seekers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

