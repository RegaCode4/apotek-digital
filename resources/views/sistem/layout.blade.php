<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sistem') — Apotek Digital</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-zinc-100 text-zinc-900 antialiased">

<div class="flex min-h-screen" x-data="{ sidebarOpen: true, inventarisOpen: false }">

    {{-- ══════════════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════════════ --}}
    <aside
        :class="sidebarOpen ? 'w-56' : 'w-14'"
        class="relative flex shrink-0 flex-col border-r border-zinc-200 bg-white transition-all duration-200"
    >
        {{-- Logo / Brand --}}
        <div class="flex h-14 items-center gap-2.5 border-b border-zinc-100 px-3">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-zinc-900 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <span x-show="sidebarOpen" class="text-sm font-semibold text-zinc-900 truncate">Apotek Digital</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto p-2" aria-label="Navigasi utama">

            {{-- Dashboard --}}
            @php $isDashboard = request()->routeIs('sistem.dashboard'); @endphp
            <a href="{{ route('sistem.dashboard') }}"
               class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors mb-0.5
                   {{ $isDashboard ? 'bg-zinc-900 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}"
               aria-current="{{ $isDashboard ? 'page' : 'false' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
                <span x-show="sidebarOpen" class="truncate">Dashboard</span>
            </a>

            {{-- POS / Kasir --}}
            @php $isPosGroup = request()->routeIs('pos.kasir') || request()->routeIs('pos.riwayat'); @endphp
            <div class="mb-0.5">
                <a href="{{ route('pos.kasir') }}"
                   class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors
                       {{ $isPosGroup ? 'bg-zinc-900 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}"
                   aria-current="{{ request()->routeIs('pos.kasir') ? 'page' : 'false' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    </svg>
                    <span x-show="sidebarOpen" class="truncate">POS / Kasir</span>
                </a>
                <a href="{{ route('pos.riwayat') }}"
                   x-show="sidebarOpen"
                   class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 pl-8 text-xs font-medium transition-colors
                       {{ request()->routeIs('pos.riwayat') ? 'text-zinc-900 bg-zinc-100' : 'text-zinc-500 hover:bg-zinc-50 hover:text-zinc-700' }}"
                   aria-current="{{ request()->routeIs('pos.riwayat') ? 'page' : 'false' }}"
                >
                    Riwayat Transaksi
                </a>
            </div>

            {{-- Inventaris (dropdown) --}}
            @php $isInventarisGroup = request()->routeIs('inventaris.*'); @endphp
            @if (in_array(auth()->user()->role, ['admin', 'pharmacist']))
                <div class="mb-0.5">
                    <button
                        type="button"
                        @click="inventarisOpen = !inventarisOpen"
                        class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors
                            {{ $isInventarisGroup ? 'bg-zinc-100 text-zinc-900' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}"
                        :aria-expanded="inventarisOpen"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"/>
                        </svg>
                        <span x-show="sidebarOpen" class="flex-1 truncate text-left">Inventaris</span>
                        <svg x-show="sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0 transition-transform" :class="inventarisOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <div
                        x-show="sidebarOpen && (inventarisOpen || {{ $isInventarisGroup ? 'true' : 'false' }})"
                        x-init="inventarisOpen = {{ $isInventarisGroup ? 'true' : 'false' }}"
                        class="mt-0.5 space-y-0.5 pl-2"
                    >
                        @foreach ([
                            ['route' => 'inventaris.medicines.index', 'label' => 'Daftar Obat'],
                            ['route' => 'inventaris.stok-opname',     'label' => 'Stok Opname'],
                            ['route' => 'inventaris.mutasi',          'label' => 'Mutasi Stok'],
                        ] as $item)
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium transition-colors
                                   {{ request()->routeIs($item['route']) ? 'bg-zinc-900 text-white' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900' }}"
                               aria-current="{{ request()->routeIs($item['route']) ? 'page' : 'false' }}"
                            >
                                <span class="h-1 w-1 shrink-0 rounded-full {{ request()->routeIs($item['route']) ? 'bg-white' : 'bg-zinc-300' }}"></span>
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Laporan (admin & pharmacist only) --}}
            @if (in_array(auth()->user()->role, ['admin', 'pharmacist']))
                @php $isLaporan = request()->routeIs('sistem.laporan'); @endphp
                <a href="{{ route('sistem.laporan') }}"
                   class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors mb-0.5
                       {{ $isLaporan ? 'bg-zinc-900 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}"
                   aria-current="{{ $isLaporan ? 'page' : 'false' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd"/>
                    </svg>
                    <span x-show="sidebarOpen" class="truncate">Laporan</span>
                </a>
            @endif

            {{-- User Management (admin only) --}}
            @if (auth()->user()->role === 'admin')
                @php $isUsers = request()->routeIs('admin.users'); @endphp
                <a href="{{ route('admin.users') }}"
                   class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors mb-0.5
                       {{ $isUsers ? 'bg-zinc-900 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}"
                   aria-current="{{ $isUsers ? 'page' : 'false' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="truncate">User Management</span>
                </a>
            @endif

        </nav>

        {{-- User info + logout --}}
        <div class="border-t border-zinc-100 p-2">
            <div class="flex items-center gap-2.5 rounded-lg px-2.5 py-2">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-bold text-zinc-700">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div x-show="sidebarOpen" class="min-w-0 flex-1">
                    <p class="truncate text-xs font-medium text-zinc-800">{{ auth()->user()->name }}</p>
                    <p class="truncate text-[10px] capitalize text-zinc-400">{{ auth()->user()->role }}</p>
                </div>
                <form x-show="sidebarOpen" action="{{ route('sistem.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-md p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600" title="Logout" aria-label="Logout">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </form>
            </div>

            {{-- Collapse toggle --}}
            <button
                type="button"
                @click="sidebarOpen = !sidebarOpen"
                class="mt-1 flex w-full items-center justify-center rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600"
                :aria-label="sidebarOpen ? 'Tutup sidebar' : 'Buka sidebar'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform" :class="sidebarOpen ? '' : 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════
         MAIN CONTENT AREA
    ══════════════════════════════════════════════ --}}
    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Top bar --}}
        <header class="flex h-14 shrink-0 items-center justify-between border-b border-zinc-200 bg-white px-6">
            <h1 class="text-base font-semibold text-zinc-900">@yield('title', 'Sistem Internal')</h1>
            <p class="text-xs text-zinc-400">{{ now()->translatedFormat('l, d F Y') }}</p>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

@livewire('components.toast-notification')

@livewireScripts
</body>
</html>
