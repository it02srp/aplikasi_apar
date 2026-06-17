<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apar_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apar_id')->constrained()->onDelete('cascade');
            $table->timestamp('inspected_at');
            $table->foreignId('inspected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('kondisi_handle', ['OK', 'NOT OK'])->nullable();
            $table->enum('kondisi_selang', ['OK', 'NOT OK'])->nullable();
            $table->enum('kondisi_pin_kunci', ['OK', 'NOT OK'])->nullable();
            $table->enum('kondisi_indikator', ['OK', 'NOT OK'])->nullable();
            $table->enum('kondisi_tabung', ['OK', 'NOT OK'])->nullable();
            $table->enum('kondisi_masa_apar', ['OK', 'NOT OK'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apar_inspections');
    }
};
