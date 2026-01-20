<?php

namespace App\Livewire\Settings;

use App\Models\AuditLog;
use App\Models\CallDisposition;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CallDispositions extends Component
{
    public $dispositions = [];
    public $showModal = false;
    public $editingId = null;
    public $name = '';
    public $sortOrder = 0;
    public $isDefault = false;
    public $requiresNote = false;

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'sortOrder' => 'required|integer|min:0',
            'isDefault' => 'boolean',
            'requiresNote' => 'boolean',
        ];

        if ($this->editingId) {
            $rules['name'] .= '|unique:call_dispositions,name,' . $this->editingId . ',id,tenant_id,' . auth()->user()->tenant_id;
        } else {
            $rules['name'] .= '|unique:call_dispositions,name,NULL,id,tenant_id,' . auth()->user()->tenant_id;
        }

        return $rules;
    }

    public function mount(): void
    {
        $this->loadDispositions();
    }

    public function loadDispositions(): void
    {
        $this->dispositions = CallDisposition::ordered()
            ->withCount('calls')
            ->get()
            ->map(fn($disposition) => [
                'id' => $disposition->id,
                'name' => $disposition->name,
                'sort_order' => $disposition->sort_order,
                'is_default' => $disposition->is_default,
                'requires_note' => $disposition->requires_note,
                'calls_count' => $disposition->calls_count,
            ])
            ->toArray();
    }

    public function create(): void
    {
        $this->resetForm();
        $maxSortOrder = CallDisposition::max('sort_order') ?? 0;
        $this->sortOrder = $maxSortOrder + 1;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $disposition = CallDisposition::findOrFail($id);
        
        $this->editingId = $disposition->id;
        $this->name = $disposition->name;
        $this->sortOrder = $disposition->sort_order;
        $this->isDefault = $disposition->is_default;
        $this->requiresNote = $disposition->requires_note;
        
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // If setting as default, unset other defaults
            if ($this->isDefault) {
                CallDisposition::where('tenant_id', auth()->user()->tenant_id)
                    ->where('id', '!=', $this->editingId ?: 0)
                    ->update(['is_default' => false]);
            }

            if ($this->editingId) {
                $disposition = CallDisposition::findOrFail($this->editingId);
                $disposition->update([
                    'name' => $this->name,
                    'sort_order' => $this->sortOrder,
                    'is_default' => $this->isDefault,
                    'requires_note' => $this->requiresNote,
                ]);
                $action = 'call_disposition.updated';
            } else {
                $disposition = CallDisposition::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'name' => $this->name,
                    'sort_order' => $this->sortOrder,
                    'is_default' => $this->isDefault,
                    'requires_note' => $this->requiresNote,
                ]);
                $action = 'call_disposition.created';
            }

            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => $action,
                'auditable_type' => CallDisposition::class,
                'auditable_id' => $disposition->id,
                'metadata' => ['name' => $this->name],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            $this->loadDispositions();
            $this->closeModal();
            session()->flash('success', 'Call disposition saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to save call disposition: ' . $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        try {
            $disposition = CallDisposition::withCount('calls')->findOrFail($id);

            if ($disposition->calls_count > 0) {
                $this->addError('general', 'Cannot delete disposition with existing calls.');
                return;
            }

            DB::beginTransaction();

            AuditLog::create([
                'tenant_id' => auth()->user()->tenant_id,
                'user_id' => auth()->id(),
                'action' => 'call_disposition.deleted',
                'auditable_type' => CallDisposition::class,
                'auditable_id' => $disposition->id,
                'metadata' => ['name' => $disposition->name],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $disposition->delete();

            DB::commit();

            $this->loadDispositions();
            session()->flash('success', 'Call disposition deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to delete call disposition: ' . $e->getMessage());
        }
    }

    public function moveUp(int $id): void
    {
        $this->reorderDisposition($id, 'up');
    }

    public function moveDown(int $id): void
    {
        $this->reorderDisposition($id, 'down');
    }

    private function reorderDisposition(int $id, string $direction): void
    {
        try {
            DB::beginTransaction();

            $disposition = CallDisposition::findOrFail($id);
            $currentOrder = $disposition->sort_order;

            if ($direction === 'up') {
                $adjacent = CallDisposition::where('sort_order', '<', $currentOrder)
                    ->orderBy('sort_order', 'desc')
                    ->first();
                
                if ($adjacent) {
                    $newOrder = $adjacent->sort_order;
                    $adjacent->update(['sort_order' => $currentOrder]);
                    $disposition->update(['sort_order' => $newOrder]);
                }
            } else {
                $adjacent = CallDisposition::where('sort_order', '>', $currentOrder)
                    ->orderBy('sort_order', 'asc')
                    ->first();
                
                if ($adjacent) {
                    $newOrder = $adjacent->sort_order;
                    $adjacent->update(['sort_order' => $currentOrder]);
                    $disposition->update(['sort_order' => $newOrder]);
                }
            }

            DB::commit();
            $this->loadDispositions();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Failed to reorder disposition: ' . $e->getMessage());
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
        $this->sortOrder = 0;
        $this->isDefault = false;
        $this->requiresNote = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.settings.call-dispositions')
            ->layout('layouts.settings', ['title' => 'Call Dispositions']);
    }
}
