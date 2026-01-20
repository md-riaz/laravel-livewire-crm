<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\LeadStatus;
use App\Models\CallDisposition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Auth\Register;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_accessible(): void
    {
        $this->withoutVite();
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_company_can_be_registered(): void
    {
        Livewire::test(Register::class)
            ->set('company_name', 'Test Company')
            ->set('owner_name', 'John Doe')
            ->set('owner_email', 'john@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('timezone', 'America/New_York')
            ->call('register')
            ->assertRedirect(route('dashboard'));

        // Assert tenant was created
        $this->assertDatabaseHas('tenants', [
            'name' => 'Test Company',
            'status' => 'active',
        ]);

        // Assert owner was created
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'tenant_admin',
        ]);

        // Assert user is authenticated
        $this->assertAuthenticated();
    }

    public function test_default_lead_statuses_are_seeded(): void
    {
        Livewire::test(Register::class)
            ->set('company_name', 'Test Company')
            ->set('owner_name', 'John Doe')
            ->set('owner_email', 'john@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $tenant = Tenant::first();
        
        // Assert default lead statuses were created
        $this->assertTrue($tenant->leadStatuses()->count() >= 6);
        $this->assertTrue($tenant->leadStatuses()->where('is_default', true)->exists());
    }

    public function test_default_call_dispositions_are_seeded(): void
    {
        Livewire::test(Register::class)
            ->set('company_name', 'Test Company')
            ->set('owner_name', 'John Doe')
            ->set('owner_email', 'john@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $tenant = Tenant::first();
        
        // Assert default call dispositions were created
        $this->assertTrue($tenant->callDispositions()->count() >= 6);
    }

    public function test_validation_errors_are_shown(): void
    {
        Livewire::test(Register::class)
            ->set('company_name', '')
            ->set('owner_email', 'invalid-email')
            ->call('register')
            ->assertHasErrors(['company_name', 'owner_email']);
    }
}
