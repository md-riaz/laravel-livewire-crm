<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Leads Pipeline</h1>
        <button wire:click="openCreateModal" 
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            + New Lead
        </button>
    </div>

    <!-- Kanban Board -->
    <div class="flex gap-4 overflow-x-auto pb-4" 
         x-data="kanbanBoard()"
         @lead-moved:success.window="$wire.$refresh()">
        @foreach($statuses as $status)
            <div class="flex-shrink-0 w-80">
                <!-- Column Header -->
                <div class="bg-white rounded-t-lg p-4 border-b-4" style="border-bottom-color: {{ $status->color }}">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">{{ $status->name }}</h3>
                        <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded-full">
                            {{ $leadsByStatus[$status->id]->count() }}
                        </span>
                    </div>
                </div>

                <!-- Leads Container -->
                <div class="bg-gray-50 rounded-b-lg p-4 min-h-[400px] space-y-3"
                     data-status-id="{{ $status->id }}"
                     @dragover.prevent="handleDragOver($event)"
                     @drop="handleDrop($event, {{ $status->id }})">
                    
                    @forelse($leadsByStatus[$status->id] as $lead)
                        <div class="bg-white rounded-lg shadow p-4 cursor-move hover:shadow-md transition-shadow"
                             draggable="true"
                             data-lead-id="{{ $lead->id }}"
                             data-status-id="{{ $status->id }}"
                             @dragstart="handleDragStart($event, {{ $lead->id }}, {{ $status->id }})"
                             @click.stop="$dispatch('openLeadDrawer', { leadId: {{ $lead->id }} })">
                            
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-medium text-gray-900">{{ $lead->name }}</h4>
                                @if($lead->score)
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $lead->score === 'hot' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $lead->score === 'warm' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $lead->score === 'cold' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ ucfirst($lead->score) }}
                                    </span>
                                @endif
                            </div>

                            @if($lead->company_name)
                                <p class="text-sm text-gray-600 mb-2">{{ $lead->company_name }}</p>
                            @endif

                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>{{ $lead->assignedTo ? $lead->assignedTo->name : 'Unassigned' }}</span>
                                @if($lead->estimated_value)
                                    <span class="font-semibold text-green-600">${{ number_format($lead->estimated_value, 0) }}</span>
                                @endif
                            </div>

                            @if($lead->next_followup_at)
                                <div class="mt-2 text-xs text-gray-500">
                                    Follow-up: {{ $lead->next_followup_at->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <p class="text-sm">No leads in this status</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create Lead Modal -->
    @if($showCreateModal)
        @livewire('leads.create-lead-modal')
    @endif

    <!-- Lead Drawer -->
    @livewire('leads.lead-drawer')

    <script>
        function kanbanBoard() {
            return {
                draggedLeadId: null,
                draggedFromStatusId: null,

                handleDragStart(event, leadId, statusId) {
                    this.draggedLeadId = leadId;
                    this.draggedFromStatusId = statusId;
                    event.dataTransfer.effectAllowed = 'move';
                    event.target.classList.add('opacity-50');
                },

                handleDragOver(event) {
                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                },

                handleDrop(event, newStatusId) {
                    event.preventDefault();
                    
                    const draggedElement = document.querySelector(`[data-lead-id="${this.draggedLeadId}"]`);
                    if (draggedElement) {
                        draggedElement.classList.remove('opacity-50');
                    }

                    if (this.draggedFromStatusId !== newStatusId) {
                        @this.dispatch('leadMoved', {
                            leadId: this.draggedLeadId,
                            newStatusId: newStatusId,
                            oldStatusId: this.draggedFromStatusId
                        });
                    }

                    this.draggedLeadId = null;
                    this.draggedFromStatusId = null;
                }
            };
        }
    </script>
</div>
