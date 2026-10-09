<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Drop FK yang bergantung pada uq_hydrant_periode
        DB::statement('ALTER TABLE hydrant_inspections DROP FOREIGN KEY hydrant_inspections_hydrant_id_foreign');
        // Drop unique index
        DB::statement('ALTER TABLE hydrant_inspections DROP INDEX uq_hydrant_periode');
        // Buat index biasa untuk hydrant_id agar FK bisa dibuat ulang
        DB::statement('ALTER TABLE hydrant_inspections ADD INDEX hydrant_inspections_hydrant_id_index (hydrant_id)');
        // Recreate FK
        DB::statement('ALTER TABLE hydrant_inspections ADD CONSTRAINT hydrant_inspections_hydrant_id_foreign FOREIGN KEY (hydrant_id) REFERENCES hydrants (id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE hydrant_inspections DROP FOREIGN KEY hydrant_inspections_hydrant_id_foreign');
        DB::statement('ALTER TABLE hydrant_inspections DROP INDEX hydrant_inspections_hydrant_id_index');
        DB::statement('ALTER TABLE hydrant_inspections ADD UNIQUE KEY uq_hydrant_periode (hydrant_id, periode)');
        DB::statement('ALTER TABLE hydrant_inspections ADD CONSTRAINT hydrant_inspections_hydrant_id_foreign FOREIGN KEY (hydrant_id) REFERENCES hydrants (id) ON DELETE CASCADE');
    }
};
