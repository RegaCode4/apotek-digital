<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Sistem' }} — Apotek Digital</title>

    <link rel="icon" href="/logo.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-[var(--color-bg)] text-[var(--color-ink)] antialiased">

<div class="flex min-h-screen" x-data="{
    sidebarOpen: localStorage.getItem('sidebar-collapsed') !== 'true',
    inventarisOpen: false,
    toggle() {
        this.sidebarOpen = !this.sidebarOpen;
        localStorage.setItem('sidebar-collapsed', !this.sidebarOpen);
    }
}">

    {{-- ══════════════════════════════════════════════
         BILAH SISI
    ══════════════════════════════════════════════ --}}
    <aside
        :class="sidebarOpen ? 'w-56' : 'w-14'"
        class="relative flex shrink-0 flex-col border-r-2 border-[var(--color-brutal)] bg-[var(--color-sidebar)] transition-all duration-300 ease-in-out text-[var(--color-sidebar-text)]"
    >
        {{-- Logo / Merek + Tombol Toggle --}}
        <div class="flex h-14 items-center gap-2.5 border-b-2 border-[var(--color-brutal)] px-3 bg-[var(--color-sidebar)]">
            <img src="/logo.svg" alt="Apotek Digital" class="h-8 w-8 shrink-0 rounded-lg border border-[var(--color-brutal)]">
            <span
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity duration-150 delay-100"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="flex-1 truncate text-sm font-bold text-[var(--color-sidebar-text)]"
            >Apotek Digital</span>
            <button
                type="button"
                @click="toggle()"
                class="ml-auto flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-[var(--color-sidebar-muted)] transition-colors hover:bg-white/10 hover:text-[var(--color-sidebar-text)]"
                :aria-label="sidebarOpen ? 'Tutup sidebar' : 'Buka sidebar'"
                :title="sidebarOpen ? 'Tutup sidebar' : 'Buka sidebar'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300" :class="sidebarOpen ? '' : 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>

        {{-- Navigasi --}}
        <nav class="flex-1 overflow-y-auto p-2 space-y-1" aria-label="Navigasi utama">

            {{-- Dashboard --}}
            @php $isDashboard = request()->routeIs('sistem.dashboard'); @endphp
            <div class="group/nav relative mb-0.5">
                <a href="{{ route('sistem.dashboard') }}"
                   class="flex items-center gap-2.5 rounded-lg border-2 px-2.5 py-1.5 text-sm font-medium transition-all
                       {{ $isDashboard
                           ? 'border-[var(--color-brutal)] bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] shadow-[2px_2px_0_var(--color-brutal)]'
                           : 'border-transparent text-[var(--color-sidebar-muted)] hover:bg-white/10 hover:text-[var(--color-sidebar-text)]' }}"
                   aria-current="{{ $isDashboard ? 'page' : 'false' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-current" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition-opacity duration-150 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="truncate">Dashboard</span>
                </a>
                <span x-show="!sidebarOpen" class="pointer-events-none absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md border border-[var(--color-brutal)] bg-[var(--color-brutal)] px-2 py-1 text-xs font-medium text-[var(--color-sidebar-text)] opacity-0 shadow transition-opacity group-hover/nav:opacity-100">Dashboard</span>
            </div>

            {{-- POS / Kasir --}}
            @php $isPosGroup = request()->routeIs('pos.kasir') || request()->routeIs('pos.riwayat'); @endphp
            <div class="mb-0.5 space-y-1">
                <div class="group/nav relative">
                    <a href="{{ route('pos.kasir') }}"
                       class="flex items-center gap-2.5 rounded-lg border-2 px-2.5 py-1.5 text-sm font-medium transition-all
                           {{ $isPosGroup
                               ? 'border-[var(--color-brutal)] bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] shadow-[2px_2px_0_var(--color-brutal)]'
                               : 'border-transparent text-[var(--color-sidebar-muted)] hover:bg-white/10 hover:text-[var(--color-sidebar-text)]' }}"
                       aria-current="{{ request()->routeIs('pos.kasir') ? 'page' : 'false' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-current" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                        <span x-show="sidebarOpen" x-transition:enter="transition-opacity duration-150 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="truncate">POS / Kasir</span>
                    </a>
                    <span x-show="!sidebarOpen" class="pointer-events-none absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md border border-[var(--color-brutal)] bg-[var(--color-brutal)] px-2 py-1 text-xs font-medium text-[var(--color-sidebar-text)] opacity-0 shadow transition-opacity group-hover/nav:opacity-100">POS / Kasir</span>
                </div>
                <a href="{{ route('pos.riwayat') }}"
                   x-show="sidebarOpen"
                   class="flex items-center gap-2.5 rounded-lg border-2 px-2.5 py-1 text-xs font-medium transition-all pl-8
                       {{ request()->routeIs('pos.riwayat')
                           ? 'border-[var(--color-brutal)] bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] shadow-[1px_1px_0_var(--color-brutal)]'
                           : 'border-transparent text-[var(--color-sidebar-muted)] hover:bg-white/5 hover:text-[var(--color-sidebar-text)]' }}"
                   aria-current="{{ request()->routeIs('pos.riwayat') ? 'page' : 'false' }}"
                >
                    Riwayat Transaksi
                </a>
            </div>

            {{-- Inventaris (tarik turun) --}}
            @php $isInventarisGroup = request()->routeIs('inventaris.*'); @endphp
            @if (in_array(auth()->user()->role, ['admin', 'pharmacist']))
                <div class="mb-0.5 space-y-1">
                    <div class="group/nav relative">
                        <button
                            type="button"
                            @click="sidebarOpen && (inventarisOpen = !inventarisOpen)"
                            class="flex w-full items-center gap-2.5 rounded-lg border-2 px-2.5 py-1.5 text-sm font-medium transition-all
                                {{ $isInventarisGroup
                                    ? 'border-[var(--color-brutal)] bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] shadow-[2px_2px_0_var(--color-brutal)]'
                                    : 'border-transparent text-[var(--color-sidebar-muted)] hover:bg-white/10 hover:text-[var(--color-sidebar-text)]' }}"
                            :aria-expanded="inventarisOpen"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-current" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"/>
                            </svg>
                            <span x-show="sidebarOpen" x-transition:enter="transition-opacity duration-150 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex-1 truncate text-left">Inventaris</span>
                            <svg x-show="sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0 transition-transform" :class="inventarisOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <span x-show="!sidebarOpen" class="pointer-events-none absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md border border-[var(--color-brutal)] bg-[var(--color-brutal)] px-2 py-1 text-xs font-medium text-[var(--color-sidebar-text)] opacity-0 shadow transition-opacity group-hover/nav:opacity-100">Inventaris</span>
                    </div>

                    <div
                        x-show="sidebarOpen && (inventarisOpen || {{ $isInventarisGroup ? 'true' : 'false' }})"
                        x-init="inventarisOpen = {{ $isInventarisGroup ? 'true' : 'false' }}"
                        class="mt-0.5 space-y-1 pl-2"
                    >
                        @foreach ([
                            ['route' => 'inventaris.medicines.index', 'label' => 'Daftar Obat'],
                            ['route' => 'inventaris.kategori',        'label' => 'Kategori Obat'],
                            ['route' => 'inventaris.stok-opname',     'label' => 'Stok Opname'],
                            ['route' => 'inventaris.mutasi',          'label' => 'Mutasi Stok'],
                        ] as $item)
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-2 rounded-lg border-2 px-2.5 py-1 text-xs font-medium transition-all
                                   {{ request()->routeIs($item['route'])
                                       ? 'border-[var(--color-brutal)] bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] shadow-[1px_1px_0_var(--color-brutal)]'
                                       : 'border-transparent text-[var(--color-sidebar-muted)] hover:bg-white/5 hover:text-[var(--color-sidebar-text)]' }}"
                               aria-current="{{ request()->routeIs($item['route']) ? 'page' : 'false' }}"
                            >
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full border border-[var(--color-brutal)] {{ request()->routeIs($item['route']) ? 'bg-white' : 'bg-[var(--color-sidebar-muted)]' }}"></span>
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Laporan (admin & apoteker saja) --}}
            @if (in_array(auth()->user()->role, ['admin', 'pharmacist']))
                @php $isLaporan = request()->routeIs('laporan.index'); @endphp
                <div class="group/nav relative mb-0.5">
                    <a href="{{ route('laporan.index') }}"
                       class="flex items-center gap-2.5 rounded-lg border-2 px-2.5 py-1.5 text-sm font-medium transition-all
                           {{ $isLaporan
                               ? 'border-[var(--color-brutal)] bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] shadow-[2px_2px_0_var(--color-brutal)]'
                               : 'border-transparent text-[var(--color-sidebar-muted)] hover:bg-white/10 hover:text-[var(--color-sidebar-text)]' }}"
                       aria-current="{{ $isLaporan ? 'page' : 'false' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-current" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd"/>
                        </svg>
                        <span x-show="sidebarOpen" x-transition:enter="transition-opacity duration-150 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="truncate">Laporan</span>
                    </a>
                    <span x-show="!sidebarOpen" class="pointer-events-none absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md border border-[var(--color-brutal)] bg-[var(--color-brutal)] px-2 py-1 text-xs font-medium text-[var(--color-sidebar-text)] opacity-0 shadow transition-opacity group-hover/nav:opacity-100">Laporan</span>
                </div>
            @endif

            {{-- Manajemen Pengguna (admin saja) --}}
            @if (auth()->user()->role === 'admin')
                @php $isUsers = request()->routeIs('admin.users'); @endphp
                <div class="group/nav relative mb-0.5">
                    <a href="{{ route('admin.users') }}"
                       class="flex items-center gap-2.5 rounded-lg border-2 px-2.5 py-1.5 text-sm font-medium transition-all
                           {{ $isUsers
                               ? 'border-[var(--color-brutal)] bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] shadow-[2px_2px_0_var(--color-brutal)]'
                               : 'border-transparent text-[var(--color-sidebar-muted)] hover:bg-white/10 hover:text-[var(--color-sidebar-text)]' }}"
                       aria-current="{{ $isUsers ? 'page' : 'false' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-current" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        <span x-show="sidebarOpen" x-transition:enter="transition-opacity duration-150 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="truncate">User Management</span>
                    </a>
                    <span x-show="!sidebarOpen" class="pointer-events-none absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md border border-[var(--color-brutal)] bg-[var(--color-brutal)] px-2 py-1 text-xs font-medium text-[var(--color-sidebar-text)] opacity-0 shadow transition-opacity group-hover/nav:opacity-100">User Management</span>
                </div>
            @endif

        </nav>

        {{-- Info pengguna + logout --}}
        <div class="border-t-2 border-[var(--color-brutal)] p-2">
            <div class="group/nav relative flex items-center gap-2.5 rounded-lg px-2.5 py-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[var(--color-primary-soft)] text-xs font-bold text-[var(--color-primary-contrast)] border-2 border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)]">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-150 delay-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="min-w-0 flex-1">
                    <p class="truncate text-xs font-bold text-[var(--color-sidebar-text)]">{{ auth()->user()->name }}</p>
                    <p class="truncate text-[10px] capitalize text-[var(--color-sidebar-muted)]">{{ auth()->user()->role }}</p>
                </div>
                <form x-show="sidebarOpen" action="{{ route('sistem.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-md p-1.5 text-[var(--color-sidebar-muted)] hover:bg-white/10 hover:text-[var(--color-sidebar-text)]" title="Logout" aria-label="Logout">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </form>
                {{-- Tooltip nama pengguna saat diciutkan --}}
                <span x-show="!sidebarOpen" class="pointer-events-none absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md border border-[var(--color-brutal)] bg-[var(--color-brutal)] px-2 py-1 text-xs font-medium text-[var(--color-sidebar-text)] opacity-0 shadow transition-opacity group-hover/nav:opacity-100">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </aside>

        {{-- ══════════════════════════════════════════════
             AREA KONTEN UTAMA
        ══════════════════════════════════════════════ --}}
    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Bilah atas --}}
        <header class="flex h-14 shrink-0 items-center justify-between border-b-2 border-[var(--color-brutal)] bg-[var(--color-surface)] px-6">
            <h1 class="text-base font-bold text-[var(--color-ink)]">{{ $title ?? 'Sistem Internal' }}</h1>
            <p class="text-xs font-medium text-[var(--color-muted)]">{{ now()->translatedFormat('l, d F Y') }}</p>
        </header>

        <main class="flex-1 overflow-y-auto p-6 bg-[var(--color-bg)]">
            {{ $slot }}
        </main>
    </div>
</div>

@livewire('components.toast-notification')

@livewireScripts
</body>
</html>
