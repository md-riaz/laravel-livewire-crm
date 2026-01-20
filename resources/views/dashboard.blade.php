<x-layouts.app>
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm font-medium">Total Leads</h3>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ \App\Models\Lead::count() }}</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm font-medium">Active Calls Today</h3>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ \App\Models\Call::whereDate('started_at', today())->count() }}</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm font-medium">Team Members</h3>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ \App\Models\User::where('is_active', true)->count() }}</p>
            </div>
        </div>

        <div class="mt-8 bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Welcome to Laravel Livewire CRM</h2>
            <p class="text-gray-600">Your multi-tenant CRM system is ready to use. Navigate to Leads to start managing your pipeline.</p>
        </div>
    </div>
</x-layouts.app>
