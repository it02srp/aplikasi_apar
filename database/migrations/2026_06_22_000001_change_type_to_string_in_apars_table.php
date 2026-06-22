<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apars', function (Blueprint $table) {
            $table->string('type', 100)->change();
        });

        // Normalisasi data lama: huruf pertama tiap kata kapital
        DB::table('apars')->get()->each(function ($apar) {
            DB::table('apars')
                ->where('id', $apar->id)
                ->update(['type' => ucwords(strtolower(trim($apar->type)))]);
        });
    }

    public function down(): void
    {
        Schema::table('apars', function (Blueprint $table) {
            $table->enum('type', ['CO2', 'Dry Powder', 'Foam', 'Water', 'Clean Agent'])->change();
        });
    }
};
