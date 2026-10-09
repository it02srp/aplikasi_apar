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
        Schema::create('hydrant_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hydrant_id')->constrained('hydrants')->cascadeOnDelete();
            $table->date('maintenance_date');
            $table->enum('maintenance_type', [
                'Inspeksi Rutin',
                'Penggantian Selang',
                'Penggantian Nozzle',
                'Penggantian Kopling',
                'Perbaikan Valve',
                'Perbaikan Pompa',
                'Perbaikan',
                'Lainnya',
            ]);
            $table->string('technician')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hydrant_maintenances');
    }
};
