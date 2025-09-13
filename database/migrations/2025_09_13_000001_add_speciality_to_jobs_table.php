<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('jobs', 'speciality')) {
                $table->string('speciality', 150)->nullable()->after('title');
                $table->index('speciality');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (Schema::hasColumn('jobs', 'speciality')) {
                $table->dropIndex(['speciality']);
                $table->dropColumn('speciality');
            }
        });
    }
};

