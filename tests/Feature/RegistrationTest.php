<?php

namespace Tests\Feature;

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

    public function test_registration_page_shows_invitation_message(): void
    {
        $this->withoutVite();
        $response = $this->get('/register');
        $response->assertSee('Invitation Required');
        $response->assertSee('invitation from your company administrator');
    }

    public function test_registration_component_renders(): void
    {
        Livewire::test(Register::class)
            ->assertStatus(200);
    }
}
