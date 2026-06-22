<div>
<div class="flex min-h-0 gap-4">

    {{-- ════════════════════════════════════════════════════════
         KOLOM KIRI — Pencarian & Katalog Obat
    ════════════════════════════════════════════════════════ --}}
    <div class="flex w-[560px] shrink-0 flex-col gap-4">

        {{-- Header panel kiri --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-base font-semibold text-zinc-900">Cari Obat</h2>
            <input
                type="search"
                wire:model.live.300ms="search"
                placeholder="Ketik nama merek atau generik..."
                class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                autocomplete="off"
            />
            @if (strlen($search) > 0 && strlen($search) < 2)
                <p class="mt-1.5 text-xs text-zinc-400">Ketik minimal 2 karakter...</p>
            @endif

            {{-- Filter kategori --}}
            @if ($categories->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <button
                        type="button"
                        wire:click="selectCategory(null)"
                        class="rounded-full border px-2.5 py-1 text-xs font-medium transition-colors
                            {{ $categoryId === null
                                ? 'border-zinc-900 bg-zinc-900 text-white'
                                : 'border-zinc-300 bg-white text-zinc-600 hover:border-zinc-400 hover:bg-zinc-50' }}"
                    >
                        Semua
                    </button>
                    @foreach ($categories as $category)
                        <button
                            type="button"
                            wire:click="selectCategory({{ $category->id }})"
                            wire:key="cat-{{ $category->id }}"
                            class="rounded-full border px-2.5 py-1 text-xs font-medium transition-colors
                                {{ $categoryId === $category->id
                                    ? 'border-zinc-900 bg-zinc-900 text-white'
                                    : 'border-zinc-300 bg-white text-zinc-600 hover:border-zinc-400 hover:bg-zinc-50' }}"
                        >
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Daftar obat — hasil filter, atau obat terlaris saat belum memfilter --}}
        <div>
            @php
                $medicines = $isFiltering ? $searchResults : $topMedicines;
            @endphp

            @if ($isFiltering || $medicines->isNotEmpty())
                <div class="mb-2 flex items-center gap-2">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                        {{ $isFiltering ? 'Hasil Pencarian' : 'Obat Terlaris' }}
                    </h3>
                    @unless ($isFiltering)
                        <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-500">
                            Paling sering terjual
                        </span>
                    @endunless
                </div>
            @endif

            @if ($medicines->isNotEmpty())
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($medicines as $medicine)
                        @php $outOfStock = $medicine->stock <= 0; @endphp
                        <button
                            type="button"
                            wire:click="{{ $outOfStock ? '' : 'addToCart(' . $medicine->id . ')' }}"
                            wire:key="med-{{ $medicine->id }}"
                            @disabled($outOfStock)
                            class="flex h-full flex-col rounded-xl border p-3 text-left transition-colors
                                {{ $outOfStock
                                    ? 'cursor-not-allowed border-red-200 bg-red-50 opacity-70'
                                    : 'cursor-pointer border-zinc-200 bg-white hover:border-zinc-300 hover:bg-zinc-50 active:bg-zinc-100' }}"
                        >
                            <p class="line-clamp-2 text-sm font-medium {{ $outOfStock ? 'text-red-700' : 'text-zinc-900' }}">
                                {{ $medicine->name }}
                            </p>
                            @if ($medicine->generic_name)
                                <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $medicine->generic_name }}</p>
                            @endif

                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                @if ($medicine->requires_prescription)
                                    <span class="inline-flex rounded-full bg-sky-100 px-1.5 py-0.5 text-[10px] font-medium text-sky-700">
                                        Resep
                                    </span>
                                @endif
                                @if ($outOfStock)
                                    <span class="text-xs font-medium text-red-600">Stok habis</span>
                                @else
                                    <span class="text-xs text-zinc-500">Stok: {{ $medicine->stock }}</span>
                                @endif
                            </div>

                            <p class="mt-2 text-sm font-semibold text-zinc-900">
                                Rp {{ number_format($medicine->price, 0, ',', '.') }}
                            </p>
                        </button>
                    @endforeach
                </div>
            @elseif ($isFiltering)
                <div class="rounded-xl border border-zinc-200 bg-white p-6 text-center text-sm text-zinc-500">
                    @if (strlen($search) >= 2)
                        Tidak ada obat ditemukan untuk "{{ $search }}".
                    @else
                        Tidak ada obat pada kategori ini.
                    @endif
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-8 text-center text-sm text-zinc-400">
                    Belum ada data obat terlaris. Ketik untuk mencari obat.
                </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         KOLOM KANAN — Keranjang Belanja
    ════════════════════════════════════════════════════════ --}}
    <div class="flex flex-1 flex-col gap-4">

        {{-- Header keranjang --}}
        <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-4 py-3 shadow-sm">
            <h2 class="text-base font-semibold text-zinc-900">Keranjang Belanja</h2>
            @if (! empty($cart))
                <span class="rounded-full bg-zinc-900 px-2.5 py-0.5 text-xs font-medium text-white">
                    {{ count($cart) }} item
                </span>
            @endif
        </div>

        {{-- Error message --}}
        @if ($errorMessage)
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $errorMessage }}
            </div>
        @endif

        {{-- List item keranjang --}}
        <div>
            @if (empty($cart))
                <div class="flex h-full items-center justify-center rounded-xl border border-dashed border-zinc-300 bg-white">
                    <div class="text-center">
                        <p class="text-sm font-medium text-zinc-500">Keranjang kosong</p>
                        <p class="mt-1 text-xs text-zinc-400">Cari dan klik obat di sebelah kiri untuk menambahkan</p>
                    </div>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($cart as $index => $item)
                        <div wire:key="cart-item-{{ $index }}" class="rounded-xl border border-zinc-200 bg-white p-3 shadow-sm">
                            <div class="flex items-start gap-3">
                                {{-- Info obat --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <p class="text-sm font-medium text-zinc-900">{{ $item['name'] }}</p>
                                        {{-- Non-Fornas badge — hanya muncul saat BPJS dan obat tidak ditanggung --}}
                                        @if ($paymentMethod === 'bpjs' && ! $item['is_fornas'])
                                            <span
                                                title="Obat ini tidak ditanggung BPJS, pembayaran menjadi tanggungan pasien"
                                                class="inline-flex cursor-help items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700"
                                            >
                                                ⚠ Non-Fornas
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-xs text-zinc-500">
                                        Rp {{ number_format($item['unit_price'], 0, ',', '.') }} / pcs
                                    </p>
                                </div>

                                {{-- Kontrol qty --}}
                                <div class="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                        class="flex h-7 w-7 items-center justify-center rounded-md border border-zinc-300 bg-white text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                                        aria-label="Kurangi qty"
                                    >−</button>
                                    <input
                                        type="number"
                                        wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                        value="{{ $item['quantity'] }}"
                                        min="1"
                                        class="h-7 w-12 rounded-md border border-zinc-300 text-center text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                                        aria-label="Jumlah"
                                    />
                                    <button
                                        type="button"
                                        wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                        class="flex h-7 w-7 items-center justify-center rounded-md border border-zinc-300 bg-white text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                                        aria-label="Tambah qty"
                                    >+</button>
                                </div>

                                {{-- Subtotal per item --}}
                                <div class="w-28 text-right">
                                    <p class="text-sm font-semibold text-zinc-900">
                                        Rp {{ number_format($item['unit_price'] * $item['quantity'], 0, ',', '.') }}
                                    </p>
                                </div>

                                {{-- Hapus --}}
                                <button
                                    type="button"
                                    wire:click="removeFromCart({{ $index }})"
                                    class="flex h-7 w-7 items-center justify-center rounded-md text-zinc-400 hover:bg-red-50 hover:text-red-600"
                                    aria-label="Hapus item"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Input no. resep jika diperlukan --}}
                            @if ($item['requires_prescription'])
                                <div class="mt-2 border-t border-zinc-100 pt-2">
                                    <label class="mb-1 block text-xs font-medium text-sky-700">
                                        No. Resep <span class="text-red-500" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.live="cart.{{ $index }}.prescription_no"
                                        placeholder="Nomor resep dokter..."
                                        class="block w-full rounded-md border border-sky-300 px-2.5 py-1.5 text-xs text-zinc-900 placeholder-zinc-400 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                                    />
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Summary & Checkout --}}
        @if (! empty($cart))
            <div class="space-y-3">
                {{-- Summary kalkulasi --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-zinc-600">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($cartSubtotal, 0, ',', '.') }}</span>
                        </div>

                        {{-- Diskon --}}
                        <div class="flex items-center justify-between gap-3">
                            <label for="discountAmount" class="shrink-0 text-zinc-600">Diskon (Rp)</label>
                            <input
                                id="discountAmount"
                                type="number"
                                wire:model.live="discountAmount"
                                min="0"
                                class="w-36 rounded-md border border-zinc-300 px-2.5 py-1 text-right text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                            />
                        </div>

                        {{-- Toggle PPN --}}
                        <div class="flex items-center justify-between">
                            <label for="taxEnabled" class="text-zinc-600">
                                PPN 11%
                                @if ($taxEnabled)
                                    <span class="text-zinc-500">(Rp {{ number_format($taxAmount, 0, ',', '.') }})</span>
                                @endif
                            </label>
                            <button
                                type="button"
                                wire:click="$toggle('taxEnabled')"
                                id="taxEnabled"
                                role="switch"
                                aria-checked="{{ $taxEnabled ? 'true' : 'false' }}"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2
                                    {{ $taxEnabled ? 'bg-zinc-900' : 'bg-zinc-300' }}"
                            >
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                        {{ $taxEnabled ? 'translate-x-4' : 'translate-x-0' }}"
                                ></span>
                            </button>
                        </div>

                        <div class="flex justify-between border-t border-zinc-200 pt-2 text-base font-semibold text-zinc-900">
                            <span>Grand Total</span>
                            <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Form checkout --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                    {{-- Nama pembeli --}}
                    <div class="mb-3">
                        <label for="buyerName" class="mb-1 block text-xs font-medium text-zinc-700">
                            Nama Pembeli <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="buyerName"
                            type="text"
                            wire:model.live="buyerName"
                            placeholder="Nama lengkap pembeli..."
                            class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                        />
                        @error('buyerName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Metode pembayaran --}}
                    <div class="mb-4">
                        <p class="mb-2 text-xs font-medium text-zinc-700">Metode Pembayaran</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['cash' => 'Cash', 'transfer' => 'Transfer', 'bpjs' => 'BPJS', 'insurance' => 'Asuransi'] as $value => $label)
                                <label
                                    class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-colors
                                        {{ $paymentMethod === $value
                                            ? 'border-zinc-900 bg-zinc-900 text-white'
                                            : 'border-zinc-300 bg-white text-zinc-700 hover:border-zinc-400' }}"
                                >
                                    <input
                                        type="radio"
                                        wire:model.live="paymentMethod"
                                        value="{{ $value }}"
                                        class="sr-only"
                                    />
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Panel BPJS — muncul hanya saat payment method = bpjs --}}
                    @if ($paymentMethod === 'bpjs')
                        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                            <p class="mb-2 text-xs font-semibold text-emerald-800">Verifikasi Peserta BPJS</p>

                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    wire:model.live="bpjsNumber"
                                    placeholder="13 digit nomor BPJS..."
                                    maxlength="13"
                                    class="flex-1 rounded-lg border border-emerald-300 bg-white px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    aria-label="Nomor BPJS"
                                />
                                <button
                                    type="button"
                                    wire:click="verifyBpjs"
                                    wire:loading.attr="disabled"
                                    wire:target="verifyBpjs"
                                    class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-800 disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="verifyBpjs">Verifikasi</span>
                                    <span wire:loading wire:target="verifyBpjs">...</span>
                                </button>
                            </div>

                            {{-- Hasil verifikasi --}}
                            @if ($bpjsVerification !== null)
                                @if ($bpjsVerified)
                                    <div class="mt-2 flex items-center gap-2 rounded-md bg-emerald-100 px-3 py-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold text-emerald-800">
                                                {{ $bpjsVerification['name'] }}
                                            </p>
                                            <p class="text-[10px] text-emerald-600">
                                                Kelas {{ $bpjsVerification['kelas'] }} · Terverifikasi
                                            </p>
                                        </div>
                                        <span class="inline-flex rounded-full bg-emerald-200 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                            AKTIF
                                        </span>
                                    </div>
                                @else
                                    <div class="mt-2 flex items-center gap-2 rounded-md bg-red-100 px-3 py-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-red-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <p class="text-xs font-medium text-red-700">
                                            Peserta tidak aktif atau nomor tidak valid.
                                        </p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif

                    {{-- Tombol proses --}}
                    <button
                        type="button"
                        wire:click="processTransaction"
                        wire:loading.attr="disabled"
                        wire:target="processTransaction"
                        @disabled($paymentMethod === 'bpjs' && ! $bpjsVerified)
                        class="flex w-full items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="processTransaction">
                            Proses Transaksi
                        </span>
                        <span wire:loading wire:target="processTransaction" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     MODAL SUKSES
════════════════════════════════════════════════════════ --}}
<div
    x-data="{ show: @entangle('showSuccessModal') }"
    x-show="show"
    x-cloak
    class="relative z-50"
    role="dialog"
    aria-modal="true"
    aria-labelledby="success-modal-title"
>
    <div
        x-show="show"
        x-transition.opacity
        class="fixed inset-0 bg-zinc-900/60"
    ></div>

    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
            x-show="show"
            x-transition
            @click.stop
            class="w-full max-w-sm rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl"
        >
            {{-- Icon sukses --}}
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                <svg class="h-6 w-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h3 id="success-modal-title" class="text-lg font-semibold text-zinc-900">
                Transaksi Berhasil
            </h3>
            <p class="mt-1 text-sm text-zinc-500">
                Nota penjualan telah disimpan.
            </p>

            <div class="mt-4 rounded-lg bg-zinc-50 px-4 py-3">
                <p class="text-xs text-zinc-500">Nomor Invoice</p>
                <p class="mt-0.5 text-base font-bold tracking-wide text-zinc-900">{{ $lastInvoiceNo }}</p>
            </div>

            <div class="mt-5 flex gap-2">
                {{-- Cetak Struk --}}
                @if ($lastSaleId)
                    <a
                        href="{{ route('pos.struk', $lastSaleId) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a1 1 0 001 1h8a1 1 0 001-1v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a1 1 0 00-1-1H6a1 1 0 00-1 1zm2 0h6v3H7V4zm-1 9h8v3H6v-3zm8-4a1 1 0 110 2 1 1 0 010-2z" clip-rule="evenodd"/>
                        </svg>
                        Cetak Struk
                    </a>
                @endif

                {{-- Transaksi Baru --}}
                <button
                    type="button"
                    wire:click="closeSuccessModal"
                    class="flex flex-1 items-center justify-center rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800"
                >
                    Transaksi Baru
                </button>
            </div>
        </div>
    </div>
</div>
</div>
