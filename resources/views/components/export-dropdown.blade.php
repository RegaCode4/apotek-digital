@props(['actionCsv', 'actionPdf'])

<div x-data="{ open: false }" class="relative inline-block text-left">
    <button
        type="button"
        @click="open = !open"
        @click.away="open = false"
        class="btn-brutal btn-secondary flex items-center gap-2 px-4 py-2.5 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
    >
        <span>Export Laporan</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-none border-2 border-[var(--color-brutal)] bg-[var(--color-surface)] shadow-[4px_4px_0_var(--color-brutal)]"
        style="display: none;"
    >
        <div class="py-1">
            <button
                type="button"
                wire:click="{{ $actionCsv }}"
                @click="open = false"
                class="block w-full px-4 py-2 text-left text-sm font-bold text-[var(--color-ink)] hover:bg-[var(--color-primary-soft)] transition-colors"
            >
                Export sebagai CSV
            </button>
            <button
                type="button"
                wire:click="{{ $actionPdf }}"
                @click="open = false"
                class="block w-full px-4 py-2 text-left text-sm font-bold text-[var(--color-ink)] hover:bg-[var(--color-primary-soft)] transition-colors border-t-2 border-[var(--color-brutal)]"
            >
                Export sebagai PDF
            </button>
        </div>
    </div>
</div>
