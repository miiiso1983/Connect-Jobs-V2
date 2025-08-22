<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('company_name', 150);
                $table->string('province', 100);
                $table->string('industry', 100);
                $table->enum('subscription_plan', ['free','basic','pro','enterprise'])->default('free');
                $table->date('subscription_expiry')->nullable();
                $table->enum('status', ['active','inactive','suspended'])->default('inactive');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['user_id']);
                $table->index(['province']);
                $table->index(['industry']);
                $table->index(['status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

