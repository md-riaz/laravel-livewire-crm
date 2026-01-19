<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    use BelongsToTenant;

    const UPDATED_AT = null; // Only created_at

    protected $fillable = [
        'tenant_id',
        'lead_id',
        'user_id',
        'type',
        'payload_json',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
