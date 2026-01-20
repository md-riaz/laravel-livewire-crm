<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class LeadsTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sortField = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterAssignedTo = '';

    #[Url]
    public string $filterScore = '';

    #[Url]
    public string $filterDateFrom = '';

    #[Url]
    public string $filterDateTo = '';

    public array $selectedLeads = [];
    public bool $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'filterStatus' => ['except' => ''],
        'filterAssignedTo' => ['except' => ''],
        'filterScore' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAssignedTo(): void
    {
        $this->resetPage();
    }

    public function updatingFilterScore(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'filterStatus',
            'filterAssignedTo',
            'filterScore',
            'filterDateFrom',
            'filterDateTo',
        ]);
        $this->resetPage();
    }

    public function toggleSelectAll(): void
    {
        $this->selectAll = !$this->selectAll;
        
        if ($this->selectAll) {
            $this->selectedLeads = $this->getFilteredLeads()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedLeads = [];
        }
    }

    public function bulkAssign(int $userId): void
    {
        Lead::whereIn('id', $this->selectedLeads)
            ->update(['assigned_to_user_id' => $userId]);

        $this->selectedLeads = [];
        $this->selectAll = false;
        
        session()->flash('success', 'Leads assigned successfully.');
    }

    public function bulkChangeStatus(int $statusId): void
    {
        Lead::whereIn('id', $this->selectedLeads)
            ->update(['lead_status_id' => $statusId]);

        $this->selectedLeads = [];
        $this->selectAll = false;
        
        session()->flash('success', 'Lead status updated successfully.');
    }

    public function deleteLead(int $leadId): void
    {
        $lead = Lead::findOrFail($leadId);
        $lead->delete();
        
        session()->flash('success', 'Lead deleted successfully.');
    }

    #[On('leadCreated')]
    #[On('leadUpdated')]
    #[On('leadDeleted')]
    public function refreshLeads(): void
    {
        // Refresh the component
    }

    protected function getFilteredLeads()
    {
        $query = Lead::query()
            ->select([
                'leads.id',
                'leads.name',
                'leads.company_name',
                'leads.email',
                'leads.phone',
                'leads.score',
                'leads.estimated_value',
                'leads.last_contacted_at',
                'leads.next_followup_at',
                'leads.lead_status_id',
                'leads.assigned_to_user_id',
                'leads.created_at',
            ])
            ->with(['status:id,name,color', 'assignedTo:id,name']);

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('company_name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        // Filters
        if ($this->filterStatus) {
            $query->where('lead_status_id', $this->filterStatus);
        }

        if ($this->filterAssignedTo) {
            $query->where('assigned_to_user_id', $this->filterAssignedTo);
        }

        if ($this->filterScore) {
            $query->where('score', $this->filterScore);
        }

        if ($this->filterDateFrom) {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->whereDate('created_at', '<=', $this->filterDateTo);
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query;
    }

    public function render()
    {
        $leads = $this->getFilteredLeads()->paginate(50);
        
        $statuses = LeadStatus::select('id', 'name', 'color')->ordered()->get();
        $users = User::select('id', 'name')->where('is_active', true)->orderBy('name')->get();

        return view('livewire.leads.leads-table', [
            'leads' => $leads,
            'statuses' => $statuses,
            'users' => $users,
        ]);
    }
}
