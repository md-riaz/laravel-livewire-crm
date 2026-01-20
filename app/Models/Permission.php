<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'role',
        'permission',
    ];

    public static function grantPermission(int $tenantId, string $role, string $permission): void
    {
        static::firstOrCreate([
            'tenant_id' => $tenantId,
            'role' => $role,
            'permission' => $permission,
        ]);
    }
}
