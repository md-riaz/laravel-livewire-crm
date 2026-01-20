<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Settings - Laravel Livewire CRM' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Main Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="flex items-center justify-center h-16 bg-gray-800">
                <a href="{{ route('dashboard') }}" class="text-xl font-semibold">CRM</a>
            </div>
            <nav class="flex-1 px-4 py-4 space-y-2">
                <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-800">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Dashboard
                </a>
            </nav>
            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400">{{ auth()->user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-400 hover:text-white">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Content area with settings sidebar -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Settings Sidebar -->
            <aside class="w-64 bg-white border-r border-gray-200 overflow-y-auto">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Settings</h2>
                </div>
                <nav class="px-3 pb-6">
                    <a href="{{ route('settings.sip-credentials') }}" 
                       class="flex items-center px-3 py-2 mb-1 rounded-lg {{ request()->routeIs('settings.sip-credentials') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>My SIP Credentials</span>
                    </a>

                    @if(auth()->user()->role === 'tenant_admin')
                        <div class="mt-6 mb-2 px-3 text-xs font-semibold text-gray-500 uppercase">
                            Administration
                        </div>
                        
                        <a href="{{ route('settings.lead-statuses') }}" 
                           class="flex items-center px-3 py-2 mb-1 rounded-lg {{ request()->routeIs('settings.lead-statuses') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span>Lead Statuses</span>
                        </a>

                        <a href="{{ route('settings.call-dispositions') }}" 
                           class="flex items-center px-3 py-2 mb-1 rounded-lg {{ request()->routeIs('settings.call-dispositions') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span>Call Dispositions</span>
                        </a>

                        <a href="{{ route('settings.users') }}" 
                           class="flex items-center px-3 py-2 mb-1 rounded-lg {{ request()->routeIs('settings.users') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Users Management</span>
                        </a>
                    @endif
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="py-6 px-8">
                    @if (session('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    @stack('scripts')
    @livewireScripts
</body>
</html>
