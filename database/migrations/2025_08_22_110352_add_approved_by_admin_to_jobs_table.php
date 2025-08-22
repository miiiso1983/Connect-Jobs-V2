<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('jobs', 'approved_by_admin')) {
                $table->boolean('approved_by_admin')->default(false)->after('status');
                $table->index('approved_by_admin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (Schema::hasColumn('jobs', 'approved_by_admin')) {
                $table->dropIndex(['approved_by_admin']);
                $table->dropColumn('approved_by_admin');
            }
        });
    }
};

