<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Lead;
use App\Models\LeadStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_scope_filters_leads(): void
    {
        // Create two tenants
        $tenant1 = Tenant::create(['name' => 'Tenant 1', 'status' => 'active']);
        $tenant2 = Tenant::create(['name' => 'Tenant 2', 'status' => 'active']);

        // Create users for each tenant
        $user1 = User::factory()->create(['tenant_id' => $tenant1->id, 'role' => 'sales_agent']);
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id, 'role' => 'sales_agent']);

        // Create lead statuses
        $status1 = LeadStatus::create(['tenant_id' => $tenant1->id, 'name' => 'New', 'is_default' => true]);
        $status2 = LeadStatus::create(['tenant_id' => $tenant2->id, 'name' => 'New', 'is_default' => true]);

        // Create leads for each tenant
        $lead1 = Lead::create([
            'tenant_id' => $tenant1->id,
            'lead_status_id' => $status1->id,
            'name' => 'Lead 1',
            'score' => 'warm',
        ]);

        $lead2 = Lead::create([
            'tenant_id' => $tenant2->id,
            'lead_status_id' => $status2->id,
            'name' => 'Lead 2',
            'score' => 'warm',
        ]);

        // Act as user from tenant 1
        $this->actingAs($user1);

        // Assert only tenant 1's leads are visible
        $this->assertEquals(1, Lead::count());
        $this->assertEquals('Lead 1', Lead::first()->name);

        // Act as user from tenant 2
        $this->actingAs($user2);

        // Assert only tenant 2's leads are visible
        $this->assertEquals(1, Lead::count());
        $this->assertEquals('Lead 2', Lead::first()->name);
    }

    public function test_tenant_id_is_automatically_set(): void
    {
        $tenant = Tenant::create(['name' => 'Test Tenant', 'status' => 'active']);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'sales_agent']);

        $this->actingAs($user);

        $status = LeadStatus::create(['name' => 'New', 'is_default' => true]);

        // Assert tenant_id was automatically set
        $this->assertEquals($tenant->id, $status->tenant_id);
    }

    public function test_users_cannot_access_other_tenants_data(): void
    {
        $tenant1 = Tenant::create(['name' => 'Tenant 1', 'status' => 'active']);
        $tenant2 = Tenant::create(['name' => 'Tenant 2', 'status' => 'active']);

        $user1 = User::factory()->create(['tenant_id' => $tenant1->id]);

        $status2 = LeadStatus::create(['tenant_id' => $tenant2->id, 'name' => 'New', 'is_default' => true]);

        $this->actingAs($user1);

        // Try to query lead status from another tenant
        $result = LeadStatus::find($status2->id);

        $this->assertNull($result);
    }
}
