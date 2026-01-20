<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\LeadActivity;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class LeadDrawer extends Component
{
    public ?Lead $lead = null;
    public bool $show = false;

    // Form fields
    #[Validate('required|min:2')]
    public $name = '';

    #[Validate('nullable|min:2')]
    public $company_name = '';

    #[Validate('nullable|email')]
    public $email = '';

    #[Validate('nullable')]
    public $phone = '';

    #[Validate('nullable')]
    public $source = '';

    #[Validate('required|in:hot,warm,cold')]
    public $score = 'warm';

    #[Validate('nullable|numeric|min:0')]
    public $estimated_value = null;

    #[Validate('nullable|exists:users,id')]
    public $assigned_to_user_id = null;

    #[Validate('nullable|exists:lead_statuses,id')]
    public $lead_status_id = null;

    #[On('openLeadDrawer')]
    public function openDrawer($leadId)
    {
        $this->lead = Lead::with(['status', 'assignedTo', 'createdBy', 'activities.user'])->findOrFail($leadId);
        
        // Populate form fields
        $this->name = $this->lead->name;
        $this->company_name = $this->lead->company_name;
        $this->email = $this->lead->email;
        $this->phone = $this->lead->phone;
        $this->source = $this->lead->source;
        $this->score = $this->lead->score;
        $this->estimated_value = $this->lead->estimated_value;
        $this->assigned_to_user_id = $this->lead->assigned_to_user_id;
        $this->lead_status_id = $this->lead->lead_status_id;
        
        $this->show = true;
    }

    public function closeDrawer()
    {
        $this->show = false;
        $this->reset();
    }

    public function save()
    {
        if (!$this->lead) {
            return;
        }

        $this->validate();

        $oldStatus = $this->lead->lead_status_id;

        $this->lead->update([
            'name' => $this->name,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'score' => $this->score,
            'estimated_value' => $this->estimated_value,
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'lead_status_id' => $this->lead_status_id,
        ]);

        // Log status change if it changed
        if ($oldStatus != $this->lead_status_id) {
            $oldStatusName = LeadStatus::find($oldStatus)?->name ?? 'Unknown';
            $newStatusName = LeadStatus::find($this->lead_status_id)?->name ?? 'Unknown';

            LeadActivity::create([
                'lead_id' => $this->lead->id,
                'user_id' => auth()->id(),
                'type' => 'status_change',
                'payload_json' => [
                    'from_status' => $oldStatusName,
                    'to_status' => $newStatusName,
                ],
            ]);
        }

        session()->flash('success', 'Lead updated successfully!');
        
        $this->dispatch('leadUpdated');
        $this->closeDrawer();
    }

    public function deleteLead()
    {
        if (!$this->lead) {
            return;
        }

        $this->lead->delete();
        
        session()->flash('success', 'Lead deleted successfully!');
        
        $this->dispatch('leadDeleted');
        $this->closeDrawer();
    }

    public function clickToCall()
    {
        if (!$this->lead || !$this->lead->phone) {
            session()->flash('error', 'No phone number available.');
            return;
        }

        // Dispatch event for click-to-call via BroadcastChannel
        $this->dispatch('initiateCall', [
            'number' => $this->lead->phone,
            'relatedType' => 'lead',
            'relatedId' => $this->lead->id,
        ]);
    }

    public function render()
    {
        $statuses = LeadStatus::ordered()->get();
        $users = User::where('is_active', true)->get();

        return view('livewire.leads.lead-drawer', [
            'statuses' => $statuses,
            'users' => $users,
        ]);
    }
}
