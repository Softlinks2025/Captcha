<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Admin Panel</title>
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen flex">
    <!-- Sidebar -->
    <aside x-data="{ open: false }" class="w-64 bg-white border-r border-gray-200 flex flex-col transition-transform duration-200 ease-in-out transform" :class="{ '-translate-x-full': !open, 'translate-x-0': open }">
        <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-indigo-600 tracking-wide">Admin Panel</a>
            <button class="md:hidden text-gray-500 focus:outline-none" @click="open = false">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                <i class="fas fa-chart-line mr-3"></i> Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                <i class="fas fa-users mr-3"></i> Users
            </a>
            <a href="{{ route('admin.agents.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.agents.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                <i class="fas fa-user-tie mr-3"></i> Agents
            </a>
            <a href="{{ route('admin.subscription_plans.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.subscription_plans.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                <i class="fas fa-cubes mr-3"></i> Subscription Plans
            </a>
            <a href="{{ route('admin.agent-plans.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.agent-plans.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                <i class="fas fa-briefcase mr-3"></i> Agent Plans
            </a>
            @php
                $user = auth()->user();
            @endphp
            @if($user->can('manage users') || $user->hasRole('admin'))
                <a href="{{ route('admin.withdrawal-requests.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.withdrawal-requests.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="fas fa-wallet mr-3"></i> Withdrawal Requests
                </a>
                <a href="{{ route('admin.subadmins.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.subadmins.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="fas fa-user-shield mr-3"></i> Subadmins
                </a>
            @elseif($user->can('manage push notifications'))
                <a href="{{ route('admin.push-notification.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.push-notification.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="fas fa-bell mr-3"></i> Push Notification
                </a>
            @elseif($user->can('manage withdrawals'))
                <a href="{{ route('admin.withdrawal-requests.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.withdrawal-requests.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="fas fa-wallet mr-3"></i> Withdrawal Requests
                </a>
            @endif
            <a href="{{ route('admin.tickets.index') }}" class="flex items-center px-4 py-2 rounded-lg transition-colors {{ request()->routeIs('admin.tickets.*') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                <i class="fas fa-ticket-alt mr-3"></i> Tickets
            </a>
            <a href="#" class="flex items-center px-4 py-2 rounded-lg transition-colors text-gray-700 hover:bg-indigo-50 hover:text-indigo-700" id="settings-toggle">
                <i class="fas fa-cog mr-3"></i> Settings
            </a>
            <div id="settings-menu" style="display: none; margin-left: 2.5rem;">
                <a href="{{ route('admin.change-password') }}" class="block px-4 py-2 text-gray-700 hover:bg-indigo-100 rounded">Change Password</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded">Logout</button>
                </form>
            </div>
        </nav>
        <div class="mt-auto px-4 py-6 border-t border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-full bg-indigo-200 flex items-center justify-center text-indigo-700 font-bold text-lg">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-semibold text-gray-800">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500">Admin</div>
                </div>
            </div>
        </div>
    </aside>
    <!-- Main Content -->
    <div class="flex-1 ml-0 md:ml-64 min-h-screen flex flex-col">
        <header class="bg-white shadow px-6 py-4 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center space-x-4">
                <button class="md:hidden text-gray-500 focus:outline-none" @click="document.querySelector('aside').__x.$data.open = true">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="text-2xl font-bold text-gray-900">@yield('title')</h1>
            </div>
        </header>
        <main class="flex-1 p-6">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
    <script>
        // Settings dropdown toggle
        document.addEventListener('DOMContentLoaded', function() {
            var toggle = document.getElementById('settings-toggle');
            var menu = document.getElementById('settings-menu');
            if (toggle && menu) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
                });
            }
        });
    </script>
</body>
</html>