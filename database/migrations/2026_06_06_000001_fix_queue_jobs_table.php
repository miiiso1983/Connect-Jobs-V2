<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('queue.connections.database.table', 'queue_jobs');

        // The queue table only holds transient pending jobs, so it is safe to
        // rebuild it with the correct schema if it was created incorrectly.
        Schema::dropIfExists($table);

        Schema::create($table, function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    public function down(): void
    {
        // No-op: keep the corrected queue table in place.
    }
};
