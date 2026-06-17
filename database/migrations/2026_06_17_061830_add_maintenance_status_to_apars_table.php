<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apars', function (Blueprint $table) {
            $table->boolean('is_maintenance')->default(false)->after('notes');
            $table->timestamp('maintenance_started_at')->nullable()->after('is_maintenance');
        });
    }

    public function down(): void
    {
        Schema::table('apars', function (Blueprint $table) {
            $table->dropColumn(['is_maintenance', 'maintenance_started_at']);
        });
    }
};
