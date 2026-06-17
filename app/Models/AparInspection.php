<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AparInspection extends Model
{
    protected $fillable = [
        'apar_id',
        'inspected_at',
        'inspected_by',
        'kondisi_handle',
        'kondisi_selang',
        'kondisi_pin_kunci',
        'kondisi_indikator',
        'kondisi_tabung',
        'kondisi_masa_apar',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'inspected_at' => 'datetime',
        ];
    }

    public function apar()
    {
        return $this->belongsTo(Apar::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function isAllOk(): bool
    {
        return collect([
            $this->kondisi_handle,
            $this->kondisi_selang,
            $this->kondisi_pin_kunci,
            $this->kondisi_indikator,
            $this->kondisi_tabung,
            $this->kondisi_masa_apar,
        ])->filter()->every(fn($v) => $v === 'OK');
    }
}
