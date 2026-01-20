<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'color',
        'sort_order',
        'is_default',
        'is_closed',
        'is_won',
        'is_lost',
        'requires_note',
        'requires_followup_date',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_closed' => 'boolean',
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
        'requires_note' => 'boolean',
        'requires_followup_date' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
