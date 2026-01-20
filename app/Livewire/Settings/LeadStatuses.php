<?php

namespace App\Livewire\Settings;

use App\Models\AuditLog;
use App\Models\LeadStatus;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LeadStatuses extends Component
{
    public $statuses = [];
    public $showModal = false;
    public $editingId = null;
    public $name = '';
    public $color = '#3B82F6';
    public $sortOrder = 0;
    public $isDefault = false;
    public $isClosed = false;
    public $isWon = false;
    public $isLost = false;
    public $requiresNote = false;
    public $requiresFollowupDate = false;

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sortOrder' => 'required|integer|min:0',
            'isDefault' => 'boolean',
            'isClosed' => 'boolean',
            'isWon' => 'boolean',
            'isLost' => 'boolean',
            'requiresNote' => 'boolean',
            'requiresFollowupDate' => 'boolean',
        ];

        if ($this->editingId) {
            $rules['name'] .= '|unique:lead_statuses,name,' . $this->editingId . ',id,tenant_id,' . auth()->user()->tenant_id;
        } else {
            $rules['name'] .= '|unique:lead_statuses,name,NULL,id,tenant_id,' . auth()->user()->tenant_id;
        }

        return $rules;
    }

    public function mount(): void
    {
        $this->loadStatuses();
    }

    public function loadStatuses(): void
    {
        $this->statuses = LeadStatus::ordered()
            ->withCount('leads')
            ->get()
            ->map(fn($status) => [
                'id' => $status->id,
                'name' => $status->name,
                'color' => $status->color,
                'sort_order' => $status->sort_order,
                'is_default' => $status->is_default,
                'is_closed' => $status->is_closed,
                'is_won' => $status->is_won,
                'is_lost' => $status->is_lost,
                'requires_note' => $status->requires_note,
                'requires_followup_date' => $status->requires_followup_date,
                'leads_count' => $status->leads_count,
            ])
            ->toArray();
    }

    public function create(): void
    {
        $this->resetForm();
        $maxSortOrder = LeadStatus::max('sort_order') ?? 0;
        $this->sortOrder = $maxSortOrder + 1;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $status = LeadStatus::findOrFail($id);
        
        $this->editingId = $status->id;
        $this->name = $status->name;
        $this->color = $status->color;
        $this->sortOrder = $status->sort_order;
        $this->isDefault = $status->is_default;
        $this->isClosed = $status->is_closed;
        $this->isWon = $status->is_won;
        $this->isLost = $status->is_lost;
        $this->requiresNote = $status->requires_note;
        $this->requiresFollowupDate = $status->requires_followup_date;
        
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // If setting as default, unset other defaults
            if ($this->isDefault) {
                LeadStatus::where('tenant_id', auth()->user()->tenant_id)
                    ->update(['is_default' => false]);
            }

            if ($this->editingId) {
                $status = LeadStatus::findOrFail($this->editingId);
                $status->update([
                    'name' => $this->name,
                    'color' => $this->color,
                    'sort_order' => $this->sortOrder,
                    'is_default' => $this->isDefault,
                    'is_closed' => $this->isClosed,
                    'is_won' => $this->isWon,
                    'is_lost' => $this->isLost,
                    'requires_note' => $this->requiresNote,
                    'requires_followup_date' => $this->requiresFollowupDate,
                ]);
                $action = 'lead_status.updated';
            } else {
                $status = LeadStatus::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'name' => $this->name,
                    'color' => $this->color,
                    'sort_order' => $this->sortOrder,
                    'is_default' => $this->isDefault,
                    'is_closed' => $this->isClosed,
                    'is_won' => $this->isWon,
                    'is_lost' => $this->isLost,
                    'requires_note' => $this->requiresNote,
                    'requires_followup_date' => $this->requiresFollowupDate,
                ]);
                $action = 'lead_status.created';
            }

            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => $action,
                'auditable_type' => LeadStatus::class,
                'auditable_id' => $status->id,
                'metadata' => ['name' => $this->name],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            $this->loadStatuses();
            $this->closeModal();
            session()->flash('success', 'Lead status saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to save lead status: ' . $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        try {
            $status = LeadStatus::withCount('leads')->findOrFail($id);

            if ($status->leads_count > 0) {
                $this->addError('general', 'Cannot delete status with existing leads.');
                return;
            }

            DB::beginTransaction();

            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'lead_status.deleted',
                'auditable_type' => LeadStatus::class,
                'auditable_id' => $status->id,
                'metadata' => ['name' => $status->name],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $status->delete();

            DB::commit();

            $this->loadStatuses();
            session()->flash('success', 'Lead status deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to delete lead status: ' . $e->getMessage());
        }
    }

    public function moveUp(int $id): void
    {
        $this->reorderStatus($id, 'up');
    }

    public function moveDown(int $id): void
    {
        $this->reorderStatus($id, 'down');
    }

    private function reorderStatus(int $id, string $direction): void
    {
        try {
            DB::beginTransaction();

            $status = LeadStatus::findOrFail($id);
            $currentOrder = $status->sort_order;

            if ($direction === 'up') {
                $adjacent = LeadStatus::where('sort_order', '<', $currentOrder)
                    ->orderBy('sort_order', 'desc')
                    ->first();
                
                if ($adjacent) {
                    $newOrder = $adjacent->sort_order;
                    $adjacent->update(['sort_order' => $currentOrder]);
                    $status->update(['sort_order' => $newOrder]);
                }
            } else {
                $adjacent = LeadStatus::where('sort_order', '>', $currentOrder)
                    ->orderBy('sort_order', 'asc')
                    ->first();
                
                if ($adjacent) {
                    $newOrder = $adjacent->sort_order;
                    $adjacent->update(['sort_order' => $currentOrder]);
                    $status->update(['sort_order' => $newOrder]);
                }
            }

            DB::commit();
            $this->loadStatuses();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to reorder status: ' . $e->getMessage());
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
        $this->color = '#3B82F6';
        $this->sortOrder = 0;
        $this->isDefault = false;
        $this->isClosed = false;
        $this->isWon = false;
        $this->isLost = false;
        $this->requiresNote = false;
        $this->requiresFollowupDate = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.settings.lead-statuses')
            ->layout('layouts.settings', ['title' => 'Lead Statuses']);
    }
}
