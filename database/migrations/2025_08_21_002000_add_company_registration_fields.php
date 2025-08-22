<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies','scientific_office_name')) {
                $table->string('scientific_office_name', 150)->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('companies','company_job_title')) {
                $table->string('company_job_title', 150)->nullable()->after('scientific_office_name');
            }
            if (!Schema::hasColumn('companies','mobile_number')) {
                $table->string('mobile_number', 30)->nullable()->after('company_job_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies','mobile_number')) {
                $table->dropColumn('mobile_number');
            }
            if (Schema::hasColumn('companies','company_job_title')) {
                $table->dropColumn('company_job_title');
            }
            if (Schema::hasColumn('companies','scientific_office_name')) {
                $table->dropColumn('scientific_office_name');
            }
        });
    }
};

