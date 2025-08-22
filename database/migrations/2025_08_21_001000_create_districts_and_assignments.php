<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('province', 100);
            $table->string('name', 150);
            $table->unique(['province','name'], 'districts_province_name_unique');
        });

        Schema::table('job_seekers', function (Blueprint $table) {
            if (!Schema::hasColumn('job_seekers','districts')) {
                $table->json('districts')->nullable()->after('province');
            }
        });
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs','districts')) {
                $table->json('districts')->nullable()->after('province');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) { if (Schema::hasColumn('jobs','districts')) $table->dropColumn('districts'); });
        Schema::table('job_seekers', function (Blueprint $table) { if (Schema::hasColumn('job_seekers','districts')) $table->dropColumn('districts'); });
        Schema::dropIfExists('districts');
    }
};

