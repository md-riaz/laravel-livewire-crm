<?php

namespace App\Livewire\Settings;

use App\Models\AgentSipCredential;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SipCredentials extends Component
{
    public $selectedUserId;
    public $sipWsUrl = '';
    public $sipUsername = '';
    public $sipPassword = '';
    public $sipDomain = '';
    public $displayName = '';
    public $autoRegister = true;
    public $users = [];
    public $isTenantAdmin = false;

    protected function rules(): array
    {
        return [
            'sipWsUrl' => 'required|url|starts_with:wss://',
            'sipUsername' => 'required|string|max:255',
            'sipPassword' => 'required|string|min:6|max:255',
            'sipDomain' => 'required|string|max:255',
            'displayName' => 'required|string|max:255',
            'autoRegister' => 'boolean',
        ];
    }

    protected $messages = [
        'sipWsUrl.starts_with' => 'The SIP WebSocket URL must start with wss://',
        'sipPassword.min' => 'The SIP password must be at least 6 characters.',
    ];

    public function mount(): void
    {
        $this->isTenantAdmin = auth()->user()->role === 'tenant_admin';
        
        if ($this->isTenantAdmin) {
            $this->loadUsers();
            $this->selectedUserId = auth()->id();
        } else {
            $this->selectedUserId = auth()->id();
        }

        $this->loadCredentials();
    }

    public function loadUsers(): void
    {
        $this->users = User::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ])
            ->toArray();
    }

    public function updatedSelectedUserId(): void
    {
        $this->loadCredentials();
    }

    public function loadCredentials(): void
    {
        if (!$this->selectedUserId) {
            return;
        }

        // Verify user belongs to same tenant
        $user = User::where('id', $this->selectedUserId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$user) {
            $this->selectedUserId = auth()->id();
            return;
        }

        $credential = AgentSipCredential::where('user_id', $this->selectedUserId)->first();

        if ($credential) {
            $this->sipWsUrl = $credential->sip_ws_url ?? '';
            $this->sipUsername = $credential->sip_username ?? '';
            $this->sipPassword = $credential->sip_password ?? '';
            $this->sipDomain = $credential->sip_domain ?? '';
            $this->displayName = $credential->display_name ?? '';
            $this->autoRegister = $credential->auto_register ?? true;
        } else {
            $this->resetForm();
        }
    }

    public function save(): void
    {
        // Verify permission
        if (!$this->isTenantAdmin && $this->selectedUserId != auth()->id()) {
            $this->addError('general', 'You do not have permission to edit other users\' credentials.');
            return;
        }

        $this->validate();

        try {
            DB::beginTransaction();

            $credential = AgentSipCredential::updateOrCreate(
                [
                    'user_id' => $this->selectedUserId,
                ],
                [
                    'tenant_id' => auth()->user()->tenant_id,
                    'sip_ws_url' => $this->sipWsUrl,
                    'sip_username' => $this->sipUsername,
                    'sip_password' => $this->sipPassword,
                    'sip_domain' => $this->sipDomain,
                    'display_name' => $this->displayName,
                    'auto_register' => $this->autoRegister,
                ]
            );

            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'sip_credentials.updated',
                'auditable_type' => AgentSipCredential::class,
                'auditable_id' => $credential->id,
                'metadata' => [
                    'target_user_id' => $this->selectedUserId,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            session()->flash('success', 'SIP credentials saved successfully.');
            $this->dispatch('credentialsSaved');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to save credentials: ' . $e->getMessage());
        }
    }

    public function resetForm(): void
    {
        $this->sipWsUrl = '';
        $this->sipUsername = '';
        $this->sipPassword = '';
        $this->sipDomain = '';
        $this->displayName = '';
        $this->autoRegister = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.settings.sip-credentials')
            ->layout('layouts.settings', ['title' => 'SIP Credentials']);
    }
}
