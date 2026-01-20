<div class="bg-white rounded-lg shadow-lg p-8">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900">Register Your Company</h2>
        <p class="mt-2 text-sm text-gray-600">Start using our CRM platform</p>
    </div>

    <form wire:submit="register" class="space-y-6">
        <!-- Company Name -->
        <div>
            <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
            <input type="text" id="company_name" wire:model="company_name" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('company_name') border-red-500 @enderror">
            @error('company_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Owner Name -->
        <div>
            <label for="owner_name" class="block text-sm font-medium text-gray-700">Your Name</label>
            <input type="text" id="owner_name" wire:model="owner_name" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('owner_name') border-red-500 @enderror">
            @error('owner_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="owner_email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" id="owner_email" wire:model="owner_email" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('owner_email') border-red-500 @enderror">
            @error('owner_email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" wire:model="password" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-500 @enderror">
            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Password Confirmation -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" id="password_confirmation" wire:model="password_confirmation" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password_confirmation') border-red-500 @enderror">
            @error('password_confirmation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Timezone -->
        <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
            <select id="timezone" wire:model="timezone" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="UTC">UTC</option>
                <option value="America/New_York">Eastern Time</option>
                <option value="America/Chicago">Central Time</option>
                <option value="America/Denver">Mountain Time</option>
                <option value="America/Los_Angeles">Pacific Time</option>
            </select>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" wire:loading.attr="disabled" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                <span wire:loading.remove>Register Company</span>
                <span wire:loading>Registering...</span>
            </button>
        </div>

        <div class="text-center text-sm">
            <span class="text-gray-600">Already have an account?</span>
            <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                Sign in
            </a>
        </div>
    </form>
</div>
