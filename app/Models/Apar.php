<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Apar extends Model
{
    protected $fillable = [
        'code',
        'location',
        'building',
        'floor',
        'room',
        'type',
        'capacity',
        'capacity_unit',
        'manufacture_date',
        'expiry_date',
        'last_inspection_date',
        'next_inspection_date',
        'condition',
        'responsible_person',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'manufacture_date'     => 'date',
            'expiry_date'          => 'date',
            'last_inspection_date' => 'date',
            'next_inspection_date' => 'date',
        ];
    }

    public function getStatusAttribute(): string
    {
        $today = Carbon::today();
        if ($this->expiry_date->lt($today)) {
            return 'expired';
        }
        if ($this->expiry_date->diffInDays($today, false) >= -30) {
            return 'near_expiry';
        }
        return 'good';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->lt(Carbon::today());
    }

    public function isNearExpiry(): bool
    {
        $today = Carbon::today();
        return !$this->isExpired() && $this->expiry_date->lte($today->copy()->addDays(30));
    }

    public function isGood(): bool
    {
        return !$this->isExpired() && !$this->isNearExpiry();
    }

    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->first();
        if (!$last) {
            return 'APAR-001';
        }
        preg_match('/(\d+)$/', $last->code, $matches);
        $next = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        return 'APAR-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
