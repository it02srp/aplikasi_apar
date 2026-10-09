<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hydrant_inspections', function (Blueprint $table) {
            $table->dropUnique('uq_hydrant_periode');
        });
    }

    public function down(): void
    {
        Schema::table('hydrant_inspections', function (Blueprint $table) {
            $table->unique(['hydrant_id', 'periode'], 'uq_hydrant_periode');
        });
    }
};
