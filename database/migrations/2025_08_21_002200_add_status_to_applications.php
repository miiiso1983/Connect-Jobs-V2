<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications','status')) {
                $table->enum('status', ['pending','accepted','rejected','archived'])->default('pending')->after('applied_at');
                $table->index('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications','status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }
        });
    }
};

