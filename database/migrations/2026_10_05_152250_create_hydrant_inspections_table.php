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
        Schema::create('hydrant_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hydrant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->char('periode', 7); // Format: YYYY-MM
            $table->date('inspected_at');
            
            // 11 Item Pemeriksaan (OK / NOT OK)
            $table->enum('item_01_kondisi_box', ['OK', 'NOT OK'])->default('OK');
            $table->enum('item_02_akses_bebas', ['OK', 'NOT OK'])->default('OK');
            $table->enum('item_03_nozzle', ['OK', 'NOT OK'])->default('OK');
            $table->enum('item_04_selang', ['OK', 'NOT OK'])->default('OK');
            $table->enum('item_05_valve', ['OK', 'NOT OK'])->default('OK');
            $table->enum('item_06_coupling', ['OK', 'NOT OK'])->default('OK');
            $table->enum('item_07_kunci', ['OK', 'NOT OK'])->default('OK');
            $table->enum('item_08_pillar', ['OK', 'NOT OK'])->default('OK');
            $table->enum('item_09_tekanan', ['OK', 'NOT OK'])->default('OK');
            $table->enum('item_10_hose_rack', ['OK', 'NOT OK'])->default('OK');
            $table->enum('item_11_pompa', ['OK', 'NOT OK'])->default('OK');
            
            $table->text('notes')->nullable();
            $table->timestamps();

            // Constraint: 1 hydrant diinspeksi 1 kali per periode
            $table->unique(['hydrant_id', 'periode'], 'uq_hydrant_periode');
            
            $table->index('periode');
            $table->index('inspected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hydrant_inspections');
    }
};
