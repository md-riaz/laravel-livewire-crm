<div>
    @if($show)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" 
         x-data="{ show: @entangle('show') }"
         x-show="show"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.stop>
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b bg-blue-50">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Complete Call Wrap-Up</h3>
                    <p class="text-sm text-gray-600 mt-1">This step is mandatory to close the call</p>
                </div>
                <div class="text-blue-600">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
            </div>

            <!-- Call Information Banner -->
            @if($call)
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Direction:</span>
                        <span class="ml-2 font-medium capitalize {{ $call->direction === 'inbound' ? 'text-green-600' : 'text-blue-600' }}">
                            {{ $call->direction }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">Duration:</span>
                        <span class="ml-2 font-medium">{{ gmdate('i:s', $call->duration_seconds ?? 0) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Number:</span>
                        <span class="ml-2 font-medium">{{ $call->direction === 'inbound' ? $call->from_number : $call->to_number }}</span>
                    </div>
                    @if($this->isLeadCall)
                    <div>
                        <span class="text-gray-500">Lead:</span>
                        <span class="ml-2 font-medium text-blue-600">{{ $call->related->name }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Modal Body -->
            <form wire:submit="save" class="p-6 space-y-5">
                
                <!-- Disposition Selection -->
                <div>
                    <label for="disposition_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Call Disposition <span class="text-red-500">*</span>
                    </label>
                    <select id="disposition_id" 
                            wire:model.live="disposition_id" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('disposition_id') border-red-500 @enderror"
                            required>
                        <option value="">Select outcome of this call...</option>
                        @foreach($dispositions as $disposition)
                            <option value="{{ $disposition->id }}">
                                {{ $disposition->name }}
                                @if($disposition->requires_note) (Notes required) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('disposition_id') 
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Wrap-up Notes -->
                @php
                    $selectedDisposition = $dispositions->firstWhere('id', $disposition_id);
                    $notesRequired = $selectedDisposition && $selectedDisposition->requires_note;
                @endphp
                
                <div>
                    <label for="wrapup_notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Call Notes
                        @if($notesRequired)
                            <span class="text-red-500">*</span>
                            <span class="text-xs text-gray-500">(Required for this disposition)</span>
                        @endif
                    </label>
                    <textarea id="wrapup_notes" 
                              wire:model="wrapup_notes" 
                              rows="4"
                              placeholder="Enter details about the call, customer feedback, next steps, etc."
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('wrapup_notes') border-red-500 @enderror"
                              @if($notesRequired) required @endif></textarea>
                    @error('wrapup_notes') 
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Schedule Follow-up Section -->
                @if($this->isLeadCall)
                <div class="border-t pt-4">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" 
                               id="schedule_followup" 
                               wire:model.live="schedule_followup"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="schedule_followup" class="ml-2 block text-sm font-medium text-gray-700">
                            Schedule follow-up for this lead
                        </label>
                    </div>

                    @if($schedule_followup)
                    <div class="ml-6 mt-3">
                        <label for="followup_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Follow-up Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="followup_date" 
                               wire:model="followup_date"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('followup_date') border-red-500 @enderror"
                               required>
                        @error('followup_date') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif
                </div>
                @endif

                <!-- Warning Message -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Required:</strong> You must complete this wrap-up before proceeding. The modal cannot be closed without saving.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="save">Complete Wrap-Up</span>
                        <span wire:loading wire:target="save" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
