<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('applications')) {
            Schema::create('applications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_id');
                $table->unsignedBigInteger('job_seeker_id');
                $table->string('cv_file', 255)->nullable();
                $table->decimal('matching_percentage', 5, 2)->nullable();
                $table->timestamp('applied_at')->useCurrent();
                $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
                $table->foreign('job_seeker_id')->references('id')->on('job_seekers')->onDelete('cascade');
                $table->unique(['job_id', 'job_seeker_id']);
                $table->index(['job_id']);
                $table->index(['job_seeker_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};

