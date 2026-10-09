<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hydrant_inspections', function (Blueprint $table) {
            $table->dateTime('inspected_at')->change();
        });
    }

    public function down(): void
    {
        Schema::table('hydrant_inspections', function (Blueprint $table) {
            $table->date('inspected_at')->change();
        });
    }
};
