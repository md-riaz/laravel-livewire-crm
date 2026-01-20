<?php

namespace App\Livewire\Calls;

use App\Models\Call;
use App\Models\CallDisposition;
use App\Models\Lead;
use App\Models\LeadActivity;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CallWrapUpModal extends Component
{
    public $show = false;
    public $callId = null;
    public $call = null;

    #[Validate('required|exists:call_dispositions,id')]
    public $disposition_id = '';

    public $wrapup_notes = '';

    public $schedule_followup = false;

    public $followup_date = '';

    public function getIsLeadCallProperty()
    {
        return $this->call && $this->call->related instanceof Lead;
    }

    #[On('showCallWrapUpModal')]
    public function showModal($callId)
    {
        $this->callId = $callId;
        $this->show = true;
        
        $this->call = Call::with(['user', 'disposition', 'related'])
            ->findOrFail($callId);
        
        $this->reset(['disposition_id', 'wrapup_notes', 'schedule_followup', 'followup_date']);
    }

    public function updatedDispositionId($value)
    {
        if ($value) {
            $disposition = CallDisposition::find($value);
            if ($disposition && !$disposition->requires_note) {
                $this->wrapup_notes = '';
            }
        }
    }

    public function updatedScheduleFollowup($value)
    {
        if (!$value) {
            $this->followup_date = '';
        }
    }

    public function save()
    {
        $this->validate([
            'disposition_id' => 'required|exists:call_dispositions,id',
        ]);

        $disposition = CallDisposition::findOrFail($this->disposition_id);

        $rules = [];
        
        if ($disposition->requires_note) {
            $rules['wrapup_notes'] = 'required|min:3';
        }

        if ($this->schedule_followup) {
            $rules['followup_date'] = 'required|date|after:today';
        }

        if (!empty($rules)) {
            $this->validate($rules);
        }

        $this->call->update([
            'disposition_id' => $this->disposition_id,
            'wrapup_notes' => $this->wrapup_notes,
        ]);

        if ($this->call->related instanceof Lead) {
            $lead = $this->call->related;
            
            $updateData = ['last_contacted_at' => now()];
            
            if ($this->schedule_followup) {
                $updateData['next_followup_at'] = $this->followup_date;
            }
            
            $lead->update($updateData);
            
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'type' => 'call',
                'payload_json' => [
                    'call_id' => $this->call->id,
                    'direction' => $this->call->direction,
                    'duration_seconds' => $this->call->duration_seconds,
                    'disposition' => $disposition->name,
                    'notes' => $this->wrapup_notes,
                ],
            ]);
        }

        session()->flash('success', 'Call wrap-up completed successfully!');

        $this->dispatch('callWrappedUp');
        
        $this->show = false;
        $this->reset();
    }

    public function render()
    {
        $dispositions = CallDisposition::ordered()->get();

        return view('livewire.calls.call-wrap-up-modal', [
            'dispositions' => $dispositions,
        ]);
    }
}
