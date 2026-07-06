@props(['options' => [], 'placeholder' => 'Pilih...'])

@php
    $wireModel = $attributes->wire('model');
    // Jika tidak ada wire:model, abaikan fitur entangle
    $hasWireModel = $wireModel->value() !== null;
@endphp

<div x-data="{
        open: false,
        @if($hasWireModel)
            value: @entangle($wireModel).live,
        @else
            value: '{{ $attributes->get('value', '') }}',
        @endif
        options: {{ json_encode(collect($options)->map(fn($label, $val) => ['value' => $val, 'label' => $label])->values()) }},
        placeholder: '{{ $placeholder }}',
        get selectedLabel() {
            let opt = this.options.find(o => String(o.value) === String(this.value));
            return opt ? opt.label : this.placeholder;
        },
        selectOption(val) {
            this.value = val;
            this.open = false;
            // Jika ada event change yang perlu ditrigger
            this.$dispatch('change', val);
        }
    }" 
    class="relative w-full"
    @click.away="open = false"
>
    <!-- Hidden Select (untuk fallback & native form submission jika perlu) -->
    <select class="hidden" {{ $attributes }}>
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $val => $label)
            <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
    </select>

    <!-- Trigger Button -->
    <button type="button" @click="open = !open"
            class="input-brutal w-full text-left text-sm font-bold border-2 border-[var(--color-brutal)] shadow-[2px_2px_0_var(--color-brutal)] py-2 px-3 focus:outline-none focus:ring-2 focus:ring-[var(--color-brutal)] bg-white flex justify-between items-center transition-all"
            :class="open ? 'translate-y-0.5 shadow-[0px_0px_0_var(--color-brutal)]' : 'hover:-translate-y-0.5 hover:shadow-[3px_3px_0_var(--color-brutal)]'"
    >
        <span x-text="selectedLabel" :class="!value ? 'text-[var(--color-muted)]' : 'text-[var(--color-ink)]'"></span>
        <svg class="w-4 h-4 ml-2 transition-transform duration-200 text-[var(--color-brutal)]" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown List -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
         class="absolute z-50 w-full mt-2 bg-white border-2 border-[var(--color-brutal)] shadow-[4px_4px_0_var(--color-brutal)] max-h-60 overflow-y-auto"
         style="display: none;">
        
        <template x-for="(option, index) in options" :key="index">
            <div @click="selectOption(option.value)"
                 class="px-3 py-2.5 text-sm font-bold cursor-pointer hover:bg-[var(--color-primary)] hover:text-white border-b-2 border-transparent hover:border-[var(--color-brutal)] transition-all flex items-center justify-between"
                 :class="{'bg-[var(--color-primary-soft)] text-[var(--color-brutal)]': String(value) === String(option.value)}">
                <span x-text="option.label"></span>
                <svg x-show="String(value) === String(option.value)" class="w-4 h-4 text-[var(--color-brutal)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </template>
        
        <!-- Jika opsi kosong -->
        <template x-if="options.length === 0">
            <div class="px-3 py-2.5 text-sm text-[var(--color-muted)] font-bold italic text-center">
                Tidak ada opsi
            </div>
        </template>
    </div>
</div>
