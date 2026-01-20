<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">SIP Credentials</h1>
        <p class="mt-1 text-sm text-gray-600">Configure your SIP credentials for making and receiving calls</p>
    </div>

    @if ($isTenantAdmin)
        <div class="mb-6 bg-white rounded-lg shadow p-4">
            <label for="user-select" class="block text-sm font-medium text-gray-700 mb-2">
                Select User
            </label>
            <select wire:model.live="selectedUserId" id="user-select" class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                @foreach($users as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }} ({{ $user['email'] }})</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm" x-data="{ showPassword: false }">
        <form wire:submit="save" class="p-6 space-y-6">
            @if ($errors->has('general'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    {{ $errors->first('general') }}
                </div>
            @endif

            <!-- SIP WebSocket URL -->
            <div>
                <label for="sipWsUrl" class="block text-sm font-medium text-gray-700 mb-1">
                    SIP WebSocket URL <span class="text-red-500">*</span>
                </label>
                <input type="url" id="sipWsUrl" wire:model="sipWsUrl" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('sipWsUrl') border-red-500 @enderror"
                       placeholder="wss://sip.example.com:7443">
                @error('sipWsUrl')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">The WebSocket URL must start with wss://</p>
            </div>

            <!-- SIP Username -->
            <div>
                <label for="sipUsername" class="block text-sm font-medium text-gray-700 mb-1">
                    SIP Username <span class="text-red-500">*</span>
                </label>
                <input type="text" id="sipUsername" wire:model="sipUsername" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('sipUsername') border-red-500 @enderror"
                       placeholder="1001">
                @error('sipUsername')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Your SIP extension number</p>
            </div>

            <!-- SIP Password -->
            <div>
                <label for="sipPassword" class="block text-sm font-medium text-gray-700 mb-1">
                    SIP Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" id="sipPassword" wire:model="sipPassword" 
                           class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('sipPassword') border-red-500 @enderror"
                           placeholder="••••••••">
                    <button type="button" @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg x-show="!showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('sipPassword')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Password is encrypted before storage</p>
            </div>

            <!-- SIP Domain -->
            <div>
                <label for="sipDomain" class="block text-sm font-medium text-gray-700 mb-1">
                    SIP Domain <span class="text-red-500">*</span>
                </label>
                <input type="text" id="sipDomain" wire:model="sipDomain" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('sipDomain') border-red-500 @enderror"
                       placeholder="sip.example.com">
                @error('sipDomain')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Your SIP server domain</p>
            </div>

            <!-- Display Name -->
            <div>
                <label for="displayName" class="block text-sm font-medium text-gray-700 mb-1">
                    Display Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="displayName" wire:model="displayName" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('displayName') border-red-500 @enderror"
                       placeholder="John Doe">
                @error('displayName')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Name displayed on outgoing calls</p>
            </div>

            <!-- Auto Register -->
            <div class="flex items-center">
                <input type="checkbox" id="autoRegister" wire:model="autoRegister" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="autoRegister" class="ml-2 block text-sm text-gray-700">
                    Auto-register on Agent Console
                </label>
            </div>
            <p class="text-xs text-gray-500 pl-6">Automatically connect to SIP server when you become available</p>

            <!-- Actions -->
            <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                <button type="submit" wire:loading.attr="disabled" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="save">Save Credentials</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
                
                <button type="button" wire:click="loadCredentials" wire:loading.attr="disabled"
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-blue-900 mb-2">Need Help?</h3>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>• Contact your system administrator for SIP credentials</li>
            <li>• Ensure you have a stable internet connection for WebRTC calls</li>
            <li>• Test your credentials in the Agent Console after saving</li>
        </ul>
    </div>
</div>
