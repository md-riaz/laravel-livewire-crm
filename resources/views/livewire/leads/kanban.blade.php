<div>
    <!-- Kanban Board -->
    <div class="flex gap-4 overflow-x-auto pb-4" 
         x-data="kanbanBoard()"
         @lead-moved:success.window="$wire.$refresh()">
        @foreach($statuses as $status)
            <div class="shrink-0 w-80" wire:key="status-{{ $status->id }}">
                <!-- Column Header -->
                <div class="bg-white rounded-t-xl p-4 border-b-4 shadow-sm" style="border-bottom-color: {{ $status->color }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $status->color }};"></span>
                            <h3 class="font-semibold text-gray-900">{{ $status->name }}</h3>
                        </div>
                        <span class="bg-gray-100 text-gray-700 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ $leadsByStatus[$status->id]->count() }}
                        </span>
                    </div>
                </div>

                <!-- Leads Container -->
                <div class="bg-gray-50 rounded-b-xl p-4 min-h-[500px] space-y-3"
                     data-status-id="{{ $status->id }}"
                     @dragover.prevent="handleDragOver($event)"
                     @drop="handleDrop($event, {{ $status->id }})">
                    
                    @forelse($leadsByStatus[$status->id] as $lead)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-move hover:shadow-md hover:border-blue-300 transition-all"
                             draggable="true"
                             data-lead-id="{{ $lead->id }}"
                             data-status-id="{{ $status->id }}"
                             wire:key="lead-{{ $lead->id }}"
                             @dragstart="handleDragStart($event, {{ $lead->id }}, {{ $status->id }})"
                             @click.stop="$dispatch('openLeadDrawer', { leadId: {{ $lead->id }} })">
                            
                            <div class="flex items-start justify-between mb-3">
                                <h4 class="font-medium text-gray-900 flex-1 pr-2">{{ $lead->name }}</h4>
                                @if($lead->score)
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full shrink-0
                                        {{ $lead->score === 'hot' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $lead->score === 'warm' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $lead->score === 'cold' ? 'bg-blue-100 text-blue-700' : '' }}">
                                        @if($lead->score === 'hot') 🔥 @elseif($lead->score === 'warm') ☀️ @else ❄️ @endif
                                        {{ ucfirst($lead->score) }}
                                    </span>
                                @endif
                            </div>

                            @if($lead->company_name)
                                <div class="flex items-center text-sm text-gray-600 mb-3">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    {{ $lead->company_name }}
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                <div class="flex items-center text-xs text-gray-500">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ $lead->assignedTo ? $lead->assignedTo->name : 'Unassigned' }}
                                </div>
                                @if($lead->estimated_value)
                                    <span class="text-sm font-semibold text-green-600 inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        ${{ number_format($lead->estimated_value, 0) }}
                                    </span>
                                @endif
                            </div>

                            @if($lead->next_followup_at)
                                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center text-xs text-gray-500">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Follow-up: {{ $lead->next_followup_at->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-sm">No leads in this status</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

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
