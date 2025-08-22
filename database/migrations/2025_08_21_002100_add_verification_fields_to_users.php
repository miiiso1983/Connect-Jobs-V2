<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users','verification_code')) {
                $table->string('verification_code', 10)->nullable()->after('status');
            }
            if (!Schema::hasColumn('users','verification_expires_at')) {
                $table->dateTime('verification_expires_at')->nullable()->after('verification_code');
            }
            if (!Schema::hasColumn('users','verification_channel')) {
                $table->string('verification_channel', 20)->nullable()->after('verification_expires_at');
            }
            if (!Schema::hasColumn('users','whatsapp_number')) {
                $table->string('whatsapp_number', 30)->nullable()->after('verification_channel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['whatsapp_number','verification_channel','verification_expires_at','verification_code'] as $col) {
                if (Schema::hasColumn('users',$col)) $table->dropColumn($col);
            }
        });
    }
};

