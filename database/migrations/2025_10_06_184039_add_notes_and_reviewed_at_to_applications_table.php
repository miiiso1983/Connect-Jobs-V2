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
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('applications', function (Blueprint $table) use ($driver) {
            // Add columns
            if (! Schema::hasColumn('applications', 'notes')) {
                $col = $table->text('notes')->nullable();
                // Column ordering is not supported on sqlite; avoid referencing columns that may be dropped.
                if ($driver !== 'sqlite' && Schema::hasColumn('applications', 'status')) {
                    $col->after('status');
                }
            }
            if (! Schema::hasColumn('applications', 'reviewed_at')) {
                $col = $table->timestamp('reviewed_at')->nullable();
                if ($driver !== 'sqlite' && Schema::hasColumn('applications', 'notes')) {
                    $col->after('notes');
                }
            }

            // Update status to support more values.
            // IMPORTANT: drop the index first, otherwise sqlite can error when dropping/recreating the column.
            if (Schema::hasColumn('applications', 'status')) {
                try {
                    $table->dropIndex(['status']);
                } catch (\Throwable $e) {
                    // ignore if index doesn't exist
                }

                try {
                    $table->dropColumn('status');
                } catch (\Throwable $e) {
                    // ignore if column drop isn't supported in current driver/version
                }
            }

            // For sqlite, enum becomes a string-like column anyway; keep schema simple & test-friendly.
            if ($driver === 'sqlite') {
                $table->string('status')->default('pending');
            } else {
                $table->enum('status', ['pending', 'reviewed', 'shortlisted', 'rejected', 'hired'])
                    ->default('pending')
                    ->after('applied_at');
            }
            $table->index('status');
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
