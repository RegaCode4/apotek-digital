<div>
<div class="flex min-h-0 gap-4">

    {{-- ════════════════════════════════════════════════════════
         KOLOM KIRI — Pencarian & Katalog Obat
    ════════════════════════════════════════════════════════ --}}
    <div class="flex w-[560px] shrink-0 flex-col gap-4">

        {{-- Header panel kiri --}}
        <div class="card-brutal p-4">
            <h2 class="mb-3 text-base font-bold text-[var(--color-ink)]">Cari Obat</h2>
            <input
                type="search"
                wire:model.live.300ms="search"
                placeholder="Ketik nama merek atau generik..."
                class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none"
                autocomplete="off"
            />
            @if (strlen($search) > 0 && strlen($search) < 2)
                <p class="mt-1.5 text-xs font-semibold text-[var(--color-muted)]">Ketik minimal 2 karakter...</p>
            @endif

            {{-- Filter kategori --}}
            @if ($categories->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <button
                        type="button"
                        wire:click="selectCategory(null)"
                        class="rounded-full border-2 border-[var(--color-brutal)] px-3 py-1 text-xs font-bold transition-all cursor-pointer
                            {{ $categoryId === null
                                ? 'bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] shadow-[1px_1px_0_var(--color-brutal)]'
                                : 'bg-[var(--color-surface)] text-[var(--color-muted)] hover:text-[var(--color-ink)]' }}"
                    >
                        Semua
                    </button>
                    @foreach ($categories as $category)
                        <button
                            type="button"
                            wire:click="selectCategory({{ $category->id }})"
                            wire:key="cat-{{ $category->id }}"
                            class="rounded-full border-2 border-[var(--color-brutal)] px-3 py-1 text-xs font-bold transition-all cursor-pointer
                                {{ $categoryId === $category->id
                                    ? 'bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] shadow-[1px_1px_0_var(--color-brutal)]'
                                    : 'bg-[var(--color-surface)] text-[var(--color-muted)] hover:text-[var(--color-ink)]' }}"
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
                    <h3 class="text-xs font-bold uppercase tracking-wide text-[var(--color-muted)]">
                        {{ $isFiltering ? 'Hasil Pencarian' : 'Obat Terlaris' }}
                    </h3>
                    @unless ($isFiltering)
                        <span class="badge-brutal bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] text-[10px] font-bold">
                            Paling sering terjual
                        </span>
                    @endunless
                </div>
            @endif

            @if ($medicines->isNotEmpty())
                <div class="grid grid-cols-2 gap-2.5">
                    @foreach ($medicines as $medicine)
                        @php $outOfStock = $medicine->stock <= 0; @endphp
                        <button
                            type="button"
                            wire:click="{{ $outOfStock ? '' : 'addToCart(' . $medicine->id . ')' }}"
                            wire:key="med-{{ $medicine->id }}"
                            @disabled($outOfStock)
                            class="btn-brutal text-left p-3 flex h-full flex-col transition-all
                                {{ $outOfStock
                                    ? 'cursor-not-allowed bg-[var(--color-danger-soft)] border-[var(--color-brutal)] shadow-none opacity-70'
                                    : 'cursor-pointer bg-[var(--color-surface)] hover:bg-[var(--color-surface-muted)]' }}"
                        >
                            <p class="line-clamp-2 text-sm font-bold {{ $outOfStock ? 'text-[var(--color-danger)]' : 'text-[var(--color-ink)]' }}">
                                {{ $medicine->name }}
                            </p>
                            @if ($medicine->generic_name)
                                <p class="mt-0.5 truncate text-xs text-[var(--color-muted)] font-medium">{{ $medicine->generic_name }}</p>
                            @endif

                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                @if ($medicine->requires_prescription)
                                    <span class="badge-brutal bg-[var(--color-info-soft)] text-[var(--color-ink)] text-[10px]">
                                        Resep
                                    </span>
                                @endif
                                @if ($outOfStock)
                                    <span class="text-xs font-bold text-[var(--color-danger)]">Stok habis</span>
                                @else
                                    <span class="text-xs font-semibold text-[var(--color-muted)]">Stok: {{ $medicine->stock }}</span>
                                @endif
                            </div>

                            <p class="mt-2 text-sm font-extrabold text-[var(--color-ink)]">
                                Rp {{ number_format($medicine->price, 0, ',', '.') }}
                            </p>
                        </button>
                    @endforeach
                </div>
            @elseif ($isFiltering)
                <div class="card-brutal p-6 text-center text-sm font-bold text-[var(--color-muted)]">
                    @if (strlen($search) >= 2)
                        Tidak ada obat ditemukan untuk "{{ $search }}".
                    @else
                        Tidak ada obat pada kategori ini.
                    @endif
                </div>
            @else
                <div class="card-brutal border-dashed p-8 text-center text-sm font-bold text-[var(--color-muted)] bg-[var(--color-surface)]">
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
        <div class="flex items-center justify-between card-brutal px-4 py-3 bg-[var(--color-surface)]">
            <h2 class="text-base font-bold text-[var(--color-ink)]">Keranjang Belanja</h2>
            @if (! empty($cart))
                <span class="badge-brutal bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] font-bold">
                    {{ count($cart) }} item
                </span>
            @endif
        </div>

        {{-- Pesan error --}}
        @if ($errorMessage)
            <div class="card-brutal bg-[var(--color-danger-soft)] text-[var(--color-danger)] px-4 py-3 text-sm font-bold">
                {{ $errorMessage }}
            </div>
        @endif

        {{-- Daftar item keranjang --}}
        <div class="space-y-2.5">
            @if (empty($cart))
                <div class="flex h-32 items-center justify-center card-brutal border-dashed p-6 bg-[var(--color-surface)]">
                    <div class="text-center">
                        <p class="text-sm font-bold text-[var(--color-muted)]">Keranjang kosong</p>
                        <p class="mt-1 text-xs font-semibold text-[var(--color-muted)]/80">Cari dan klik obat di sebelah kiri untuk menambahkan</p>
                    </div>
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach ($cart as $index => $item)
                        <div wire:key="cart-item-{{ $index }}" class="card-brutal p-3">
                            <div class="flex items-start gap-3">
                                {{-- Info obat --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <p class="text-sm font-bold text-[var(--color-ink)]">{{ $item['name'] }}</p>
                                        {{-- Non-Fornas badge — hanya muncul saat BPJS dan obat tidak ditanggung --}}
                                        @if ($paymentMethod === 'bpjs' && ! $item['is_fornas'])
                                            <span
                                                title="Obat ini tidak ditanggung BPJS, pembayaran menjadi tanggungan pasien"
                                                class="badge-brutal bg-[var(--color-warning-soft)] text-[var(--color-warning)] text-[10px] font-bold cursor-help"
                                            >
                                                ⚠ Non-Fornas
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-xs font-semibold text-[var(--color-muted)]">
                                        Rp {{ number_format($item['unit_price'], 0, ',', '.') }} / pcs
                                    </p>
                                </div>

                                {{-- Kontrol qty --}}
                                <div class="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                        class="btn-brutal h-7 w-7 text-xs flex items-center justify-center bg-[var(--color-surface)] hover:bg-[var(--color-surface-muted)] shadow-[2px_2px_0_var(--color-brutal)] cursor-pointer"
                                        aria-label="Kurangi qty"
                                    >−</button>
                                    <input
                                        type="number"
                                        wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                        value="{{ $item['quantity'] }}"
                                        min="1"
                                        class="h-7 w-12 text-center text-sm input-brutal bg-[var(--color-surface)] text-[var(--color-ink)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] shadow-[1px_1px_0_var(--color-brutal)]"
                                        aria-label="Jumlah"
                                    />
                                    <button
                                        type="button"
                                        wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                        class="btn-brutal h-7 w-7 text-xs flex items-center justify-center bg-[var(--color-surface)] hover:bg-[var(--color-surface-muted)] shadow-[2px_2px_0_var(--color-brutal)] cursor-pointer"
                                        aria-label="Tambah qty"
                                    >+</button>
                                </div>

                                {{-- Subtotal per item --}}
                                <div class="w-28 text-right">
                                    <p class="text-sm font-bold text-[var(--color-ink)]">
                                        Rp {{ number_format($item['unit_price'] * $item['quantity'], 0, ',', '.') }}
                                    </p>
                                </div>

                                {{-- Hapus --}}
                                <button
                                    type="button"
                                    wire:click="removeFromCart({{ $index }})"
                                    class="btn-brutal h-7 w-7 flex items-center justify-center bg-[var(--color-danger-soft)] text-[var(--color-danger)] hover:bg-[var(--color-danger)] hover:text-white shadow-[2px_2px_0_var(--color-brutal)] cursor-pointer"
                                    aria-label="Hapus item"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Input no. resep jika diperlukan --}}
                            @if ($item['requires_prescription'])
                                <div class="mt-2 border-t-2 border-[var(--color-brutal)] pt-2">
                                    <label class="mb-1 block text-xs font-bold text-[var(--color-info)]">
                                        No. Resep <span class="text-[var(--color-danger)]" aria-hidden="true">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.live="cart.{{ $index }}.prescription_no"
                                        placeholder="Nomor resep dokter..."
                                        class="block w-full input-brutal text-xs text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]"
                                    />
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Ringkasan & Pembayaran --}}
        @if (! empty($cart))
            <div class="space-y-4">
                {{-- Ringkasan kalkulasi --}}
                <div class="card-brutal p-4">
                    <div class="space-y-2 text-sm font-semibold">
                        <div class="flex justify-between text-[var(--color-muted)]">
                            <span>Subtotal</span>
                            <span class="text-[var(--color-ink)]">Rp {{ number_format($cartSubtotal, 0, ',', '.') }}</span>
                        </div>

                        {{-- Diskon --}}
                        <div class="flex items-center justify-between gap-3">
                            <label for="discountAmount" class="shrink-0 text-[var(--color-muted)]">Diskon (Rp)</label>
                            <input
                                id="discountAmount"
                                type="number"
                                wire:model.live="discountAmount"
                                min="0"
                                class="w-36 input-brutal text-right text-sm text-[var(--color-ink)] focus:outline-none shadow-[2px_2px_0_var(--color-brutal)]"
                            />
                        </div>

                        {{-- Toggle PPN --}}
                        <div class="flex items-center justify-between">
                            <label for="taxEnabled" class="text-[var(--color-muted)]">
                                PPN 11%
                                @if ($taxEnabled)
                                    <span class="text-[var(--color-ink)]">(Rp {{ number_format($taxAmount, 0, ',', '.') }})</span>
                                @endif
                            </label>
                            <button
                                type="button"
                                wire:click="$toggle('taxEnabled')"
                                id="taxEnabled"
                                role="switch"
                                aria-checked="{{ $taxEnabled ? 'true' : 'false' }}"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)] transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:ring-offset-2
                                    {{ $taxEnabled ? 'bg-[var(--color-primary)]' : 'bg-[var(--color-surface-muted)]' }}"
                            >
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full border border-[var(--color-brutal)] bg-white shadow ring-0 transition duration-200 ease-in-out ml-0.5
                                        {{ $taxEnabled ? 'translate-x-5' : 'translate-x-0' }}"
                                ></span>
                            </button>
                        </div>

                        <div class="flex justify-between border-t-2 border-[var(--color-brutal)] pt-2 text-base font-extrabold text-[var(--color-ink)]">
                            <span>Grand Total</span>
                            <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Form checkout --}}
                <div class="card-brutal p-4">
                    {{-- Nama pembeli --}}
                    <div class="mb-3">
                        <label for="buyerName" class="mb-1 block text-xs font-bold text-[var(--color-ink)]">
                            Nama Pembeli <span class="text-[var(--color-danger)]" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="buyerName"
                            type="text"
                            wire:model.live="buyerName"
                            placeholder="Nama lengkap pembeli..."
                            class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)]"
                        />
                        @error('buyerName')
                            <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Metode pembayaran --}}
                    <div class="mb-4">
                        <p class="mb-2 text-xs font-bold text-[var(--color-ink)]">Metode Pembayaran</p>
                        <div class="grid grid-cols-2 gap-2.5">
                            @foreach (['cash' => 'Cash', 'transfer' => 'Transfer', 'bpjs' => 'BPJS', 'insurance' => 'Asuransi'] as $value => $label)
                                <label
                                    class="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-[var(--color-brutal)] px-3 py-2 text-sm font-bold transition-all
                                        {{ $paymentMethod === $value
                                            ? 'bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] shadow-[2px_2px_0_var(--color-brutal)]'
                                            : 'bg-[var(--color-surface)] text-[var(--color-muted)] hover:border-[var(--color-brutal)] hover:text-[var(--color-ink)]' }}"
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

                    {{-- Panel BPJS — muncul hanya saat metode pembayaran = bpjs --}}
                    @if ($paymentMethod === 'bpjs')
                        <div class="mb-4 card-brutal bg-[var(--color-success-soft)] text-[var(--color-ink)] p-3">
                            <p class="mb-2 text-xs font-bold text-[var(--color-brutal)]">Verifikasi Peserta BPJS</p>

                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    wire:model.live="bpjsNumber"
                                    placeholder="13 digit nomor BPJS..."
                                    maxlength="13"
                                    class="flex-1 input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] bg-[var(--color-surface)] focus:outline-none"
                                    aria-label="Nomor BPJS"
                                />
                                <button
                                    type="button"
                                    wire:click="verifyBpjs"
                                    wire:loading.attr="disabled"
                                    wire:target="verifyBpjs"
                                    class="btn-brutal bg-[var(--color-success)] text-white hover:bg-[var(--color-success)] px-3 py-2 text-xs cursor-pointer shadow-[2px_2px_0_var(--color-brutal)] disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="verifyBpjs">Verifikasi</span>
                                    <span wire:loading wire:target="verifyBpjs">...</span>
                                </button>
                            </div>

                            {{-- Hasil verifikasi --}}
                            @if ($bpjsVerification !== null)
                                @if ($bpjsVerified)
                                    <div class="mt-2 flex items-center gap-2 rounded-md border-2 border-[var(--color-brutal)] bg-white px-3 py-2 shadow-[2px_2px_0_var(--color-brutal)]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-[var(--color-success)]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="min-w-0 flex-1 text-[var(--color-ink)]">
                                            <p class="text-xs font-bold">
                                                {{ $bpjsVerification['name'] }}
                                            </p>
                                            <p class="text-[10px] font-semibold text-[var(--color-muted)]">
                                                Kelas {{ $bpjsVerification['kelas'] }} · Terverifikasi
                                            </p>
                                        </div>
                                        <span class="badge-brutal bg-[var(--color-success-soft)] text-[var(--color-success)] font-bold text-[10px]">
                                            AKTIF
                                        </span>
                                    </div>
                                @else
                                    <div class="mt-2 flex items-center gap-2 rounded-md border-2 border-[var(--color-brutal)] bg-[var(--color-danger-soft)] px-3 py-2 shadow-[2px_2px_0_var(--color-brutal)] text-[var(--color-danger)]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <p class="text-xs font-bold">
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
                        class="btn-brutal btn-primary w-full py-3 flex items-center justify-center text-sm font-bold cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
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
        class="fixed inset-0 bg-[var(--color-brutal)]/40 backdrop-blur-xs"
    ></div>

    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
            x-show="show"
            x-transition
            @click.stop
            class="w-full max-w-sm card-brutal p-6 card-brutal-lg bg-[var(--color-surface)]"
        >
            {{-- Icon sukses --}}
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-[var(--color-success-soft)] border-2 border-[var(--color-brutal)] shadow-[2px_2px_0_var(--color-brutal)]">
                <svg class="h-6 w-6 text-[var(--color-brutal)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h3 id="success-modal-title" class="text-lg font-bold text-[var(--color-ink)]">
                Transaksi Berhasil
            </h3>
            <p class="mt-1 text-sm font-semibold text-[var(--color-muted)]">
                Nota penjualan telah disimpan.
            </p>

            <div class="mt-4 card-brutal bg-[var(--color-surface-muted)] p-4 shadow-[2px_2px_0_var(--color-brutal)] border-2 border-[var(--color-brutal)]">
                <p class="text-xs font-bold text-[var(--color-muted)]">Nomor Invoice</p>
                <p class="mt-0.5 text-base font-extrabold tracking-wide text-[var(--color-ink)] font-mono">{{ $lastInvoiceNo }}</p>
            </div>

            <div class="mt-5 flex gap-2.5">
                {{-- Cetak Struk --}}
                @if ($lastSaleId)
                    <a
                        href="{{ route('pos.struk', $lastSaleId) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn-brutal btn-secondary flex-1 py-2.5 flex items-center justify-center gap-1.5 text-sm font-bold cursor-pointer"
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
                    class="btn-brutal btn-primary flex-1 py-2.5 flex items-center justify-center text-sm font-bold cursor-pointer"
                >
                    Transaksi Baru
                </button>
            </div>
        </div>
    </div>
</div>
</div>
