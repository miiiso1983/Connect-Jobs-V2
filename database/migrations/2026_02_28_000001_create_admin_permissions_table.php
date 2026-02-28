<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_permissions')) {
            return;
        }

        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->boolean('manage_companies')->default(false);
            $table->boolean('manage_jobs')->default(false);
            $table->boolean('manage_jobseekers')->default(false);
            $table->boolean('manage_verifications')->default(false);
            $table->boolean('manage_settings')->default(false);
            $table->boolean('manage_districts')->default(false);
            $table->boolean('manage_admin_users')->default(false);

            $table->timestamps();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_permissions');
    }
};
