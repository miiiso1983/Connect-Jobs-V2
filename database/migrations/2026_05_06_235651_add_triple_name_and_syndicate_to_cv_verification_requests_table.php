<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cv_verification_requests', function (Blueprint $table) {
            $table->string('triple_name')->nullable()->after('cv_file');
            $table->boolean('is_syndicate_member')->default(false)->after('triple_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cv_verification_requests', function (Blueprint $table) {
            $table->dropColumn(['triple_name', 'is_syndicate_member']);
        });
    }
};
