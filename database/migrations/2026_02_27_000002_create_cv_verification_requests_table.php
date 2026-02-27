<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cv_verification_requests')) {
            return;
        }

        if (! Schema::hasTable('job_seekers')) {
            return;
        }

        Schema::create('cv_verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_seeker_id')->constrained('job_seekers')->cascadeOnDelete();

            // Snapshot of CV path at time of request
            $table->string('cv_file', 1024)->nullable();

            // pending | approved | rejected
            $table->string('status', 30)->default('pending');

            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();

            $table->index(['job_seeker_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_verification_requests');
    }
};

