<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Leads Pipeline</h1>
            <p class="mt-1 text-gray-600">Manage and track your leads</p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- View Mode Toggle -->
            <div class="inline-flex rounded-lg shadow-sm border border-gray-300 bg-white" role="group">
                <button wire:click="setViewMode('kanban')" 
                        class="px-4 py-2.5 text-sm font-medium rounded-l-lg
                        {{ $viewMode === 'kanban' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50' }}
                        focus:z-10 focus:ring-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    <span class="ml-2 hidden sm:inline">Kanban</span>
                </button>
                <button wire:click="setViewMode('table')" 
                        class="px-4 py-2.5 text-sm font-medium rounded-r-lg border-l
                        {{ $viewMode === 'table' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50' }}
                        focus:z-10 focus:ring-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span class="ml-2 hidden sm:inline">Table</span>
                </button>
            </div>

            <!-- New Lead Button -->
            <button wire:click="openCreateModal" 
                    class="inline-flex items-center px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium shadow-sm transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Lead
            </button>
        </div>
    </div>

    <!-- View Content -->
    <div>
        @if($viewMode === 'kanban')
            @livewire('leads.kanban')
        @else
            @livewire('leads.leads-table')
        @endif
    </div>

    <!-- Create Lead Modal -->
    @if($showCreateModal)
        @livewire('leads.create-lead-modal')
    @endif

    <!-- Lead Drawer -->
    @livewire('leads.lead-drawer')
</div>
