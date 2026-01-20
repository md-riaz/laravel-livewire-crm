<?php

namespace App\Livewire\Settings;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UsersManagement extends Component
{
    public $users = [];
    public $showModal = false;
    public $editingId = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = 'agent';
    public $isActive = true;
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
    }

    public function loadUsers(): void
    {
        $this->users = User::where('tenant_id', auth()->user()->tenant_id)
            ->withCount(['sipCredential', 'assignedLeads', 'calls'])
            ->orderBy('name')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'has_sip_credentials' => $user->sip_credential_count > 0,
                'assigned_leads_count' => $user->assigned_leads_count,
                'calls_count' => $user->calls_count,
            ])
            ->toArray();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
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
