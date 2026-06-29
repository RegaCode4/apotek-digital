<div
    aria-live="polite"
    aria-atomic="false"
    class="pointer-events-none fixed bottom-5 right-5 z-[200] flex flex-col items-end gap-2"
>
    @foreach ($toasts as $toast)
        @php
            $styles = match ($toast['type']) {
                'success' => [
                    'bg'     => 'bg-emerald-50 border-emerald-200',
                    'icon'   => 'text-emerald-500',
                    'text'   => 'text-emerald-900',
                    'path'   => 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
                ],
                'error'   => [
                    'bg'     => 'bg-red-50 border-red-200',
                    'icon'   => 'text-red-500',
                    'text'   => 'text-red-900',
                    'path'   => 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z',
                ],
                'warning' => [
                    'bg'     => 'bg-amber-50 border-amber-200',
                    'icon'   => 'text-amber-500',
                    'text'   => 'text-amber-900',
                    'path'   => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z',
                ],
                default   => [
                    'bg'     => 'bg-sky-50 border-sky-200',
                    'icon'   => 'text-sky-500',
                    'text'   => 'text-sky-900',
                    'path'   => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z',
                ],
            };
        @endphp

        <div
            wire:key="toast-{{ $toast['id'] }}"
            x-data="{
                show: false,
                init() {
                    this.$nextTick(() => { this.show = true });
                    setTimeout(() => {
                        this.show = false;
                        setTimeout(() => $wire.dismiss({{ $toast['id'] }}), 300);
                    }, 4000);
                }
            }"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
            role="alert"
            class="pointer-events-auto flex w-80 items-start gap-3 rounded-xl border {{ $styles['bg'] }} p-3.5 shadow-lg"
        >
            {{-- Icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0 {{ $styles['icon'] }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="{{ $styles['path'] }}" clip-rule="evenodd"/>
            </svg>

            {{-- Pesan --}}
            <p class="flex-1 text-sm font-medium {{ $styles['text'] }}">
                {{ $toast['message'] }}
            </p>

            {{-- Tombol tutup --}}
            <button
                type="button"
                @click="show = false; setTimeout(() => $wire.dismiss({{ $toast['id'] }}), 200)"
                class="shrink-0 rounded-md p-0.5 opacity-50 hover:opacity-100 focus:outline-none {{ $styles['icon'] }}"
                aria-label="Tutup notifikasi"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    @endforeach
</div>
