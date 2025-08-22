<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('master_settings', function (Blueprint $table) {
            $table->unique(['setting_type','value'], 'master_settings_type_value_unique');
        });
    }

    public function down(): void
    {
        Schema::table('master_settings', function (Blueprint $table) {
            $table->dropUnique('master_settings_type_value_unique');
        });
    }
};

