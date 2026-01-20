<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'lead_status_id',
        'assigned_to_user_id',
        'created_by_user_id',
        'name',
        'company_name',
        'email',
        'phone',
        'source',
        'score',
        'estimated_value',
        'last_contacted_at',
        'next_followup_at',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'last_contacted_at' => 'datetime',
        'next_followup_at' => 'datetime',
    ];

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->orderBy('created_at', 'desc');
    }

    public function calls()
    {
        return $this->morphMany(Call::class, 'related');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to_user_id', $userId);
    }

    public function scopeWithScore($query, $score)
    {
        return $query->where('score', $score);
    }
}
