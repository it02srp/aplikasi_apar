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
        Schema::create('hydrants', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('location', 255);
            $table->unsignedTinyInteger('hose_length');
            $table->enum('condition', ['Good', 'Needs Attention', 'Damaged'])->default('Good');
            $table->string('responsible_person', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hydrants');
    }
};
