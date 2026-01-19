<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\LeadActivity;
use Livewire\Component;
use Livewire\Attributes\On;

class Kanban extends Component
{
    public $showCreateModal = false;
    public $selectedLeadId = null;

    public function mount()
    {
        //
    }

    #[On('leadMoved')]
    public function moveLead($leadId, $newStatusId, $oldStatusId)
    {
        $lead = Lead::findOrFail($leadId);
        $newStatus = LeadStatus::findOrFail($newStatusId);
        $oldStatus = LeadStatus::findOrFail($oldStatusId);

        // Validate business rules
        if ($newStatus->requires_note) {
            $this->dispatch('showNoteRequiredModal', leadId: $leadId, statusId: $newStatusId);
            return;
        }

        if ($newStatus->requires_followup_date) {
            $this->dispatch('showFollowupRequiredModal', leadId: $leadId, statusId: $newStatusId);
            return;
        }

        // Update lead status
        $lead->update([
            'lead_status_id' => $newStatusId,
        ]);

        // Create activity log
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'status_change',
            'payload_json' => [
                'from_status' => $oldStatus->name,
                'to_status' => $newStatus->name,
            ],
        ]);

        $this->dispatch('leadMoved:success');
    }

    #[On('leadCreated')]
    public function handleLeadCreated()
    {
        $this->showCreateModal = false;
    }

    #[On('leadUpdated')]
    public function handleLeadUpdated()
    {
        // Refresh the view
    }

    #[On('leadDeleted')]
    public function handleLeadDeleted()
    {
        // Refresh the view
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function render()
    {
        $statuses = LeadStatus::ordered()->get();
        
        $leadsByStatus = [];
        foreach ($statuses as $status) {
            $leadsByStatus[$status->id] = Lead::where('lead_status_id', $status->id)
                ->with(['assignedTo', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('livewire.leads.kanban', [
            'statuses' => $statuses,
            'leadsByStatus' => $leadsByStatus,
        ])->layout('components.layouts.app');
    }
}
