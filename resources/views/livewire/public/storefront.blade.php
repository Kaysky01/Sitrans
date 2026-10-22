<div class="min-h-screen bg-slate-50/60 pb-16 dark:bg-espresso-950">
    <!-- Storefront Cafe Banner -->
    <div class="relative overflow-hidden bg-gradient-to-r from-navy-900 via-navy-800 to-navy-950 py-10 text-white shadow-md">
        <img src="{{ asset('images/pos-counter.jpg') }}" alt="Cafe ambiance" class="absolute inset-0 h-full w-full object-cover mix-blend-overlay opacity-25" />
        <div class="relative mx-auto max-w-5xl px-4 sm:px-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-navy-900 font-extrabold text-2xl shadow-lg ring-4 ring-white/20">
                        ☕
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-navy-500/25 px-3 py-0.5 text-xs font-bold text-navy-200 backdrop-blur-sm">
                            <span class="h-2 w-2 rounded-full bg-orange-400 animate-pulse"></span>
                            <span>Menu Digital &middot; Pesan Mandiri</span>
                        </div>
                        <h1 class="mt-1 font-display text-2xl sm:text-3xl font-black text-white">{{ $tenant->name }}</h1>
                        @if ($outlet)
                            <p class="mt-0.5 text-xs sm:text-sm text-navy-100/90">{{ $outlet->name }}@if ($outlet->address) &middot; {{ $outlet->address }}@endif</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-4 py-2.5 backdrop-blur-md">
                    <svg class="h-5 w-5 text-navy-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <div>
                        <span class="font-extrabold text-white">{{ $this->cartCount }}</span>
                        <span class="text-xs text-navy-100">item pesanan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        @if ($placedNumber)
            <section class="mb-8 overflow-hidden rounded-3xl border border-navy-500/30 bg-gradient-to-br from-navy-50 via-cream-100 to-white p-6 sm:p-8 shadow-xl dark:border-navy-500/20 dark:from-navy-900 dark:to-espresso-900">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-navy-500/20 pb-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-navy-500 to-navy-400 text-white font-black text-2xl shadow-lg shadow-navy-500/30">
                            ✓
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-navy-100 px-3 py-0.5 text-xs font-bold text-navy-900 dark:bg-navy-900 dark:text-navy-200">
                                <span class="h-2 w-2 rounded-full bg-navy-500 animate-ping"></span>
                                <span>Pesanan Diterima Sistem</span>
                            </div>
                            <h2 class="mt-1 font-display text-2xl font-black text-slate-900 dark:text-cream-100">Terima Kasih atas Pesanan Anda!</h2>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-cream-300">
                                Nomor Antrean: <span class="font-extrabold font-mono text-base text-navy-500 dark:text-navy-200">#{{ $placedNumber }}</span>
                                &middot; Total: <span class="font-extrabold tabular text-slate-900 dark:text-cream-100">{{ \App\Support\Format::rupiah($placedTotal) }}</span>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="startOver" class="rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow-md transition hover:bg-slate-800 dark:bg-cream-100 dark:text-slate-900 dark:hover:bg-white">
                        + Buat Pesanan Baru
                    </button>
                </div>

                <!-- Payment Instructions depending on chosen method -->
                <div class="mt-6">
                    @if ($placedPaymentMethod === 'qris')
                        <div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-soft dark:border-cream-100/10 dark:bg-espresso-800">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-cream-100/5">
                                <span class="rounded bg-rose-600 px-2 py-0.5 text-[11px] font-black text-white tracking-widest">QRIS</span>
                                <span class="text-xs font-bold text-slate-500 dark:text-cream-300">{{ $tenant->name }}</span>
                            </div>
                            <div class="my-4 flex flex-col items-center">
                                <p class="text-xs text-slate-500 mb-2">Scan dengan aplikasi e-wallet atau m-Banking pilihan Anda:</p>
                                <!-- Interactive QRIS Mockup Canvas -->
                                <div class="relative rounded-2xl border-4 border-slate-900 bg-white p-3 shadow-inner">
                                    <svg class="h-44 w-44 text-slate-900" viewBox="0 0 100 100" fill="currentColor">
                                        <!-- QR Pattern Simulation -->
                                        <path d="M0,0 h30 v30 h-30 z M5,5 v20 h20 v-20 z M10,10 h10 v10 h-10 z" />
                                        <path d="M70,0 h30 v30 h-30 z M75,5 v20 h20 v-20 z M80,10 h10 v10 h-10 z" />
                                        <path d="M0,70 h30 v30 h-30 z M5,75 v20 h20 v-20 z M10,80 h10 v10 h-10 z" />
                                        <rect x="35" y="10" width="8" height="8" />
                                        <rect x="50" y="10" width="12" height="6" />
                                        <rect x="35" y="25" width="28" height="6" />
                                        <rect x="10" y="35" width="8" height="15" />
                                        <rect x="25" y="35" width="10" height="8" />
                                        <rect x="40" y="38" width="20" height="20" rx="4" fill="#059669" />
                                        <text x="50" y="52" fill="#ffffff" font-size="8" font-weight="bold" text-anchor="middle">SITRANS</text>
                                        <rect x="68" y="35" width="8" height="12" />
                                        <rect x="80" y="35" width="12" height="8" />
                                        <rect x="10" y="55" width="15" height="8" />
                                        <rect x="65" y="55" width="25" height="6" />
                                        <rect x="35" y="65" width="12" height="15" />
                                        <rect x="55" y="65" width="10" height="25" />
                                        <rect x="70" y="70" width="20" height="10" />
                                        <rect x="35" y="85" width="15" height="8" />
                                        <rect x="75" y="85" width="18" height="8" />
                                    </svg>
                                </div>
                                <p class="mt-3 font-display text-lg font-black text-slate-900 dark:text-cream-100">
                                    {{ \App\Support\Format::rupiah($placedTotal) }}
                                </p>
                                <div class="mt-2 flex flex-wrap justify-center gap-1.5 text-[10px] font-bold text-slate-500">
                                    <span class="rounded bg-slate-100 px-2 py-0.5">GoPay</span>
                                    <span class="rounded bg-slate-100 px-2 py-0.5">OVO</span>
                                    <span class="rounded bg-slate-100 px-2 py-0.5">Dana</span>
                                    <span class="rounded bg-slate-100 px-2 py-0.5">ShopeePay</span>
                                    <span class="rounded bg-slate-100 px-2 py-0.5">BCA Mobile</span>
                                    <span class="rounded bg-slate-100 px-2 py-0.5">Livin</span>
                                </div>
                            </div>
                            <p class="text-xs text-navy-500 font-semibold dark:text-navy-200">
                                Sistem kasir otomatis mendeteksi pembayaran QRIS Anda secara real-time.
                            </p>
                        </div>
                    @elseif ($placedPaymentMethod === 'cash')
                        <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-5 text-amber-900 dark:border-amber-900/30 dark:bg-amber-950/40 dark:text-amber-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">💵</span>
                                <div>
                                    <h4 class="font-bold text-sm">Pembayaran di Kasir (Tunai / EDC)</h4>
                                    <p class="mt-0.5 text-xs text-amber-800 dark:text-amber-300">
                                        Silakan tunjukkan nomor pesanan <strong class="font-mono font-bold text-amber-950 dark:text-amber-100">#{{ $placedNumber }}</strong> ke kasir untuk menyelesaikan pembayaran. Pesanan Anda akan langsung diproses oleh barista setelah konfirmasi kasir.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-blue-200 bg-blue-50/80 p-5 text-blue-900 dark:border-blue-900/30 dark:bg-blue-950/40 dark:text-blue-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🏦</span>
                                <div>
                                    <h4 class="font-bold text-sm">Transfer Rekening Bank</h4>
                                    <p class="mt-0.5 text-xs text-blue-800 dark:text-blue-300">
                                        Silakan transfer nominal <strong class="font-bold">{{ \App\Support\Format::rupiah($placedTotal) }}</strong> ke rekening toko dan sertakan nomor pesanan <strong class="font-mono">#{{ $placedNumber }}</strong> pada berita transfer.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <!-- Filter & Search Bar -->
        <section class="mb-8 space-y-4">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="Cari kopi, roti, camilan favorit..."
                    class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-900 dark:text-cream-100">
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="$set('categoryId', null)"
                    class="rounded-full px-4 py-2 text-xs font-bold transition shadow-sm {{ $categoryId === null ? 'bg-navy-500 text-white shadow-navy-500/25' : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-100 dark:bg-espresso-900 dark:text-cream-200 dark:border-cream-100/10' }}">
                    Semua Menu
                </button>
                @foreach ($this->categories as $category)
                    <button type="button" wire:click="$set('categoryId', {{ $category->getKey() }})"
                        class="rounded-full px-4 py-2 text-xs font-bold transition shadow-sm {{ $categoryId === $category->getKey() ? 'bg-navy-500 text-white shadow-navy-500/25' : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-100 dark:bg-espresso-900 dark:text-cream-200 dark:border-cream-100/10' }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </section>

        <!-- Product Cards Grid with Real Imagery -->
        <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($this->products as $product)
                <article wire:key="product-{{ $product->getKey() }}" class="group flex flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition hover:-translate-y-1 hover:border-navy-500/40 hover:shadow-lg dark:border-cream-100/10 dark:bg-espresso-900">
                    <!-- Product Image -->
                    <div class="relative h-44 w-full overflow-hidden bg-slate-100 dark:bg-espresso-800">
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" />
                        @if ($product->category)
                            <span class="absolute top-3 left-3 rounded-lg bg-slate-900/70 px-2.5 py-1 text-[10px] font-bold text-white backdrop-blur-md">
                                {{ $product->category->name }}
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="font-display text-base font-bold text-slate-900 dark:text-cream-100">{{ $product->name }}</h3>
                        @if ($product->description)
                            <p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-cream-300">{{ $product->description }}</p>
                        @endif

                        @if ($product->variants->isNotEmpty())
                            <div class="mt-3">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pilih Varian</label>
                                <select wire:model="selectedVariants.{{ $product->getKey() }}"
                                    class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-navy-500 focus:bg-white focus:outline-none dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100">
                                    @foreach ($product->variants as $variant)
                                        <option value="{{ $variant->getKey() }}">{{ $variant->name }} &middot; {{ \App\Support\Format::rupiah($variant->price) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="mt-auto flex items-center justify-between pt-4">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Harga</span>
                                <p class="text-sm font-extrabold tabular text-navy-500 dark:text-navy-200">{{ \App\Support\Format::rupiah($product->price) }}</p>
                            </div>
                            <button type="button" wire:click="addToCart({{ $product->getKey() }})"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-navy-500 to-navy-400 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-navy-500/20 transition hover:from-navy-900 hover:to-navy-500 hover:scale-105">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Tambah</span>
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm dark:border-cream-100/10 dark:bg-espresso-900">
                    <p class="text-sm font-semibold text-slate-500 dark:text-cream-300">Tidak ada menu yang sesuai dengan pencarian Anda.</p>
                </div>
            @endforelse
        </section>

        <!-- Cart & Checkout Area -->
        <section class="mt-12 grid gap-6 lg:grid-cols-12">
            <!-- Cart Items (7 cols) -->
            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-soft dark:border-cream-100/10 dark:bg-espresso-900 lg:col-span-7">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-cream-100/5">
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-cream-100">Keranjang Belanja</h2>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600 dark:bg-espresso-800 dark:text-cream-200">{{ $this->cartCount }} item</span>
                </div>

                @error('cart') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror

                @if ($cart === [])
                    <div class="py-12 text-center text-xs text-slate-400 dark:text-cream-300">
                        <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-espresso-700 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Keranjang masih kosong. Pilih menu di atas untuk mulai memesan.
                    </div>
                @else
                    <ul class="mt-4 divide-y divide-slate-100 dark:divide-cream-100/5">
                        @foreach ($cart as $index => $line)
                            <li wire:key="cart-{{ $index }}" class="flex items-center justify-between gap-3 py-3.5">
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-cream-100">{{ $line['name'] }}</p>
                                    @if ($line['variant_name'])
                                        <p class="text-xs text-slate-500 dark:text-cream-300">{{ $line['variant_name'] }}</p>
                                    @endif
                                    <p class="text-xs tabular text-slate-500 dark:text-cream-300">{{ \App\Support\Format::rupiah($line['unit_price']) }} &times; {{ (int) $line['quantity'] }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="decrement({{ $index }})" class="h-8 w-8 rounded-xl bg-slate-100 text-sm font-bold text-slate-800 transition hover:bg-slate-200 dark:bg-espresso-800 dark:text-cream-100">&minus;</button>
                                    <span class="w-6 text-center text-sm font-bold tabular text-slate-900 dark:text-cream-100">{{ (int) $line['quantity'] }}</span>
                                    <button type="button" wire:click="increment({{ $index }})" class="h-8 w-8 rounded-xl bg-slate-100 text-sm font-bold text-slate-800 transition hover:bg-slate-200 dark:bg-espresso-800 dark:text-cream-100">+</button>
                                    <button type="button" wire:click="removeLine({{ $index }})" class="ml-2 rounded-lg p-1 text-slate-400 hover:text-rose-600 transition" title="Hapus">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-cream-100/5">
                        <span class="text-sm font-bold text-slate-700 dark:text-cream-200">Total Pembayaran</span>
                        <span class="font-display text-xl font-black tabular text-navy-500 dark:text-navy-200">{{ \App\Support\Format::rupiah($this->cartTotal) }}</span>
                    </div>
                @endif
            </div>

            <!-- Customer Details & Place Order Form (5 cols) -->
            <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-soft dark:border-cream-100/10 dark:bg-espresso-900 lg:col-span-5">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-cream-100">Informasi Pemesan</h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">Nama Anda <span class="text-rose-600">*</span></label>
                        <input type="text" wire:model="customerName" required placeholder="Contoh: Rian"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100">
                        @error('customerName') <p class="mt-1 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">Tipe Pesanan <span class="text-rose-600">*</span></label>
                        <select wire:model.live="type" required
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm font-semibold text-slate-800 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100">
                            <option value="" disabled selected>Pilih tipe pesanan…</option>
                            <option value="dine_in">Makan di Tempat (Dine-in)</option>
                            <option value="takeaway">Bawa Pulang (Takeaway)</option>
                            <option value="pickup">Ambil Sendiri (Self-pickup)</option>
                        </select>
                        @error('type') <p class="mt-1 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror
                    </div>

                    @if ($type === 'dine_in')
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">Nomor Meja <span class="text-rose-600">*</span></label>
                            <input type="text" wire:model="tableNumber" required placeholder="Contoh: 05"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100">
                            @error('tableNumber') <p class="mt-1 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">Metode Pembayaran</label>
                        <div class="mt-2 grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                            @foreach ($this->paymentMethods as $method)
                                <label wire:key="pm-card-{{ $method->id }}"
                                    class="relative flex cursor-pointer flex-col rounded-2xl border p-3 transition {{ $paymentMethod === $method->code ? 'border-navy-500 bg-navy-50/50 shadow-sm ring-2 ring-navy-500/25 dark:border-navy-200 dark:bg-navy-900/30' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 dark:border-cream-100/10 dark:bg-espresso-800' }}">
                                    <div class="flex items-center justify-between">
                                        <input type="radio" wire:model.live="paymentMethod" value="{{ $method->code }}" class="h-4 w-4 text-navy-500 focus:ring-navy-500/40">
                                        @if ($method->code === 'qris')
                                            <span class="rounded bg-rose-600 px-1.5 py-0.5 text-[9px] font-black text-white">QRIS</span>
                                        @elseif ($method->code === 'cash')
                                            <span class="text-base">💵</span>
                                        @else
                                            <span class="text-base">🏦</span>
                                        @endif
                                    </div>
                                    <span class="mt-2 text-xs font-bold text-slate-900 dark:text-cream-100">{{ $method->name }}</span>
                                    <span class="text-[10px] text-slate-500 dark:text-cream-300">
                                        @if ($method->code === 'qris')
                                            GoPay, OVO, Dana, BCA
                                        @elseif ($method->code === 'cash')
                                            Bayar tunai di kasir
                                        @else
                                            Transfer antar bank
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('paymentMethod') <p class="mt-1 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">Catatan Khusus <span class="font-normal lowercase text-slate-400">(opsional)</span></label>
                        <textarea wire:model="notes" rows="2" placeholder="Contoh: Kopi sedikit gula, pisang goreng extra keju"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100"></textarea>
                    </div>

                    <button type="button" wire:click="placeOrder" wire:loading.attr="disabled"
                        class="mt-4 w-full rounded-xl bg-gradient-to-r from-navy-500 to-navy-400 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-navy-500/30 transition hover:from-navy-900 hover:to-navy-500 disabled:opacity-50">
                        <span wire:loading.remove class="inline-flex items-center justify-center gap-2">
                            <span>Kirim Pesanan Sekarang ({{ \App\Support\Format::rupiah($this->cartTotal) }})</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </span>
                        <span wire:loading>Memproses Pesanan…</span>
                    </button>
                </div>
            </div>
        </section>
    </div>
</div>
