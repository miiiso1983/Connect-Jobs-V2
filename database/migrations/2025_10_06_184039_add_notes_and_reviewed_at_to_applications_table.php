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
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (!Schema::hasColumn('applications', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('notes');
            }
            // Update status enum to include more statuses
            if (Schema::hasColumn('applications', 'status')) {
                $table->dropColumn('status');
            }
            $table->enum('status', ['pending', 'reviewed', 'shortlisted', 'rejected', 'hired'])->default('pending')->after('applied_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('applications', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
        });
    }
};
