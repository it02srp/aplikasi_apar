<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HydrantInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'hydrant_id',
        'inspected_by',
        'periode',
        'inspected_at',
        'item_01_kondisi_box',
        'item_02_akses_bebas',
        'item_03_nozzle',
        'item_04_selang',
        'item_05_valve',
        'item_06_coupling',
        'item_07_kunci',
        'item_08_pillar',
        'item_09_tekanan',
        'item_10_hose_rack',
        'item_11_pompa',
        'notes',
    ];

    protected $casts = [
        'inspected_at' => 'date',
    ];

    public function isAllOk(): bool
    {
        return collect([
            $this->item_01_kondisi_box,
            $this->item_02_akses_bebas,
            $this->item_03_nozzle,
            $this->item_04_selang,
            $this->item_05_valve,
            $this->item_06_coupling,
            $this->item_07_kunci,
            $this->item_08_pillar,
            $this->item_09_tekanan,
            $this->item_10_hose_rack,
            $this->item_11_pompa,
        ])->every(fn($v) => $v === 'OK');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function hydrant(): BelongsTo
    {
        return $this->belongsTo(Hydrant::class);
    }
}
