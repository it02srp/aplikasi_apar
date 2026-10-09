<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Hydrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'location',
        'hose_length',
        'condition',
        'responsible_person',
        'notes',
    ];

    public function inspections(): HasMany
    {
        return $this->hasMany(HydrantInspection::class)->orderByDesc('inspected_at')->orderByDesc('id');
    }

    public function latestInspection(): HasOne
    {
        return $this->hasOne(HydrantInspection::class)->latestOfMany('inspected_at');
    }

    public static function generateCode(): string
    {
        $last = static::orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->code, 4)) + 1 : 1;
        return 'HYD-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function getStatusAttribute(): string
    {
        $latest = $this->latestInspection;
        if (!$latest) return 'no_inspection';
        
        return match($this->condition) {
            'Good'           => 'good',
            'Needs Attention'=> 'needs_attention',
            'Damaged'        => 'damaged',
            default          => 'good',
        };
    }
}
