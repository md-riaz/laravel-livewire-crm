<?php

namespace App\Livewire\Leads;

use Livewire\Component;
use Livewire\Attributes\On;

class Index extends Component
{
    public string $viewMode = 'kanban';
    public bool $showCreateModal = false;

    public function mount(): void
    {
        // Get view mode from session or query parameter
        $this->viewMode = session('leads.view_mode', request()->get('view', 'kanban'));
        
        // Validate view mode
        if (!in_array($this->viewMode, ['kanban', 'table'])) {
            $this->viewMode = 'kanban';
        }
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['kanban', 'table'])) {
            $this->viewMode = $mode;
            session(['leads.view_mode' => $mode]);
        }
    }

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    #[On('leadCreated')]
    public function handleLeadCreated(): void
    {
        $this->showCreateModal = false;
    }

    public function render()
    {
        return view('livewire.leads.index')->layout('components.layouts.app');
    }
}
