<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Sistem' }} — Apotek Digital</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-zinc-100 text-zinc-900 antialiased">
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div>
                <p class="text-sm font-medium text-zinc-500">Apotek Digital</p>
                <h1 class="text-lg font-semibold">{{ $title ?? 'Sistem Internal' }}</h1>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right text-sm">
                    <p class="font-medium">{{ auth()->user()->name }}</p>
                    <p class="text-zinc-500 capitalize">{{ auth()->user()->role }}</p>
                </div>

                <form action="{{ route('sistem.logout') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="rounded-md border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                    >
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <livewire:components.toast-notification />

    @livewireScripts
</body>
</html>
