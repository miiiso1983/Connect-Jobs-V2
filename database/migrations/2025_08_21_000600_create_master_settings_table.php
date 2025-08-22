<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('master_settings')) {
            Schema::create('master_settings', function (Blueprint $table) {
                $table->id();
                $table->string('setting_type', 100);
                $table->string('value', 191);
                $table->unique(['setting_type', 'value']);
                $table->index(['setting_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('master_settings');
    }
};

