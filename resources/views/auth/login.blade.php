<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login Sistem — Apotek Digital</title>

    <link rel="icon" href="/logo.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4 antialiased relative">
    {{-- Background Image with Blur --}}
    <div class="fixed inset-0 z-[-1]">
        <img src="{{ asset('images/bg-login.webp') }}" alt="Background" class="w-full h-full object-cover blur-sm" />
        <div class="absolute inset-0 bg-white/60"></div>
    </div>

    <div class="w-full max-w-sm">

        {{-- Logo + judul --}}
        <div class="mb-8 text-center">
            <div class="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-xl border-2 border-[var(--color-brutal)] bg-[var(--color-primary-soft)] shadow-[4px_4px_0_var(--color-brutal)]">
                <img src="/logo.svg" alt="Apotek Digital" class="h-8 w-8">
            </div>
            <h1 class="text-2xl font-bold text-[var(--color-ink)]">Apotek Digital</h1>
            <p class="mt-1 text-sm font-semibold text-[var(--color-muted)]">Login Sistem Internal</p>
        </div>

        {{-- Card --}}
        <div class="card-brutal p-6 bg-[var(--color-surface)]" style="box-shadow: 6px 6px 0 var(--color-brutal);">

            {{-- Pesan error --}}
            @if ($errors->any())
                <div class="mb-5 card-brutal bg-[var(--color-danger-soft)] px-4 py-3 text-sm font-bold text-[var(--color-danger)]">
                    <ul class="space-y-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('sistem.login.post') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">
                        Email
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        autofocus
                        value="{{ old('email') }}"
                        placeholder="nama@apotek.com"
                        class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none @error('email') border-[var(--color-danger)] @enderror"
                    />
                </div>

                {{-- Kata Sandi --}}
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">
                        Password
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="Masukkan password"
                            class="block w-full input-brutal pr-10 text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none @error('password') border-[var(--color-danger)] @enderror"
                        />
                        <button
                            type="button"
                            id="togglePassword"
                            onclick="(function(){var i=document.getElementById('password'),b=document.getElementById('togglePassword'),e=document.getElementById('eyeOpen'),c=document.getElementById('eyeClosed');if(i.type==='password'){i.type='text';e.classList.add('hidden');c.classList.remove('hidden');b.setAttribute('aria-label','Sembunyikan password')}else{i.type='password';e.classList.remove('hidden');c.classList.add('hidden');b.setAttribute('aria-label','Tampilkan password')}})()"
                            aria-label="Tampilkan password"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-[var(--color-muted)] hover:text-[var(--color-ink)] transition-colors cursor-pointer"
                        >
                            {{-- Mata terbuka --}}
                            <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            {{-- Mata tertutup --}}
                            <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.450l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.064 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Kirim --}}
                <button
                    type="submit"
                    class="btn-brutal btn-primary w-full py-2.5 text-sm font-bold cursor-pointer flex items-center justify-center"
                >
                    Login
                </button>
            </form>
        </div>
    </div>

</body>
</html>
