<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CallDisposition extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'sort_order',
        'is_default',
        'requires_note',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'requires_note' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function calls()
    {
        return $this->hasMany(Call::class, 'disposition_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
