<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AgentSipCredential extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'sip_ws_url',
        'sip_username',
        'sip_password',
        'sip_domain',
        'display_name',
        'auto_register',
    ];

    protected $casts = [
        'auto_register' => 'boolean',
    ];

    protected $hidden = [
        'sip_password',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function sipPassword(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => decrypt($value),
            set: fn ($value) => encrypt($value),
        );
    }
}
