<div class="h-full">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
        <!-- Left Column: Agent Status & Active Call -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Agent Status Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Agent Status</h2>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">SIP:</span>
                        @if ($sipRegistered)
                            <span class="flex items-center text-green-600">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="10" cy="10" r="8"/>
                                </svg>
                                Registered
                            </span>
                        @else
                            <span class="flex items-center text-gray-400">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="10" cy="10" r="8"/>
                                </svg>
                                Not Registered
                            </span>
                        @endif
                    </div>
                </div>

                @if (!$hasCredentials)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    No SIP credentials configured. Please contact your administrator to set up telephony access.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center space-x-4">
                        <label class="text-sm font-medium text-gray-700">Status:</label>
                        <select wire:model.live="agentStatus" 
                                wire:change="changeStatus($event.target.value)"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="offline">Offline</option>
                            <option value="available">Available</option>
                            <option value="away">Away</option>
                        </select>
                        
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full mr-2 
                                {{ $agentStatus === 'available' ? 'bg-green-500' : '' }}
                                {{ $agentStatus === 'away' ? 'bg-yellow-500' : '' }}
                                {{ $agentStatus === 'offline' ? 'bg-gray-400' : '' }}
                            "></span>
                            <span class="text-sm font-medium capitalize">{{ $agentStatus }}</span>
                        </div>
                    </div>

                    @error('status') 
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
                    @error('sip') 
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
                @endif
            </div>

            <!-- Active Call Card -->
            @if ($activeCall)
                <div class="bg-indigo-50 border-2 border-indigo-500 rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Active Call</h2>
                        <div class="text-2xl font-mono text-indigo-600" x-data="{ timer: @entangle('callTimer') }" x-init="
                            setInterval(() => { 
                                timer++; 
                                $wire.updateCallTimer(timer);
                            }, 1000)
                        ">
                            <span x-text="Math.floor(timer / 60).toString().padStart(2, '0')"></span>:<span x-text="(timer % 60).toString().padStart(2, '0')"></span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Direction:</span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $activeCall['direction'] === 'outbound' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($activeCall['direction']) }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">From:</span>
                            <span class="text-sm text-gray-900">{{ $activeCall['from_number'] }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">To:</span>
                            <span class="text-sm text-gray-900">{{ $activeCall['to_number'] }}</span>
                        </div>

                        @if (isset($activeCall['lead_name']))
                            <div class="flex items-center justify-between p-3 bg-white rounded-md">
                                <span class="text-sm font-medium text-gray-700">Lead:</span>
                                <a href="{{ route('leads.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">
                                    {{ $activeCall['lead_name'] }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Call Controls -->
                    <div class="grid grid-cols-4 gap-3 mt-6">
                        <button wire:click="toggleMute" 
                                class="flex flex-col items-center justify-center p-4 rounded-lg transition-colors
                                    {{ $isMuted ? 'bg-red-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if ($isMuted)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                @endif
                            </svg>
                            <span class="text-xs mt-1">{{ $isMuted ? 'Unmute' : 'Mute' }}</span>
                        </button>

                        <button wire:click="toggleHold"
                                class="flex flex-col items-center justify-center p-4 rounded-lg transition-colors
                                    {{ $isOnHold ? 'bg-yellow-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-xs mt-1">{{ $isOnHold ? 'Resume' : 'Hold' }}</span>
                        </button>

                        <button disabled
                                class="flex flex-col items-center justify-center p-4 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            <span class="text-xs mt-1">Transfer</span>
                        </button>

                        <button wire:click="hangupCall"
                                class="flex flex-col items-center justify-center p-4 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"/>
                            </svg>
                            <span class="text-xs mt-1">Hangup</span>
                        </button>
                    </div>

                    @error('call') 
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
                </div>
            @endif

            <!-- Dial Pad Card -->
            @if (!$activeCall && $hasCredentials)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Dial Pad</h2>
                    
                    <div class="mb-4">
                        <input type="text" 
                               wire:model.live="dialNumber"
                               placeholder="Enter phone number"
                               class="w-full text-center text-2xl font-mono px-4 py-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        @error('dialNumber') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Dial Pad Grid -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        @foreach(['1', '2', '3', '4', '5', '6', '7', '8', '9', '*', '0', '#'] as $digit)
                            <button wire:click="dialPadInput('{{ $digit }}')"
                                    class="p-4 text-xl font-semibold bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                {{ $digit }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Dial Actions -->
                    <div class="grid grid-cols-2 gap-3">
                        <button wire:click="clearDialNumber"
                                class="px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            Clear
                        </button>
                        <button wire:click="makeCall"
                                class="px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors font-semibold">
                            Call
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Recent Calls -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Calls</h2>
                
                @if (empty($recentCalls))
                    <p class="text-gray-500 text-center py-8">No recent calls</p>
                @else
                    <div class="space-y-3">
                        @foreach($recentCalls as $call)
                            <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center">
                                        @if ($call['direction'] === 'outbound')
                                            <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                        @endif
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $call['direction'] === 'outbound' ? $call['to_number'] : $call['from_number'] }}
                                        </span>
                                    </div>
                                    
                                    @if ($call['duration_seconds'])
                                        <span class="text-xs text-gray-500">
                                            {{ floor($call['duration_seconds'] / 60) }}:{{ str_pad($call['duration_seconds'] % 60, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="text-xs text-gray-500 mb-1">{{ $call['started_at'] }}</div>
                                
                                @if ($call['disposition'])
                                    <div class="inline-block px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">
                                        {{ $call['disposition'] }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Include CallWrapUpModal -->
    @livewire('calls.call-wrap-up-modal')
</div>

@if ($hasCredentials)
    @push('scripts')
        <script type="module">
            import { SipAgent } from '@vite/resources/js/sip-agent.js';
            
            document.addEventListener('livewire:initialized', () => {
                const sipConfig = @json($sipCredentials);
                
                // Fetch SIP password securely
                fetch('{{ route('agent.console.sip-password') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    sipConfig.sip_password = data.password;
                    window.sipAgent = new SipAgent(sipConfig, Livewire.find('{{ $_instance->getId() }}'));
                })
                .catch(error => {
                    console.error('Failed to initialize SIP agent:', error);
                });
            });

            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (window.sipAgent) {
                    window.sipAgent.cleanup();
                }
            });
        </script>
    @endpush
@endif
