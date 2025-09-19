<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('scope', 50)->default('company'); // company, jobseeker, admin
                $table->string('key')->nullable(); // optional machine key
                $table->string('name'); // display name in UI
                $table->string('subject', 200);
                $table->text('body');
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};

