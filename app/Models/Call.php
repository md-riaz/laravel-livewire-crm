<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Call extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'related_type',
        'related_id',
        'direction',
        'from_number',
        'to_number',
        'started_at',
        'ended_at',
        'duration_seconds',
        'pbx_call_id',
        'recording_url',
        'disposition_id',
        'wrapup_notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function disposition()
    {
        return $this->belongsTo(CallDisposition::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
