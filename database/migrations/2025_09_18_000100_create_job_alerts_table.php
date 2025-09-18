<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('q')->nullable();
            $table->string('province')->nullable();
            $table->string('industry')->nullable();
            $table->string('job_title')->nullable();
            $table->string('frequency')->default('weekly'); // weekly
            $table->string('channel')->default('email');    // email
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_alerts');
    }
};

