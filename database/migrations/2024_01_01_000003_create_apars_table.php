<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apars', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('location');
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->string('room')->nullable();
            $table->enum('type', ['CO2', 'Dry Powder', 'Foam', 'Water', 'Clean Agent']);
            $table->decimal('capacity', 8, 2);
            $table->enum('capacity_unit', ['kg', 'liter']);
            $table->date('manufacture_date');
            $table->date('expiry_date');
            $table->date('last_inspection_date')->nullable();
            $table->date('next_inspection_date')->nullable();
            $table->enum('condition', ['Good', 'Needs Attention', 'Replace'])->default('Good');
            $table->string('responsible_person')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apars');
    }
};
