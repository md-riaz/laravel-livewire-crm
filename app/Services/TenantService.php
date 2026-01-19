<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Models\LeadStatus;
use App\Models\CallDisposition;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantService
{
    public function createTenantWithOwner(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Create tenant
            $tenant = Tenant::create([
                'name' => $data['company_name'],
                'status' => 'active',
                'timezone' => $data['timezone'] ?? 'UTC',
            ]);

            // 2. Create owner user
            $owner = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'password' => Hash::make($data['password']),
                'role' => 'tenant_admin',
                'is_active' => true,
            ]);

            // 3. Seed default lead statuses
            $this->seedDefaultLeadStatuses($tenant->id);

            // 4. Seed default call dispositions
            $this->seedDefaultCallDispositions($tenant->id);

            // 5. Grant default permissions
            $this->seedDefaultPermissions($tenant->id);

            return [
                'tenant' => $tenant,
                'user' => $owner,
            ];
        });
    }

    protected function seedDefaultLeadStatuses(int $tenantId): void
    {
        $statuses = [
            ['name' => 'New', 'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true],
            ['name' => 'Contacted', 'color' => '#8B5CF6', 'sort_order' => 2],
            ['name' => 'Qualified', 'color' => '#10B981', 'sort_order' => 3],
            ['name' => 'Proposal Sent', 'color' => '#F59E0B', 'sort_order' => 4],
            ['name' => 'Won', 'color' => '#22C55E', 'sort_order' => 5, 'is_won' => true, 'is_closed' => true],
            ['name' => 'Lost', 'color' => '#EF4444', 'sort_order' => 6, 'is_lost' => true, 'is_closed' => true],
        ];

        foreach ($statuses as $status) {
            LeadStatus::create(array_merge(['tenant_id' => $tenantId], $status));
        }
    }

    protected function seedDefaultCallDispositions(int $tenantId): void
    {
        $dispositions = [
            ['name' => 'Answered', 'sort_order' => 1, 'is_default' => true],
            ['name' => 'No Answer', 'sort_order' => 2],
            ['name' => 'Voicemail', 'sort_order' => 3],
            ['name' => 'Busy', 'sort_order' => 4],
            ['name' => 'Wrong Number', 'sort_order' => 5],
            ['name' => 'Follow-up Required', 'sort_order' => 6, 'requires_note' => true],
        ];

        foreach ($dispositions as $disposition) {
            CallDisposition::create(array_merge(['tenant_id' => $tenantId], $disposition));
        }
    }

    protected function seedDefaultPermissions(int $tenantId): void
    {
        // Grant permissions to sales_agent role
        $salesPermissions = [
            'leads.view',
            'leads.manage',
            'calls.make',
            'calls.view',
        ];

        foreach ($salesPermissions as $permission) {
            Permission::grantPermission($tenantId, 'sales_agent', $permission);
        }

        // supervisor gets additional permissions
        $supervisorPermissions = array_merge($salesPermissions, [
            'calls.recordings.view',
            'users.view',
        ]);

        foreach ($supervisorPermissions as $permission) {
            Permission::grantPermission($tenantId, 'supervisor', $permission);
        }
    }
}
