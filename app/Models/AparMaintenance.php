<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AparMaintenance extends Model
{
    protected $fillable = [
        'apar_id',
        'maintenance_date',
        'maintenance_type',
        'technician',
        'notes',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
        ];
    }

    public function apar()
    {
        return $this->belongsTo(Apar::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
