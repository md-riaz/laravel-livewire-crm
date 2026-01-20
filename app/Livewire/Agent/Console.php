<?php

namespace App\Livewire\Agent;

use App\Models\AgentSipCredential;
use App\Models\AuditLog;
use App\Models\Call;
use App\Models\Lead;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class Console extends Component
{
    public $agentStatus = 'offline';
    public $sipRegistered = false;
    public $activeCall = null;
    public $callTimer = 0;
    public $isMuted = false;
    public $isOnHold = false;
    public $recentCalls = [];
    public $sipCredentials = null;
    public $hasCredentials = false;
    public $dialNumber = '';

    public function mount(): void
    {
        $this->loadSipCredentials();
        $this->loadRecentCalls();
    }

    public function loadSipCredentials(): void
    {
        $credential = auth()->user()->sipCredential;
        
        if ($credential) {
            $this->hasCredentials = true;
            $this->sipCredentials = [
                'sip_ws_url' => $credential->sip_ws_url,
                'sip_username' => $credential->sip_username,
                'sip_domain' => $credential->sip_domain,
                'display_name' => $credential->display_name,
                'auto_register' => $credential->auto_register,
            ];
        }
    }

    public function getSipPassword(): string
    {
        $credential = auth()->user()->sipCredential;
        
        if (!$credential) {
            return '';
        }

        return $credential->sip_password;
    }

    public function loadRecentCalls(): void
    {
        $this->recentCalls = Call::query()
            ->where('user_id', auth()->id())
            ->with(['disposition', 'related'])
            ->orderBy('started_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($call) {
                return [
                    'id' => $call->id,
                    'direction' => $call->direction,
                    'from_number' => $call->from_number,
                    'to_number' => $call->to_number,
                    'started_at' => $call->started_at?->format('M d, H:i'),
                    'duration_seconds' => $call->duration_seconds,
                    'disposition' => $call->disposition?->name,
                    'related_type' => $call->related_type,
                    'related_id' => $call->related_id,
                ];
            })
            ->toArray();
    }

    public function changeStatus(string $status): void
    {
        if (!in_array($status, ['offline', 'available', 'away'])) {
            $this->addError('status', 'Invalid status');
            return;
        }

        $this->agentStatus = $status;

        AuditLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'agent.status_changed',
            'auditable_type' => 'App\Models\User',
            'auditable_id' => auth()->id(),
            'metadata' => ['status' => $status],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if ($status === 'available' && $this->hasCredentials) {
            $this->dispatch('registerSip');
        } elseif (in_array($status, ['offline', 'away'])) {
            $this->dispatch('unregisterSip');
        }
    }

    #[On('sipRegistered')]
    public function onSipRegistered(): void
    {
        $this->sipRegistered = true;
        
        AuditLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'sip.registered',
            'auditable_type' => 'App\Models\AgentSipCredential',
            'auditable_id' => auth()->user()->sipCredential->id,
            'metadata' => [],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    #[On('sipUnregistered')]
    public function onSipUnregistered(): void
    {
        $this->sipRegistered = false;
        
        if (auth()->user()->sipCredential) {
            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'sip.unregistered',
                'auditable_type' => 'App\Models\AgentSipCredential',
                'auditable_id' => auth()->user()->sipCredential->id,
                'metadata' => [],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    #[On('sipRegistrationFailed')]
    public function onSipRegistrationFailed(array $error): void
    {
        $this->sipRegistered = false;
        $this->addError('sip', 'SIP Registration failed: ' . ($error['message'] ?? 'Unknown error'));
        
        if (auth()->user()->sipCredential) {
            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'sip.registration_failed',
                'auditable_type' => 'App\Models\AgentSipCredential',
                'auditable_id' => auth()->user()->sipCredential->id,
                'metadata' => ['error' => $error],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    #[On('callStarted')]
    public function onCallStarted(array $callData): void
    {
        $phoneNumber = $callData['direction'] === 'outbound' 
            ? $callData['to_number'] 
            : $callData['from_number'];

        $lead = Lead::where('tenant_id', auth()->user()->tenant_id)
            ->where(function ($query) use ($phoneNumber) {
                $query->where('phone', $phoneNumber)
                    ->orWhere('mobile', $phoneNumber)
                    ->orWhere('work_phone', $phoneNumber);
            })
            ->first();

        $call = Call::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'related_type' => $lead ? Lead::class : null,
            'related_id' => $lead?->id,
            'direction' => $callData['direction'],
            'from_number' => $callData['from_number'],
            'to_number' => $callData['to_number'],
            'started_at' => now(),
            'pbx_call_id' => $callData['pbx_call_id'] ?? null,
        ]);

        $this->activeCall = [
            'id' => $call->id,
            'direction' => $call->direction,
            'from_number' => $call->from_number,
            'to_number' => $call->to_number,
            'started_at' => $call->started_at->format('Y-m-d H:i:s'),
            'lead_id' => $lead?->id,
            'lead_name' => $lead?->name,
        ];

        $this->callTimer = 0;

        AuditLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'call.started',
            'auditable_type' => Call::class,
            'auditable_id' => $call->id,
            'metadata' => $callData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    #[On('callAnswered')]
    public function onCallAnswered(): void
    {
        if ($this->activeCall) {
            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'call.answered',
                'auditable_type' => Call::class,
                'auditable_id' => $this->activeCall['id'],
                'metadata' => [],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    #[On('callEnded')]
    public function onCallEnded(array $endData): void
    {
        if ($this->activeCall) {
            $call = Call::find($this->activeCall['id']);
            
            if ($call) {
                $call->update([
                    'ended_at' => now(),
                    'duration_seconds' => $endData['duration_seconds'] ?? 0,
                ]);

                AuditLog::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'user_id' => auth()->id(),
                    'action' => 'call.ended',
                    'auditable_type' => Call::class,
                    'auditable_id' => $call->id,
                    'metadata' => $endData,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                $this->dispatch('showCallWrapUpModal', callId: $call->id);
            }
        }

        $this->activeCall = null;
        $this->callTimer = 0;
        $this->isMuted = false;
        $this->isOnHold = false;
        $this->loadRecentCalls();
    }

    #[On('callFailed')]
    public function onCallFailed(array $error): void
    {
        $this->addError('call', 'Call failed: ' . ($error['message'] ?? 'Unknown error'));
        
        if ($this->activeCall) {
            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'call.failed',
                'auditable_type' => Call::class,
                'auditable_id' => $this->activeCall['id'],
                'metadata' => ['error' => $error],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        $this->activeCall = null;
        $this->callTimer = 0;
        $this->isMuted = false;
        $this->isOnHold = false;
    }

    #[On('callTimerUpdate')]
    public function updateCallTimer(int $seconds): void
    {
        $this->callTimer = $seconds;
    }

    public function makeCall(): void
    {
        if (empty($this->dialNumber)) {
            $this->addError('dialNumber', 'Please enter a phone number');
            return;
        }

        if (!$this->sipRegistered) {
            $this->addError('sip', 'SIP not registered. Please set status to Available.');
            return;
        }

        if ($this->activeCall) {
            $this->addError('call', 'Cannot make call while another call is active');
            return;
        }

        $number = preg_replace('/[^0-9+]/', '', $this->dialNumber);
        
        if (empty($number)) {
            $this->addError('dialNumber', 'Invalid phone number');
            return;
        }

        $this->dispatch('makeOutboundCall', number: $number);
        
        $this->dialNumber = '';
    }

    public function answerCall(): void
    {
        $this->dispatch('answerInboundCall');
    }

    public function hangupCall(): void
    {
        $this->dispatch('hangupCall');
    }

    public function toggleMute(): void
    {
        $this->isMuted = !$this->isMuted;
        $this->dispatch('toggleMute', muted: $this->isMuted);
    }

    public function toggleHold(): void
    {
        $this->isOnHold = !$this->isOnHold;
        $this->dispatch('toggleHold', held: $this->isOnHold);
    }

    public function dialPadInput(string $digit): void
    {
        if (strlen($this->dialNumber) < 20) {
            $this->dialNumber .= $digit;
        }

        if ($this->activeCall) {
            $this->dispatch('sendDtmf', digit: $digit);
        }
    }

    public function clearDialNumber(): void
    {
        $this->dialNumber = '';
    }

    #[On('callWrappedUp')]
    public function onCallWrappedUp(): void
    {
        $this->loadRecentCalls();
    }

    public function render()
    {
        return view('livewire.agent.console')
            ->layout('components.layouts.app', ['title' => 'Agent Console']);
    }
}
