<?php

namespace App\Livewire\Auth;

use App\Services\TenantService;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class CompanyRegistration extends Component
{
    #[Validate('required|min:2')]
    public $company_name = '';

    #[Validate('required|min:2')]
    public $owner_name = '';

    #[Validate('required|email|unique:users,email')]
    public $owner_email = '';

    #[Validate('required|min:8')]
    public $password = '';

    #[Validate('required|same:password')]
    public $password_confirmation = '';

    #[Validate('required|timezone')]
    public $timezone = 'UTC';

    public function mount()
    {
        // Try to guess timezone from browser (can be enhanced with JS)
        $this->timezone = config('app.timezone', 'UTC');
    }

    public function register(TenantService $tenantService)
    {
        $this->validate();

        $result = $tenantService->createTenantWithOwner([
            'company_name' => $this->company_name,
            'owner_name' => $this->owner_name,
            'owner_email' => $this->owner_email,
            'password' => $this->password,
            'timezone' => $this->timezone,
        ]);

        // Log the owner in
        Auth::login($result['user']);

        session()->flash('success', 'Company registered successfully!');

        // Redirect to dashboard
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.company-registration')->layout('components.layouts.guest');
    }
}
