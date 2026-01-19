<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_to_user_id');
    }

    public function createdLeads()
    {
        return $this->hasMany(Lead::class, 'created_by_user_id');
    }

    public function calls()
    {
        return $this->hasMany(Call::class);
    }

    public function sipCredential()
    {
        return $this->hasOne(AgentSipCredential::class);
    }

    public function hasPermission(string $permission): bool
    {
        // Simple permission check - can be extended
        if ($this->role === 'tenant_admin') {
            return true;
        }

        return Permission::where('tenant_id', $this->tenant_id)
            ->where('role', $this->role)
            ->where('permission', $permission)
            ->exists();
    }

    public function canAccessTenant(): bool
    {
        return $this->is_active && $this->tenant && $this->tenant->isActive();
    }
}
