<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apar_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apar_id')->constrained('apars')->cascadeOnDelete();
            $table->date('maintenance_date');
            $table->enum('maintenance_type', [
                'Inspeksi Rutin',
                'Pengisian Ulang',
                'Penggantian Komponen',
                'Perbaikan',
                'Lainnya',
            ]);
            $table->string('technician')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apar_maintenances');
    }
};
