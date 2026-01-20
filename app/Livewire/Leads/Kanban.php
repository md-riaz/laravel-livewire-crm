<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\LeadActivity;
use Livewire\Component;
use Livewire\Attributes\On;

class Kanban extends Component
{
    public int $leadsPerColumn = 50;
    public array $loadedColumns = [];

    public function mount(): void
    {
        // Load all columns initially
        $statuses = LeadStatus::ordered()->get();
        foreach ($statuses as $status) {
            $this->loadedColumns[$status->id] = true;
        }
    }

    #[On('leadMoved')]
    public function moveLead(int $leadId, int $newStatusId, int $oldStatusId): void
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
    #[On('leadUpdated')]
    #[On('leadDeleted')]
    public function handleLeadEvents(): void
    {
        // Refresh the view
    }

    public function render()
    {
        $statuses = LeadStatus::ordered()->get();
        
        $leadsByStatus = [];
        foreach ($statuses as $status) {
            $leadsByStatus[$status->id] = Lead::where('lead_status_id', $status->id)
                ->select([
                    'id',
                    'name',
                    'company_name',
                    'score',
                    'estimated_value',
                    'next_followup_at',
                    'assigned_to_user_id',
                    'lead_status_id',
                ])
                ->with(['assignedTo:id,name'])
                ->orderBy('created_at', 'desc')
                ->limit($this->leadsPerColumn)
                ->get();
        }

        return view('livewire.leads.kanban', [
            'statuses' => $statuses,
            'leadsByStatus' => $leadsByStatus,
        ]);
    }
}
