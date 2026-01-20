<div class="bg-white rounded-lg shadow-lg p-8">
    @if($invalidInvitation)
        <div class="text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Invalid Invitation</h2>
            <p class="text-gray-600 mb-6">{{ $errorMessage }}</p>
            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                Go to Login
            </a>
        </div>
    @else
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Accept Invitation</h2>
            <p class="mt-2 text-sm text-gray-600">Complete your account setup</p>
        </div>

        @if($invitation)
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-sm text-blue-800">
                    <strong>{{ $invitation->invitedBy->name }}</strong> has invited you to join as a <strong>{{ ucfirst(str_replace('_', ' ', $invitation->role)) }}</strong>
                </p>
                <p class="text-xs text-blue-600 mt-1">Email: {{ $invitation->email }}</p>
            </div>
        @endif

        @if ($errors->has('general'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                <p class="text-sm text-red-600">{{ $errors->first('general') }}</p>
            </div>
        @endif

        <form wire:submit="accept" class="space-y-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="name" wire:model="name" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" wire:model="password" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
            </div>

            <!-- Password Confirmation -->
            <div>
                <label for="passwordConfirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" id="passwordConfirmation" wire:model="passwordConfirmation" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" wire:loading.attr="disabled" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                    <span wire:loading.remove>Accept Invitation & Create Account</span>
                    <span wire:loading>Creating Account...</span>
                </button>
            </div>
        </form>

        <div class="mt-6 text-center text-sm">
            <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                Already have an account? Sign in
            </a>
        </div>
    @endif
</div>
