<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HydrantMaintenance extends Model
{
    protected $fillable = [
        'hydrant_id',
        'maintenance_date',
        'maintenance_type',
        'technician',
        'notes',
        'performed_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
    ];

    public static array $types = [
        'Inspeksi Rutin',
        'Penggantian Selang',
        'Penggantian Nozzle',
        'Penggantian Kopling',
        'Perbaikan Valve',
        'Perbaikan Pompa',
        'Perbaikan',
        'Lainnya',
    ];

    public function hydrant()
    {
        return $this->belongsTo(Hydrant::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
