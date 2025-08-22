<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('jobs')) {
            // If jobs table is truly missing, create a minimal version
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('title', 200)->nullable();
                $table->text('description')->nullable();
                $table->text('requirements')->nullable();
                $table->string('province', 100)->nullable();
                $table->json('districts')->nullable();
                $table->string('status', 20)->default('draft');
                $table->boolean('approved_by_admin')->default(false);
                $table->string('jd_file', 255)->nullable();
                $table->index('company_id');
                $table->index('province');
                $table->index('status');
                $table->index('approved_by_admin');
            });
            return;
        }

        Schema::table('jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('jobs', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->index('company_id');
            }
            if (! Schema::hasColumn('jobs', 'title')) {
                $table->string('title', 200)->nullable()->after('company_id');
            }
            if (! Schema::hasColumn('jobs', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (! Schema::hasColumn('jobs', 'requirements')) {
                $table->text('requirements')->nullable()->after('description');
            }
            if (! Schema::hasColumn('jobs', 'province')) {
                $table->string('province', 100)->nullable()->after('requirements');
                $table->index('province');
            }
            if (! Schema::hasColumn('jobs', 'districts')) {
                $table->json('districts')->nullable()->after('province');
            }
            if (! Schema::hasColumn('jobs', 'status')) {
                $table->string('status', 20)->default('draft')->after('districts');
                $table->index('status');
            }
            if (! Schema::hasColumn('jobs', 'approved_by_admin')) {
                $table->boolean('approved_by_admin')->default(false)->after('status');
                $table->index('approved_by_admin');
            }
            if (! Schema::hasColumn('jobs', 'jd_file')) {
                $table->string('jd_file', 255)->nullable()->after('approved_by_admin');
            }
        });
    }

    public function down(): void
    {
        // No destructive down to avoid data loss. This migration is corrective.
    }
};

