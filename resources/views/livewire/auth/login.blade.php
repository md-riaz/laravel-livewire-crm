<div class="bg-white rounded-lg shadow-lg p-8">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900">Sign In</h2>
        <p class="mt-2 text-sm text-gray-600">Welcome back to your CRM</p>
    </div>

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-600">{{ session('error') }}</p>
        </div>
    @endif

    <form wire:submit="login" class="space-y-6">
        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" id="email" wire:model="email" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror">
            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" wire:model="password" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-500 @enderror">
            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input type="checkbox" id="remember" wire:model="remember" 
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="remember" class="ml-2 block text-sm text-gray-900">
                Remember me
            </label>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" wire:loading.attr="disabled" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                <span wire:loading.remove>Sign In</span>
                <span wire:loading>Signing in...</span>
            </button>
        </div>

        <div class="text-center text-sm">
            <span class="text-gray-600">Don't have an account?</span>
            <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500">
                Register your company
            </a>
        </div>
    </form>
</div>
