<?php

namespace App\Livewire\Settings;

use App\Mail\UserInvitationMail;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class UsersManagement extends Component
{
    public $users = [];
    public $invitations = [];
    public $showModal = false;
    public $showInviteModal = false;
    public $editingId = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = 'agent';
    public $isActive = true;
    public $inviteEmail = '';
    public $inviteRole = 'agent';
    public $roles = [
        'agent' => 'Agent',
        'supervisor' => 'Supervisor',
        'tenant_admin' => 'Tenant Admin',
    ];

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:agent,supervisor,tenant_admin',
            'isActive' => 'boolean',
        ];

        if ($this->editingId) {
            $rules['email'] = 'required|email|max:255|unique:users,email,' . $this->editingId . ',id,tenant_id,' . auth()->user()->tenant_id;
            $rules['password'] = 'nullable|string|min:8|max:255';
        } else {
            $rules['email'] = 'required|email|max:255|unique:users,email,NULL,id,tenant_id,' . auth()->user()->tenant_id;
            $rules['password'] = 'required|string|min:8|max:255';
        }

        return $rules;
    }

    public function mount(): void
    {
        $this->loadUsers();
        $this->loadInvitations();
    }

    public function loadUsers(): void
    {
        $this->users = User::where('tenant_id', auth()->user()->tenant_id)
            ->with('sipCredential')
            ->withCount(['assignedLeads', 'calls'])
            ->orderBy('name')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'has_sip_credentials' => $user->sipCredential !== null,
                'assigned_leads_count' => $user->assigned_leads_count,
                'calls_count' => $user->calls_count,
            ])
            ->toArray();
    }

    public function loadInvitations(): void
    {
        $this->invitations = UserInvitation::where('tenant_id', auth()->user()->tenant_id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->with('invitedBy')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($invitation) => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'invited_by' => $invitation->invitedBy->name,
                'expires_at' => $invitation->expires_at->format('M j, Y g:i A'),
                'expires_at_raw' => $invitation->expires_at,
            ])
            ->toArray();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openInviteModal(): void
    {
        $this->resetInviteForm();
        $this->showInviteModal = true;
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
        $this->resetInviteForm();
    }

    private function resetInviteForm(): void
    {
        $this->inviteEmail = '';
        $this->inviteRole = 'agent';
        $this->resetValidation();
    }

    public function sendInvite(): void
    {
        $this->validate([
            'inviteEmail' => 'required|email|max:255',
            'inviteRole' => 'required|in:agent,supervisor,tenant_admin',
        ]);

        try {
            DB::beginTransaction();

            $tenantId = auth()->user()->tenant_id;

            // Check if user already exists
            $existingUser = User::where('tenant_id', $tenantId)
                ->where('email', $this->inviteEmail)
                ->exists();

            if ($existingUser) {
                $this->addError('inviteEmail', 'A user with this email already exists.');
                DB::rollBack();
                return;
            }

            // Check if there's already a pending invitation
            $existingInvitation = UserInvitation::where('tenant_id', $tenantId)
                ->where('email', $this->inviteEmail)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->first();

            if ($existingInvitation) {
                $this->addError('inviteEmail', 'A pending invitation already exists for this email.');
                DB::rollBack();
                return;
            }

            // Generate token
            $plainToken = UserInvitation::generateToken();

            // Create invitation
            $invitation = UserInvitation::create([
                'tenant_id' => $tenantId,
                'email' => $this->inviteEmail,
                'token' => Hash::make($plainToken),
                'role' => $this->inviteRole,
                'invited_by_user_id' => auth()->id(),
                'expires_at' => now()->addDays(7),
            ]);

            // Send invitation email
            Mail::to($this->inviteEmail)->send(new UserInvitationMail(
                $invitation,
                $plainToken,
                auth()->user()->name,
                auth()->user()->tenant->name
            ));

            // Log action
            AuditLog::create([
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'action' => 'user.invited',
                'auditable_type' => UserInvitation::class,
                'auditable_id' => $invitation->id,
                'metadata' => [
                    'email' => $this->inviteEmail,
                    'role' => $this->inviteRole,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            $this->loadInvitations();
            $this->closeInviteModal();
            session()->flash('success', 'Invitation sent successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to send invitation: ' . $e->getMessage());
        }
    }

    public function resendInvitation(int $id): void
    {
        try {
            DB::beginTransaction();

            $invitation = UserInvitation::where('tenant_id', auth()->user()->tenant_id)
                ->whereNull('accepted_at')
                ->findOrFail($id);

            // Generate new token
            $plainToken = UserInvitation::generateToken();
            $invitation->token = Hash::make($plainToken);
            $invitation->expires_at = now()->addDays(7);
            $invitation->save();

            // Resend email
            Mail::to($invitation->email)->send(new UserInvitationMail(
                $invitation,
                $plainToken,
                auth()->user()->name,
                auth()->user()->tenant->name
            ));

            // Log action
            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'user.invitation_resent',
                'auditable_type' => UserInvitation::class,
                'auditable_id' => $invitation->id,
                'metadata' => ['email' => $invitation->email],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            $this->loadInvitations();
            session()->flash('success', 'Invitation resent successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to resend invitation: ' . $e->getMessage());
        }
    }

    public function revokeInvitation(int $id): void
    {
        try {
            DB::beginTransaction();

            $invitation = UserInvitation::where('tenant_id', auth()->user()->tenant_id)
                ->whereNull('accepted_at')
                ->findOrFail($id);

            // Log action before deletion
            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'user.invitation_revoked',
                'auditable_type' => UserInvitation::class,
                'auditable_id' => $invitation->id,
                'metadata' => ['email' => $invitation->email],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $invitation->delete();

            DB::commit();

            $this->loadInvitations();
            session()->flash('success', 'Invitation revoked successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to revoke invitation: ' . $e->getMessage());
        }
    }

    public function edit(int $id): void
    {
        $user = User::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);
        
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->isActive = $user->is_active;
        $this->password = '';
        
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'is_active' => $this->isActive,
                'tenant_id' => auth()->user()->tenant_id,
            ];

            if ($this->editingId) {
                $user = User::where('tenant_id', auth()->user()->tenant_id)
                    ->findOrFail($this->editingId);
                
                if (!empty($this->password)) {
                    $data['password'] = Hash::make($this->password);
                }
                
                $user->update($data);
                $action = 'user.updated';
            } else {
                $data['password'] = Hash::make($this->password);
                $user = User::create($data);
                $action = 'user.created';
            }

            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => $action,
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'metadata' => [
                    'name' => $this->name,
                    'email' => $this->email,
                    'role' => $this->role,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            $this->loadUsers();
            $this->closeModal();
            session()->flash('success', 'User saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to save user: ' . $e->getMessage());
        }
    }

    public function toggleActive(int $id): void
    {
        try {
            DB::beginTransaction();

            $user = User::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            // Prevent deactivating yourself
            if ($user->id === auth()->id()) {
                $this->addError('general', 'You cannot deactivate your own account.');
                return;
            }

            $user->update(['is_active' => !$user->is_active]);

            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => $user->is_active ? 'user.activated' : 'user.deactivated',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'metadata' => ['name' => $user->name],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            $this->loadUsers();
            session()->flash('success', 'User ' . ($user->is_active ? 'activated' : 'deactivated') . ' successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to toggle user status: ' . $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'agent';
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.settings.users-management')
            ->layout('layouts.settings', ['title' => 'Users Management']);
    }
}
