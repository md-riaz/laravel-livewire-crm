<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Lead Statuses</h1>
            <p class="mt-1 text-sm text-gray-600">Manage lead status stages for your sales pipeline</p>
        </div>
        <button wire:click="create" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300">
            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Status
        </button>
    </div>

    @if ($errors->has('general'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
            {{ $errors->first('general') }}
        </div>
    @endif

    <!-- Statuses Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flags</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requirements</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leads</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($statuses as $status)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <button wire:click="moveUp({{ $status['id'] }})" class="p-1 text-gray-400 hover:text-gray-600" title="Move up">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    </svg>
                                </button>
                                <button wire:click="moveDown({{ $status['id'] }})" class="p-1 text-gray-400 hover:text-gray-600" title="Move down">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <span class="text-sm text-gray-500 ml-2">{{ $status['sort_order'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" 
                                      style="background-color: {{ $status['color'] }}20; color: {{ $status['color'] }};">
                                    <span class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $status['color'] }};"></span>
                                    {{ $status['name'] }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                @if($status['is_default'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">Default</span>
                                @endif
                                @if($status['is_closed'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Closed</span>
                                @endif
                                @if($status['is_won'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Won</span>
                                @endif
                                @if($status['is_lost'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Lost</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                @if($status['requires_note'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Note</span>
                                @endif
                                @if($status['requires_followup_date'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Follow-up</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $status['leads_count'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="edit({{ $status['id'] }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                Edit
                            </button>
                            <button wire:click="delete({{ $status['id'] }})" 
                                    wire:confirm="Are you sure you want to delete this status?"
                                    class="text-red-600 hover:text-red-900">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No lead statuses found. Create your first status to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <form wire:submit="save">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $editingId ? 'Edit Lead Status' : 'Add Lead Status' }}
                        </h3>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" wire:model="name" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Color -->
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-1">
                                Color <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="color" id="color" wire:model.live="color" 
                                       class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                                <input type="text" wire:model="color" 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="#3B82F6">
                                <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium" 
                                      style="background-color: {{ $color }}20; color: {{ $color }};">
                                    Preview
                                </span>
                            </div>
                            @error('color')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label for="sortOrder" class="block text-sm font-medium text-gray-700 mb-1">
                                Sort Order <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="sortOrder" wire:model="sortOrder" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('sortOrder') border-red-500 @enderror">
                            @error('sortOrder')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Flags -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="isDefault" wire:model="isDefault" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="isDefault" class="ml-2 text-sm text-gray-700">Default Status</label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="isClosed" wire:model="isClosed" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="isClosed" class="ml-2 text-sm text-gray-700">Closed</label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="isWon" wire:model="isWon" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="isWon" class="ml-2 text-sm text-gray-700">Won</label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="isLost" wire:model="isLost" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="isLost" class="ml-2 text-sm text-gray-700">Lost</label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="requiresNote" wire:model="requiresNote" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="requiresNote" class="ml-2 text-sm text-gray-700">Requires Note</label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="requiresFollowupDate" wire:model="requiresFollowupDate" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="requiresFollowupDate" class="ml-2 text-sm text-gray-700">Requires Follow-up Date</label>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                        <button type="button" wire:click="closeModal" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">Save</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
