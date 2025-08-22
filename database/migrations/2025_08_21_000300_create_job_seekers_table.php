<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('job_seekers')) {
            Schema::create('job_seekers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('full_name', 150);
                $table->string('province', 100);
                $table->string('job_title', 150)->nullable();
                $table->string('speciality', 150)->nullable();
                $table->enum('gender', ['male','female','other'])->nullable();
                $table->boolean('own_car')->default(false);
                $table->boolean('profile_completed')->default(false);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['user_id']);
                $table->index(['province']);
                $table->index(['speciality']);
                $table->index(['gender']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_seekers');
    }
};

