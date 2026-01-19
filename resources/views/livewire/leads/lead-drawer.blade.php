<div>
    @if($show && $lead)
    <!-- Slide-over panel -->
    <div class="fixed inset-0 overflow-hidden z-50" 
         x-data="{ show: @entangle('show') }"
         x-show="show"
         x-transition:enter="ease-in-out duration-500"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in-out duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Background overlay -->
        <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="$wire.closeDrawer()"></div>

        <!-- Slide-over panel -->
        <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
            <div class="w-screen max-w-2xl"
                 x-show="show"
                 x-transition:enter="transform transition ease-in-out duration-500"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-500"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                
                <div class="h-full flex flex-col bg-white shadow-xl overflow-y-scroll">
                    <!-- Header -->
                    <div class="px-6 py-6 bg-gray-50 border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <h2 class="text-lg font-medium text-gray-900">Lead Details</h2>
                            <button wire:click="closeDrawer" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Lead Info and Form -->
                    <div class="flex-1 px-6 py-6 space-y-6">
                        <!-- Status Badge -->
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" 
                                  style="background-color: {{ $lead->status->color }}20; color: {{ $lead->status->color }}">
                                {{ $lead->status->name }}
                            </span>
                            <span class="text-sm text-gray-500">
                                Created {{ $lead->created_at->diffForHumans() }}
                            </span>
                        </div>

                        @if (session()->has('success'))
                            <div class="p-4 bg-green-50 border border-green-200 rounded-md">
                                <p class="text-sm text-green-600">{{ session('success') }}</p>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="p-4 bg-red-50 border border-red-200 rounded-md">
                                <p class="text-sm text-red-600">{{ session('error') }}</p>
                            </div>
                        @endif

                        <!-- Edit Form -->
                        <form wire:submit="save" class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                                <input type="text" id="name" wire:model="name" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Company -->
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700">Company</label>
                                <input type="text" id="company_name" wire:model="company_name" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('company_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" id="email" wire:model="email" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                    <div class="flex gap-2">
                                        <input type="text" id="phone" wire:model="phone" 
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @if($lead->phone)
                                            <button type="button" wire:click="clickToCall" 
                                                    class="mt-1 px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Source -->
                                <div>
                                    <label for="source" class="block text-sm font-medium text-gray-700">Source</label>
                                    <input type="text" id="source" wire:model="source" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('source') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Score -->
                                <div>
                                    <label for="score" class="block text-sm font-medium text-gray-700">Score *</label>
                                    <select id="score" wire:model="score" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="hot">Hot</option>
                                        <option value="warm">Warm</option>
                                        <option value="cold">Cold</option>
                                    </select>
                                    @error('score') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Estimated Value -->
                                <div>
                                    <label for="estimated_value" class="block text-sm font-medium text-gray-700">Estimated Value ($)</label>
                                    <input type="number" id="estimated_value" wire:model="estimated_value" step="0.01" min="0"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('estimated_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="lead_status_id" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select id="lead_status_id" wire:model="lead_status_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('lead_status_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Assigned To -->
                            <div>
                                <label for="assigned_to_user_id" class="block text-sm font-medium text-gray-700">Assigned To</label>
                                <select id="assigned_to_user_id" wire:model="assigned_to_user_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                                    @endforeach
                                </select>
                                @error('assigned_to_user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-between pt-4 border-t">
                                <button type="button" wire:click="deleteLead" 
                                        onclick="return confirm('Are you sure you want to delete this lead?')"
                                        class="px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-300 rounded-md hover:bg-red-100">
                                    Delete Lead
                                </button>
                                <div class="flex gap-3">
                                    <button type="button" wire:click="closeDrawer" 
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <button type="submit" wire:loading.attr="disabled"
                                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                                        <span wire:loading.remove>Save Changes</span>
                                        <span wire:loading>Saving...</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Activity Timeline -->
                        <div class="pt-6 border-t">
                            <h3 class="text-sm font-medium text-gray-900 mb-4">Activity Timeline</h3>
                            <div class="space-y-4">
                                @forelse($lead->activities as $activity)
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <span class="text-blue-600 text-xs font-medium">{{ substr($activity->user->name, 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-900">
                                                <span class="font-medium">{{ $activity->user->name }}</span>
                                                @if($activity->type === 'status_change')
                                                    changed status from 
                                                    <span class="font-medium">{{ $activity->payload_json['from_status'] ?? 'Unknown' }}</span> 
                                                    to 
                                                    <span class="font-medium">{{ $activity->payload_json['to_status'] ?? 'Unknown' }}</span>
                                                @elseif($activity->type === 'call')
                                                    made a call ({{ $activity->payload_json['duration'] ?? 0 }}s)
                                                @elseif($activity->type === 'note')
                                                    added a note
                                                @else
                                                    {{ $activity->type }}
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                            @if($activity->type === 'note' && isset($activity->payload_json['note']))
                                                <p class="mt-1 text-sm text-gray-600">{{ $activity->payload_json['note'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No activity yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
