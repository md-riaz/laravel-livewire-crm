<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false }" x-cloak>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard - Laravel Livewire CRM' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col" 
               :class="{ 'hidden': !sidebarOpen, 'block': sidebarOpen }"
               @click.away="sidebarOpen = false">
            <div class="flex items-center justify-center h-16 bg-gray-800">
                <span class="text-xl font-semibold">CRM</span>
            </div>
            <nav class="flex-1 px-4 py-4 space-y-2">
                <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('dashboard') ? 'bg-gray-800' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('leads.index') }}" class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('leads.*') ? 'bg-gray-800' : '' }}">
                    Leads
                </a>
                <a href="{{ route('calls.index') }}" class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('calls.*') ? 'bg-gray-800' : '' }}">
                    Calls
                </a>
                @if(in_array(auth()->user()->role, ['agent', 'supervisor', 'tenant_admin']))
                <a href="{{ route('agent.console') }}" class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('agent.console') ? 'bg-gray-800' : '' }}">
                    Agent Console
                </a>
                @endif
                
                <!-- Settings Dropdown -->
                <div x-data="{ open: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('settings.*') ? 'bg-gray-800' : '' }}">
                        <span>Settings</span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="ml-4 mt-1 space-y-1">
                        <a href="{{ route('settings.sip-credentials') }}" class="block px-4 py-2 text-sm rounded hover:bg-gray-800 {{ request()->routeIs('settings.sip-credentials') ? 'bg-gray-800' : '' }}">
                            My SIP Credentials
                        </a>
                        @if(auth()->user()->role === 'tenant_admin')
                            <a href="{{ route('settings.lead-statuses') }}" class="block px-4 py-2 text-sm rounded hover:bg-gray-800 {{ request()->routeIs('settings.lead-statuses') ? 'bg-gray-800' : '' }}">
                                Lead Statuses
                            </a>
                            <a href="{{ route('settings.call-dispositions') }}" class="block px-4 py-2 text-sm rounded hover:bg-gray-800 {{ request()->routeIs('settings.call-dispositions') ? 'bg-gray-800' : '' }}">
                                Call Dispositions
                            </a>
                            <a href="{{ route('settings.users') }}" class="block px-4 py-2 text-sm rounded hover:bg-gray-800 {{ request()->routeIs('settings.users') ? 'bg-gray-800' : '' }}">
                                Users Management
                            </a>
                        @endif
                    </div>
                </div>
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

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top bar -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center px-6">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none lg:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="flex-1"></div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ auth()->user()->tenant->name }}</span>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
    @livewireScripts
</body>
</html>
