<?php

namespace App\Livewire\Calls;

use App\Models\Call;
use App\Models\CallDisposition;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;

class CallsLog extends Component
{
    use WithPagination;

    #[Validate('nullable|date')]
    public $dateFrom = '';

    #[Validate('nullable|date')]
    public $dateTo = '';

    #[Validate('nullable|exists:users,id')]
    public $filterUserId = '';

    #[Validate('nullable|exists:call_dispositions,id')]
    public $filterDispositionId = '';

    #[Validate('nullable|string|max:255')]
    public $search = '';

    public $sortField = 'started_at';
    public $sortDirection = 'desc';

    public $expandedRows = [];

    /**
     * Reset pagination when filters change
     */
    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when filters change
     */
    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when filters change
     */
    public function updatingFilterUserId(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when filters change
     */
    public function updatingFilterDispositionId(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when search changes
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Sort by a specific field
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Toggle expanded row for showing wrap-up notes
     */
    public function toggleRow(int $callId): void
    {
        if (in_array($callId, $this->expandedRows)) {
            $this->expandedRows = array_diff($this->expandedRows, [$callId]);
        } else {
            $this->expandedRows[] = $callId;
        }
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->reset(['dateFrom', 'dateTo', 'filterUserId', 'filterDispositionId', 'search']);
        $this->resetPage();
    }

    /**
     * Check if user is supervisor or admin
     */
    protected function isSupervisor(): bool
    {
        return in_array(auth()->user()->role, ['supervisor', 'tenant_admin']);
    }

    /**
     * Render the calls log component
     */
    public function render()
    {
        $query = Call::query()
            ->with(['user', 'disposition', 'related']);

        // Role-based filtering
        if (!$this->isSupervisor()) {
            $query->where('user_id', auth()->id());
        }

        // Apply filters
        if ($this->dateFrom) {
            $query->whereDate('started_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('started_at', '<=', $this->dateTo);
        }

        if ($this->filterUserId) {
            $query->where('user_id', $this->filterUserId);
        }

        if ($this->filterDispositionId) {
            $query->where('disposition_id', $this->filterDispositionId);
        }

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('from_number', 'like', '%' . $this->search . '%')
                    ->orWhere('to_number', 'like', '%' . $this->search . '%')
                    ->orWhere('wrapup_notes', 'like', '%' . $this->search . '%');
            });
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $calls = $query->paginate(50);

        // Get filter options
        $users = $this->isSupervisor()
            ? User::where('tenant_id', auth()->user()->tenant_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect();

        $dispositions = CallDisposition::ordered()->get();

        return view('livewire.calls.calls-log', [
            'calls' => $calls,
            'users' => $users,
            'dispositions' => $dispositions,
            'isSupervisor' => $this->isSupervisor(),
        ])->layout('components.layouts.app');
    }
}
