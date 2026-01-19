<div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" 
     x-data="{ show: true }"
     x-show="show"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.self="$wire.closeModal()">
    
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Create New Lead</h3>
            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form wire:submit="save" class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Lead Name *</label>
                    <input type="text" id="name" wire:model="name" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Company -->
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
                    <input type="text" id="company_name" wire:model="company_name" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('company_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

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
                    <input type="text" id="phone" wire:model="phone" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Source -->
                <div>
                    <label for="source" class="block text-sm font-medium text-gray-700">Source</label>
                    <input type="text" id="source" wire:model="source" placeholder="e.g., Website, Referral" 
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

                <!-- Estimated Value -->
                <div>
                    <label for="estimated_value" class="block text-sm font-medium text-gray-700">Estimated Value ($)</label>
                    <input type="number" id="estimated_value" wire:model="estimated_value" step="0.01" min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('estimated_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Assigned To -->
                <div>
                    <label for="assigned_to_user_id" class="block text-sm font-medium text-gray-700">Assign To</label>
                    <select id="assigned_to_user_id" wire:model="assigned_to_user_id" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                        @endforeach
                    </select>
                    @error('assigned_to_user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" wire:click="closeModal" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="submit" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                    <span wire:loading.remove>Create Lead</span>
                    <span wire:loading>Creating...</span>
                </button>
            </div>
        </form>
    </div>
</div>
