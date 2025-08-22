<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('title', 200);
                $table->text('description');
                $table->text('requirements')->nullable();
                $table->string('province', 100);
                $table->enum('status', ['draft','open','closed','paused'])->default('draft');
                $table->boolean('approved_by_admin')->default(false);
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                $table->index(['province']);
                $table->index(['status']);
                $table->index(['approved_by_admin']);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};

