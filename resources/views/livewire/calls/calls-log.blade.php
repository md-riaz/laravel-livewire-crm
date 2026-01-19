<div>
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Call Logs</h1>
        <p class="text-sm text-gray-600 mt-1">View and manage call history</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Date From -->
            <div>
                <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" 
                       wire:model.live="dateFrom" 
                       id="dateFrom"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Date To -->
            <div>
                <label for="dateTo" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" 
                       wire:model.live="dateTo" 
                       id="dateTo"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- User Filter (Supervisors only) -->
            @if($isSupervisor)
            <div>
                <label for="filterUserId" class="block text-sm font-medium text-gray-700 mb-1">Agent</label>
                <select wire:model.live="filterUserId" 
                        id="filterUserId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Agents</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Disposition Filter -->
            <div>
                <label for="filterDispositionId" class="block text-sm font-medium text-gray-700 mb-1">Disposition</label>
                <select wire:model.live="filterDispositionId" 
                        id="filterDispositionId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Dispositions</option>
                    @foreach($dispositions as $disposition)
                        <option value="{{ $disposition->id }}">{{ $disposition->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Search -->
        <div class="flex items-end gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       id="search"
                       placeholder="Search by phone number or notes..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button wire:click="clearFilters" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Clear Filters
            </button>
        </div>
    </div>

    <!-- Calls Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                            wire:click="sortBy('started_at')">
                            <div class="flex items-center gap-1">
                                Date/Time
                                @if($sortField === 'started_at')
                                    <span class="text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Agent
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Direction
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            From
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            To
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                            wire:click="sortBy('duration_seconds')">
                            <div class="flex items-center gap-1">
                                Duration
                                @if($sortField === 'duration_seconds')
                                    <span class="text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Disposition
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Related
                        </th>
                        @if($isSupervisor)
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Recording
                        </th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($calls as $call)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $call->started_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $call->started_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $call->user ? $call->user->name : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $call->direction === 'inbound' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($call->direction) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $call->from_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $call->to_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($call->duration_seconds)
                                    {{ gmdate('H:i:s', $call->duration_seconds) }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $call->disposition ? $call->disposition->name : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($call->related_type === 'App\Models\Lead' && $call->related)
                                    <span class="text-gray-900 font-medium">
                                        Lead: {{ $call->related->name }}
                                    </span>
                                @elseif($call->related)
                                    <span class="text-gray-600">
                                        {{ class_basename($call->related_type) }}: {{ $call->related->name ?? $call->related_id }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            @if($isSupervisor)
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($call->recording_url && filter_var($call->recording_url, FILTER_VALIDATE_URL))
                                    <button onclick="window.open({{ json_encode($call->recording_url) }}, '_blank')"
                                            class="inline-flex items-center text-blue-600 hover:text-blue-800 focus:outline-none">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
                                        </svg>
                                    </button>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($call->wrapup_notes)
                                    <button wire:click="toggleRow({{ $call->id }})"
                                            class="text-blue-600 hover:text-blue-800 focus:outline-none">
                                        {{ in_array($call->id, $expandedRows) ? 'Hide' : 'Show' }} Notes
                                    </button>
                                @else
                                    <span class="text-gray-400">No notes</span>
                                @endif
                            </td>
                        </tr>
                        @if(in_array($call->id, $expandedRows) && $call->wrapup_notes)
                        <tr class="bg-gray-50">
                            <td colspan="{{ $isSupervisor ? '10' : '9' }}" class="px-6 py-4">
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700">Wrap-up Notes:</span>
                                    <p class="mt-1 text-gray-600">{{ $call->wrapup_notes }}</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ $isSupervisor ? '10' : '9' }}" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <p class="text-lg font-medium">No calls found</p>
                                    <p class="text-sm">Try adjusting your filters or search criteria</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($calls->hasPages())
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
            {{ $calls->links() }}
        </div>
        @endif
    </div>

    <!-- Summary -->
    <div class="mt-4 text-sm text-gray-600">
        Showing {{ $calls->firstItem() ?? 0 }} to {{ $calls->lastItem() ?? 0 }} of {{ $calls->total() }} calls
    </div>
</div>
