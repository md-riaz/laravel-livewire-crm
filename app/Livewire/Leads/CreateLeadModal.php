<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Validate;

class CreateLeadModal extends Component
{
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

    public function save()
    {
        $this->validate();

        $defaultStatus = LeadStatus::default()->first();

        if (!$defaultStatus) {
            session()->flash('error', 'No default lead status found. Please configure lead statuses.');
            return;
        }

        Lead::create([
            'lead_status_id' => $defaultStatus->id,
            'assigned_to_user_id' => $this->assigned_to_user_id,
            'created_by_user_id' => auth()->id(),
            'name' => $this->name,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'score' => $this->score,
            'estimated_value' => $this->estimated_value,
        ]);

        session()->flash('success', 'Lead created successfully!');
        
        $this->dispatch('leadCreated');
        $this->reset();
    }

    public function closeModal()
    {
        $this->dispatch('leadCreated');
        $this->reset();
    }

    public function render()
    {
        $users = User::where('is_active', true)->get();

        return view('livewire.leads.create-lead-modal', [
            'users' => $users,
        ]);
    }
}
