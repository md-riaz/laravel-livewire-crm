<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class AcceptInvitation extends Component
{
    public ?UserInvitation $invitation = null;
    public string $token = '';
    public string $name = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $invalidInvitation = false;
    public string $errorMessage = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|max:255|confirmed',
        ];
    }

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->loadInvitation();
    }

    private function loadInvitation(): void
    {
        // NOTE: Token verification requires loading invitations from DB because tokens are bcrypt-hashed.
        // For high-traffic applications, consider using a separate unhashed token_identifier field
        // or implementing a caching layer to minimize database queries.
        $invitations = UserInvitation::with('invitedBy')
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->limit(100) // Reasonable limit for pending invitations
            ->get();

        // Verify token against hashed values
        foreach ($invitations as $inv) {
            if ($inv->verifyToken($this->token)) {
                $this->invitation = $inv;
                return;
            }
        }

        $this->invalidInvitation = true;
        $this->errorMessage = 'This invitation is invalid, expired, or has already been used.';
    }

    public function accept()
    {
        if ($this->invalidInvitation || !$this->invitation) {
            $this->addError('general', 'Invalid invitation.');
            return;
        }

        $this->validate();

        try {
            DB::beginTransaction();

            // Check if user already exists with this email
            $existingUser = User::withoutGlobalScopes()
                ->where('email', $this->invitation->email)
                ->where('tenant_id', $this->invitation->tenant_id)
                ->first();

            if ($existingUser) {
                $this->addError('general', 'A user with this email already exists.');
                DB::rollBack();
                return;
            }

            // Create the user
            $user = User::create([
                'name' => $this->name,
                'email' => $this->invitation->email,
                'password' => Hash::make($this->password),
                'tenant_id' => $this->invitation->tenant_id,
                'role' => $this->invitation->role,
                'is_active' => true,
            ]);

            // Mark invitation as accepted
            $this->invitation->markAsAccepted();

            DB::commit();

            // Log the user in
            auth()->login($user);

            // Redirect to dashboard
            $this->redirect(route('dashboard'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to accept invitation: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.accept-invitation')
            ->layout('layouts.guest');
    }
}
