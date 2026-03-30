<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Activate all existing jobseeker users and mark their emails as verified.
     */
    public function up(): void
    {
        // Activate all jobseekers that are currently inactive
        \DB::table('users')
            ->where('role', 'jobseeker')
            ->where('status', 'inactive')
            ->update([
                'status' => 'active',
            ]);

        // Mark all unverified emails as verified
        \DB::table('users')
            ->whereNull('email_verified_at')
            ->update([
                'email_verified_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible — activating users is a one-way operation
    }
};
