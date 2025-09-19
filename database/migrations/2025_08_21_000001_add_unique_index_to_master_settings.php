<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('master_settings')) {
            Schema::table('master_settings', function (Blueprint $table) {
                // Add unique index only if it does not already exist
                try {
                    $table->unique(['setting_type','value'], 'master_settings_type_value_unique');
                } catch (\Throwable $e) {
                    // Ignore if the index already exists or SQLite limitations
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('master_settings')) {
            Schema::table('master_settings', function (Blueprint $table) {
                try { $table->dropUnique('master_settings_type_value_unique'); } catch (\Throwable $e) {}
            });
        }
    }
};

