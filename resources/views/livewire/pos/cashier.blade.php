<div class="flex h-dvh flex-col overflow-hidden bg-cream-100 font-sans text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
    <!-- Top Navigation -->
    <header class="flex h-14 shrink-0 items-center justify-between px-4 sm:px-6">
        <a href="/app" class="flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-slate-900 shadow-sm ring-1 ring-slate-900/10 dark:bg-slate-900 dark:text-slate-100">
                <x-filament::icon icon="heroicon-o-building-storefront" class="h-5 w-5 text-navy-500" />
            </span>
            <span class="font-serif text-xl font-black tracking-tight text-navy-900 dark:text-white">Sitrans</span>
        </a>

        <nav class="hidden items-center gap-1 md:flex">
            <a href="/app"
                class="rounded-full px-4 py-2 text-sm font-semibold text-slate-500 transition hover:bg-white hover:text-navy-900 dark:text-slate-300 dark:hover:bg-slate-800">
                Overview
            </a>
            <span class="rounded-full bg-navy-500 px-4 py-2 text-sm font-bold text-white shadow-sm shadow-navy-600/20">
                POS Terminal
            </span>
            <a href="/app/orders"
                class="rounded-full px-4 py-2 text-sm font-semibold text-slate-500 transition hover:bg-white hover:text-navy-900 dark:text-slate-300 dark:hover:bg-slate-800">
                Orders
            </a>
        </nav>

        <a href="/app" title="Ke Back Office"
            class="flex h-9 w-9 items-center justify-center rounded-full bg-navy-500 text-white shadow-sm transition hover:bg-navy-700">
            <x-filament::icon icon="heroicon-o-user" class="h-5 w-5" />
        </a>
    </header>

    <!-- Toolbar -->
    <div class="flex h-16 shrink-0 items-center gap-2 bg-white px-3.5 sm:gap-3.5 sm:px-5 dark:border-slate-800 dark:bg-slate-900">
        <div class="max-[440px]:hidden">
            <livewire:outlet-switcher :always-show="true" />
        </div>

        <div class="relative min-w-0 flex-1 max-w-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-5 w-5" />
            </div>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari menu & produk..."
                class="w-full rounded-full border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-4 text-base font-medium text-slate-800 transition placeholder:text-slate-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 sm:text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
        </div>

        <div class="ml-auto flex shrink-0 items-center gap-2 sm:gap-3">
            @if ($this->shift)
                @php($shiftHour = (int) ($this->shift->opened_at?->format('G') ?? 8))
                @php($shiftPeriod = $shiftHour < 11 ? 'Pagi' : ($shiftHour < 15 ? 'Siang' : ($shiftHour < 19 ? 'Sore' : 'Malam')))
                <button type="button" wire:click="$set('showCloseShiftModal', true)"
                    class="inline-flex items-center gap-2 rounded-full bg-orange-100 px-3.5 py-2 text-xs font-bold text-orange-900 ring-1 ring-orange-500/30 transition hover:bg-orange-200 dark:bg-orange-950/50 dark:text-orange-200">
                    <span class="h-2 w-2 rounded-full bg-orange-600 animate-pulse dark:bg-orange-400"></span>
                    <span class="hidden sm:inline">Shift {{ $shiftPeriod }} Aktif</span>
                    <span class="sm:hidden">Shift</span>
                </button>
            @else
                <button type="button" wire:click="$set('showShiftModal', true)"
                    class="inline-flex items-center gap-1.5 rounded-full bg-orange-500 px-3.5 py-2 text-xs font-bold text-white shadow-sm shadow-orange-500/30 transition hover:bg-orange-600">
                    <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4" />
                    <span class="max-[440px]:hidden">Buka Shift</span>
                </button>
            @endif

            <!-- Cart summary chip: opens the mobile cart drawer -->
            <label for="cart-drawer-toggle"
                class="relative flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-white text-slate-700 shadow-sm ring-1 ring-slate-900/10 transition hover:bg-slate-50 lg:pointer-events-none dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700">
                <x-filament::icon icon="heroicon-o-shopping-cart" class="h-5 w-5" />
                @php($cartCount = $this->order?->items?->count() ?? 0)
                @if ($cartCount > 0)
                    <span class="absolute -right-0.5 -top-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-danger-600 text-[10px] font-black text-white ring-2 ring-white dark:ring-slate-900">
                        {{ $cartCount }}
                    </span>
                @endif
            </label>

            <!-- Signed-in user -->
            <div class="hidden items-center gap-2.5 lg:flex">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-navy-500 text-xs font-black text-white shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </span>
                <span class="leading-tight">
                    <span class="block text-sm font-bold text-slate-900 dark:text-slate-100">{{ auth()->user()->name }}</span>
                    <span class="block text-[11px] font-semibold text-slate-400">{{ $this->roleLabel }}</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Main POS Interface: katalog penuh; keranjang jadi bottom-sheet di layar < xl -->
    <div class="flex min-h-0 flex-1 overflow-hidden">
        <input type="checkbox" id="cart-drawer-toggle" class="peer sr-only xl:hidden" aria-label="Buka atau tutup keranjang">

        <!-- Category rail (desktop) -->
        <nav class="hidden w-24 shrink-0 flex-col gap-2.5 overflow-y-auto p-3 scrollbar-none xl:flex" aria-label="Kategori menu">
            <button type="button" wire:click="selectCategory(null)"
                @class([
                    'flex flex-col items-center gap-1.5 rounded-2xl px-2 py-3.5 text-[11px] font-bold transition',
                    'bg-navy-500 text-white shadow-md shadow-navy-600/20' => $categoryId === null,
                    'bg-white text-slate-700 shadow-sm ring-1 ring-slate-900/5 hover:bg-slate-50' => $categoryId !== null,
                ])>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z" />
                </svg>
                <span>Semua</span>
            </button>
            @foreach ($this->categories as $category)
                <button type="button" wire:key="rail-{{ $category->id }}" wire:click="selectCategory({{ $category->id }})"
                    @class([
                        'flex flex-col items-center gap-1.5 rounded-2xl px-2 py-3.5 text-[11px] font-bold transition',
                        'bg-navy-500 text-white shadow-md shadow-navy-600/20' => $categoryId === $category->id,
                        'bg-white text-slate-700 shadow-sm ring-1 ring-slate-900/5 hover:bg-slate-50' => $categoryId !== $category->id,
                    ])>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $this->categoryIcon($category->name) }}" />
                    </svg>
                    <span class="line-clamp-2 text-center leading-tight">{{ $category->name }}</span>
                </button>
            @endforeach
        </nav>

        <!-- CENTER: Products & Menu Browsing -->
        <section class="flex min-w-0 flex-1 flex-col overflow-y-auto p-4 sm:p-6">
            <!-- Categories chips (mobile/tablet) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none xl:hidden">
                <button type="button" wire:click="selectCategory(null)"
                    @class([
                        'rounded-full px-4 py-2 text-xs font-bold transition shrink-0',
                        'bg-navy-500 text-white shadow-sm' => $categoryId === null,
                        'bg-white text-slate-700 ring-1 ring-slate-900/10 hover:bg-slate-50 dark:text-slate-200' => $categoryId !== null,
                    ])>
                    Semua
                </button>
                @foreach ($this->categories as $category)
                    <button type="button" wire:key="cat-{{ $category->id }}" wire:click="selectCategory({{ $category->id }})"
                        @class([
                            'rounded-full px-4 py-2 text-xs font-bold transition shrink-0',
                            'bg-navy-500 text-white shadow-sm' => $categoryId === $category->id,
                            'bg-white text-slate-700 ring-1 ring-slate-900/10 hover:bg-slate-50 dark:text-slate-200' => $categoryId !== $category->id,
                        ])>
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>

            <!-- Bundles / Special Promos -->
            @if ($this->bundles->isNotEmpty())
                <div class="mt-3 flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                    <span class="shrink-0 text-[10px] font-black uppercase tracking-wider text-orange-600 dark:text-orange-400">Promo Paket:</span>
                    @foreach ($this->bundles as $bundle)
                        <button type="button" wire:key="bundle-{{ $bundle->id }}" wire:click="addBundle({{ $bundle->id }})"
                            class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-3.5 py-1.5 text-xs font-bold text-orange-900 ring-1 ring-orange-500/30 transition hover:bg-orange-200 dark:bg-orange-950/40 dark:text-orange-200">
                            <span>🎁 {{ $bundle->name }}</span>
                            <span class="tabular font-black text-orange-700 dark:text-orange-300">&middot; {{ \App\Support\Format::rupiah($bundle->price) }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            <!-- Product Grid -->
            <div class="mt-4 grid grid-cols-2 gap-3 pb-24 sm:gap-4 xl:grid-cols-3 xl:pb-6 2xl:grid-cols-4">
                @forelse ($this->products as $product)
                    <button type="button" wire:key="product-{{ $product->id }}" wire:click="chooseProduct({{ $product->id }})"
                        @disabled($product->is_sold_out)
                        @class([
                            'group flex flex-col overflow-hidden rounded-2xl bg-white p-3 text-left shadow-sm ring-1 ring-slate-900/5 transition duration-150 hover:shadow-md dark:bg-slate-900 dark:ring-slate-800',
                            'hover:-translate-y-0.5' => ! $product->is_sold_out,
                        ])>
                        <div class="relative mb-3 aspect-[4/3] w-full overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                                @class([
                                    'h-full w-full object-cover transition',
                                    'blur-[2px] scale-105 opacity-80' => $product->is_sold_out,
                                ]) loading="lazy" />
                            @if ($product->category)
                                <span class="absolute right-2.5 top-2.5 rounded-full bg-white/95 px-2.5 py-1 text-[10px] font-bold text-slate-700 shadow-sm backdrop-blur dark:bg-slate-900/90 dark:text-slate-200">
                                    {{ $product->category->name }}
                                </span>
                            @endif
                            @if ($product->is_sold_out)
                                <span class="absolute inset-0 flex items-center justify-center">
                                    <span class="rounded-lg bg-danger-600 px-4 py-1.5 text-xs font-black tracking-wider text-white shadow-lg">HABIS</span>
                                </span>
                            @endif
                        </div>
                        <span class="font-serif text-base font-bold leading-snug text-slate-900 line-clamp-2 dark:text-slate-100 {{ $product->is_sold_out ? 'text-slate-400 dark:text-slate-500' : '' }}">{{ $product->name }}</span>
                        @if ($product->description)
                            <span class="mt-0.5 text-xs text-slate-400 line-clamp-1">{{ $product->description }}</span>
                        @endif
                        <div class="mt-auto flex items-end justify-between pt-3">
                            <span @class([
                                'font-extrabold tabular',
                                $product->is_sold_out ? 'text-sm text-slate-300' : 'text-sm text-navy-500 dark:text-navy-200',
                            ])>{{ \App\Support\Format::rupiah($product->price) }}</span>
                            @if (! $product->is_sold_out)
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-navy-500 text-white shadow-sm shadow-navy-600/20 transition group-hover:bg-navy-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" d="M12 5v14m8-8H4" />
                                    </svg>
                                </span>
                            @else
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" d="M12 5v14m8-8H4" />
                                    </svg>
                                </span>
                            @endif
                        </div>
                    </button>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-20 text-center">
                        <x-filament::icon icon="heroicon-o-inbox" class="mb-3 h-12 w-12 text-slate-300 dark:text-slate-700" />
                        <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">Tidak ada menu yang sesuai pencarian.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Floating cart bar (mobile/tablet < xl): opens the cart bottom-sheet -->
        <label for="cart-drawer-toggle"
            class="fixed inset-x-3 bottom-3 z-30 flex cursor-pointer items-center justify-between gap-3 rounded-2xl bg-navy-900 px-4 py-3 text-xs font-bold text-white shadow-2xl shadow-navy-900/40 ring-1 ring-white/10 backdrop-blur transition hover:bg-navy-700 peer-checked:pointer-events-none peer-checked:opacity-0 xl:hidden">
            <span class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-navy-200/20 text-base">🛒</span>
                <span>
                    <span class="block">{{ $this->order?->items?->count() ?? 0 }} item</span>
                    <span class="block tabular font-extrabold text-navy-200">{{ \App\Support\Format::rupiah($this->order?->grand_total ?? 0) }}</span>
                </span>
            </span>
            <span class="flex items-center gap-1.5 rounded-full bg-white px-3.5 py-2 text-[11px] font-extrabold text-navy-900">
                Lihat Keranjang ⌃
            </span>
        </label>

        <!-- RIGHT: Order Summary & Cart Panel — bottom-sheet di bawah xl, panel tetap di xl+ -->
        <aside class="fixed inset-x-0 bottom-0 top-[7.5rem] z-40 flex w-full max-w-sm translate-y-full flex-col rounded-t-3xl border-t border-slate-200 bg-white shadow-2xl shadow-slate-900/25 transition-transform duration-300 ease-out peer-checked:translate-y-0 sm:inset-x-2 sm:rounded-3xl sm:max-w-md dark:border-slate-800 dark:bg-slate-900 xl:static xl:translate-y-0 xl:rounded-none xl:border-l xl:border-t-0 xl:shadow-soft xl:transition-none dark:xl:border-t-0">
            <!-- Order Header — detail pesanan wajib: tipe, nama pelanggan, meja (Dine In), catatan -->
            <div class="shrink-0 border-b border-slate-100 px-4 pb-3.5 pt-4 dark:border-slate-800 sm:px-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="font-serif text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ $this->order?->number ? '#'.$this->order->number : 'Pesanan Baru' }}
                    </h2>
                    <div class="flex items-center gap-2">
                        <label for="cart-drawer-toggle"
                            class="cursor-pointer rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 xl:hidden dark:hover:bg-slate-800"
                            aria-label="Tutup keranjang">✕</label>
                    </div>
                </div>

                <div class="mt-2.5 space-y-2">
                    @if ($errors->any())
                        <div class="rounded-xl bg-danger-50 px-3 py-2 dark:bg-navy-900">
                            @foreach ($errors->all() as $error)
                                <p class="text-[11px] font-semibold text-danger-700 dark:text-danger-300">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tipe Pesanan <span class="text-danger-600">*</span></p>
                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach (\App\Modules\Pos\Enums\OrderType::cases() as $type)
                            <button type="button" wire:click="$set('orderType', '{{ $type->value }}')"
                                @class([
                                    'rounded-full px-3.5 py-1.5 text-xs font-bold transition',
                                    'bg-navy-500 text-white shadow-sm shadow-navy-600/20' => $orderType === $type->value,
                                    'bg-slate-100 text-slate-500 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300' => $orderType !== $type->value,
                                ])>
                                {{ $type->shortLabel() }}
                            </button>
                        @endforeach
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                        <label class="block">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nama Pelanggan <span class="text-danger-600">*</span></span>
                            <input type="text" wire:model.live.debounce.400ms="customerName" placeholder="Nama pembeli"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        </label>
                        <label class="block" x-cloak x-show="$wire.orderType === 'dine_in'">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nomor Meja <span class="text-danger-600">*</span></span>
                            <input type="text" wire:model.live.debounce.400ms="tableNumber" placeholder="Meja 01"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Catatan <span class="text-slate-400 normal-case">(opsional)</span></span>
                        <input type="text" wire:model.live.debounce.400ms="orderNotes" placeholder="Contoh: less sugar, tanpa terasi"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-navy-500 focus:bg-white focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    </label>
                </div>
            </div>

            <!-- Items List -->
            <div class="min-h-0 flex-1 overflow-y-auto px-4 py-2 sm:px-5">
                @if ($this->order === null || $this->order->items->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400 dark:bg-slate-800">
                            🛒
                        </div>
                        <p class="mt-3 text-xs font-bold text-slate-600 dark:text-slate-300">Keranjang kasir masih kosong</p>
                        <p class="mt-1 text-[11px] text-slate-400">Pilih tipe pesanan &amp; isi nama pelanggan di atas, lalu sentuh menu — keranjang terisi otomatis.</p>
                    </div>
                @else
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($this->order->items as $item)
                            <li wire:key="item-{{ $item->id }}" class="py-3.5">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $item->name }}</p>
                                    <span class="shrink-0 tabular text-sm font-bold text-slate-900 dark:text-slate-100">
                                        {{ \App\Support\Format::rupiah($item->line_total) }}
                                    </span>
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-1">
                                    @if ($item->bundle_id)
                                        <span class="rounded-md bg-orange-100 px-2 py-0.5 text-[10px] font-bold text-orange-800">Paket</span>
                                    @endif
                                    @if ($item->variant_name)
                                        <span class="rounded-md bg-navy-100 px-2 py-0.5 text-[10px] font-semibold text-navy-900 dark:bg-navy-900 dark:text-navy-200">{{ $item->variant_name }}</span>
                                    @endif
                                    @foreach ($item->modifiers as $modifier)
                                        <span class="rounded-md bg-navy-100 px-2 py-0.5 text-[10px] font-semibold text-navy-900 dark:bg-navy-900 dark:text-navy-200">+ {{ $modifier->name }}</span>
                                    @endforeach
                                </div>
                                <div class="mt-2.5 flex items-center justify-between">
                                    <div class="flex items-center justify-between gap-1 rounded-full border border-slate-200 bg-white px-1.5 py-1 shadow-xs dark:border-slate-700 dark:bg-slate-800">
                                        <button type="button" wire:click="decrementItem({{ $item->id }})"
                                            class="flex h-6 w-6 items-center justify-center rounded-full text-sm font-bold text-slate-500 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">&minus;</button>
                                        <span class="tabular min-w-6 text-center text-xs font-bold text-slate-900 dark:text-slate-100">{{ (int) $item->quantity }}</span>
                                        <button type="button" wire:click="incrementItem({{ $item->id }})"
                                            class="flex h-6 w-6 items-center justify-center rounded-full text-sm font-bold text-slate-500 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">+</button>
                                    </div>
                                    <button type="button" wire:click="removeItem({{ $item->id }})"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-danger-600 transition hover:text-danger-700 dark:text-danger-300">
                                        <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                                        Hapus
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Bill Calculations & Payment Trigger -->
            <div class="shrink-0 border-t border-slate-100 bg-slate-50/60 px-4 py-4 dark:border-slate-800 dark:bg-slate-950/40 sm:px-5">
                <div class="flex justify-between py-0.5 text-xs text-slate-600 dark:text-slate-400">
                    <span>Subtotal</span>
                    <span class="tabular font-medium">{{ \App\Support\Format::rupiah($this->order?->subtotal ?? 0) }}</span>
                </div>
                @if (($this->order?->discount_total ?? 0) > 0)
                    <div class="flex justify-between py-0.5 text-xs font-semibold text-danger-600 dark:text-danger-300">
                        <span>Diskon</span>
                        <span class="tabular">− {{ \App\Support\Format::rupiah($this->order->discount_total) }}</span>
                    </div>
                @endif
                @if (($this->order?->service_amount ?? 0) > 0)
                    <div class="flex justify-between py-0.5 text-xs text-slate-600 dark:text-slate-400">
                        <span>Service Charge</span>
                        <span class="tabular font-medium">{{ \App\Support\Format::rupiah($this->order->service_amount) }}</span>
                    </div>
                @endif
                @if (($this->order?->tax_amount ?? 0) > 0)
                    <div class="flex justify-between py-0.5 text-xs text-slate-600 dark:text-slate-400">
                        <span>Pajak (PB1)</span>
                        <span class="tabular font-medium">{{ \App\Support\Format::rupiah($this->order->tax_amount) }}</span>
                    </div>
                @endif

                <div class="mt-1.5 flex items-baseline justify-between border-t border-slate-200/80 pt-2.5 dark:border-slate-800">
                    <span class="font-serif text-xl font-bold text-slate-900 dark:text-slate-200">Total</span>
                    <span class="font-serif text-2xl font-black tabular text-navy-500 dark:text-navy-200">
                        {{ \App\Support\Format::rupiah($this->order?->grand_total ?? 0) }}
                    </span>
                </div>

                <!-- Footer Actions -->
                <div class="mt-3 grid grid-cols-2 gap-2.5">
                    <button type="button" wire:click="openDiscountModal" @disabled($this->order === null)
                        class="rounded-full bg-slate-200/70 px-4 py-3 text-xs font-bold text-slate-700 transition hover:bg-slate-300/70 disabled:opacity-40 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        % Diskon
                    </button>
                    <button type="button" wire:click="newOrder"
                        class="rounded-full bg-slate-200/70 px-4 py-3 text-xs font-bold text-slate-700 transition hover:bg-slate-300/70 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        + Baru
                    </button>
                </div>
                <button type="button" wire:click="openPaymentModal" @disabled($this->order === null || $this->order->items->isEmpty())
                    class="mt-2.5 flex w-full items-center justify-center gap-2 rounded-full bg-orange-500 py-3.5 text-sm font-bold text-white shadow-lg shadow-orange-500/25 transition hover:bg-orange-600 disabled:opacity-40">
                    <x-filament::icon icon="heroicon-o-credit-card" class="h-5 w-5" />
                    <span>Bayar {{ \App\Support\Format::rupiah($this->order?->grand_total ?? 0) }}</span>
                </button>
            </div>
        </aside>
    </div>

    <!-- Footer -->
    <footer class="hidden shrink-0 py-2.5 text-center text-[11px] font-medium text-slate-400 lg:block dark:text-slate-600">
        &copy; {{ date('Y') }} Sitrans POS &amp; Back-Office SaaS. All rights reserved.
    </footer>

    <!-- MODAL: Product Variants & Modifiers -->
    @if ($showProductModal && $this->modalProduct)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-0 backdrop-blur-xs sm:items-center sm:p-4">
            <div class="max-h-[92dvh] w-full max-w-lg overflow-y-auto rounded-t-3xl bg-white p-5 shadow-2xl sm:rounded-3xl sm:p-6 dark:bg-slate-900">
                <div class="flex items-start justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="font-serif text-lg font-bold text-slate-900 dark:text-white">{{ $this->modalProduct->name }}</h3>
                        <p class="text-xs text-slate-500">Pilih varian dan opsi tambahan</p>
                    </div>
                    <button type="button" wire:click="$set('showProductModal', false)" class="rounded-lg p-1 text-slate-400 hover:text-slate-700 dark:hover:text-white">✕</button>
                </div>

                @error('modalVariantId') <p class="mt-2 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror
                @error('modalModifiers') <p class="mt-2 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror

                @if ($this->modalProduct->variants->isNotEmpty())
                    <div class="mt-4">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Pilihan Varian</label>
                        <div class="mt-2 space-y-2">
                            @foreach ($this->modalProduct->variants as $variant)
                                <label class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-xs font-semibold transition hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800/60">
                                    <span class="flex items-center gap-2.5">
                                        <input type="radio" wire:model="modalVariantId" value="{{ $variant->id }}" class="h-4 w-4 text-navy-500 focus:ring-navy-500/40">
                                        <span class="text-slate-800 dark:text-slate-200">{{ $variant->name }}</span>
                                    </span>
                                    <span class="tabular font-bold text-navy-500 dark:text-navy-200">{{ \App\Support\Format::rupiah($variant->price) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                @foreach ($this->modalProduct->modifierGroups as $group)
                    <div class="mt-4" wire:key="group-{{ $group->id }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $group->name }}</span>
                            @if ($group->is_required)
                                <span class="rounded bg-danger-50 px-1.5 py-0.5 text-[10px] font-bold text-danger-700">Wajib</span>
                            @endif
                        </div>
                        <div class="mt-2 space-y-2">
                            @foreach ($group->modifiers as $modifier)
                                <label class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-xs font-medium transition hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800/60">
                                    <span class="flex items-center gap-2.5">
                                        <input type="{{ $group->max_select === 1 ? 'radio' : 'checkbox' }}"
                                            wire:model="modalModifiers.{{ $group->id }}"
                                            name="group-{{ $group->id }}"
                                            value="{{ $modifier->id }}"
                                            class="rounded text-navy-500 focus:ring-navy-500/40">
                                        <span class="text-slate-800 dark:text-slate-200">{{ $modifier->name }}</span>
                                    </span>
                                    @if ($modifier->price > 0)
                                        <span class="tabular text-xs font-bold text-slate-600 dark:text-slate-400">+ {{ \App\Support\Format::rupiah($modifier->price) }}</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="mt-6 flex gap-3">
                    <button type="button" wire:click="$set('showProductModal', false)"
                        class="flex-1 rounded-full border border-slate-200 bg-slate-50 py-2.5 text-xs font-bold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        Batal
                    </button>
                    <button type="button" wire:click="confirmProduct"
                        class="flex-1 rounded-full bg-navy-500 py-2.5 text-xs font-bold text-white shadow-md shadow-navy-600/25 transition hover:bg-navy-900">
                        Tambahkan ke Pesanan
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: Discount -->
    @if ($showDiscountModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-0 backdrop-blur-xs sm:items-center sm:p-4">
            <div class="max-h-[92dvh] w-full max-w-sm overflow-y-auto rounded-t-3xl bg-white p-5 shadow-2xl sm:rounded-3xl sm:p-6 dark:bg-slate-900">
                <h3 class="font-serif text-lg font-bold text-slate-900 dark:text-white">Diskon Pesanan</h3>
                <div class="mt-4 space-y-3.5">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Tipe Diskon</label>
                        <select wire:model="discountType"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold text-slate-800 focus:border-navy-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                            <option value="">Tanpa Diskon</option>
                            <option value="percent">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nilai Diskon</label>
                        <input type="number" step="0.01" wire:model="discountValue"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-900 focus:border-navy-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        @error('discountValue') <p class="mt-1 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror
                    </div>
                    @if ($this->discountRequiresPin)
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">PIN Supervisor</label>
                            <input type="password" wire:model="discountPin" placeholder="Masukkan 4-6 digit PIN"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-900 focus:border-navy-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                            <p class="mt-1 text-[11px] text-orange-600"">Diskon memerlukan otorisasi PIN supervisor.</p>
                            @error('pin') <p class="mt-1 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" wire:click="$set('showDiscountModal', false)"
                        class="flex-1 rounded-full border border-slate-200 bg-slate-50 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        Batal
                    </button>
                    <button type="button" wire:click="applyDiscount"
                        class="flex-1 rounded-full bg-navy-500 py-2.5 text-xs font-bold text-white shadow-md hover:bg-navy-900">
                        Terapkan Diskon
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: Open Shift -->
    @if ($showShiftModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-0 backdrop-blur-xs sm:items-center sm:p-4">
            <div class="max-h-[92dvh] w-full max-w-sm overflow-y-auto rounded-t-3xl bg-white p-5 shadow-2xl sm:rounded-3xl sm:p-6 dark:bg-slate-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100 text-orange-700">
                        <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-serif text-base font-bold text-slate-900 dark:text-white">Buka Shift Kasir</h3>
                        <p class="text-xs text-slate-500">Catat modal awal kasir hari ini</p>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Uang Modal Awal (Cash Drawer)</label>
                    <input type="number" step="0.01" wire:model="openingFloat" placeholder="0"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-lg font-black text-slate-900 focus:border-navy-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('openingFloat') <p class="mt-1 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" wire:click="$set('showShiftModal', false)"
                        class="flex-1 rounded-full border border-slate-200 bg-slate-50 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        Batal
                    </button>
                    <button type="button" wire:click="openShift"
                        class="flex-1 rounded-full bg-navy-500 py-2.5 text-xs font-bold text-white shadow-md hover:bg-navy-900">
                        Buka Shift Sekarang
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: Close Shift -->
    @if ($showCloseShiftModal && $this->shift)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-0 backdrop-blur-xs sm:items-center sm:p-4">
            <div class="max-h-[92dvh] w-full max-w-sm overflow-y-auto rounded-t-3xl bg-white p-5 shadow-2xl sm:rounded-3xl sm:p-6 dark:bg-slate-900">
                <h3 class="font-serif text-base font-bold text-slate-900 dark:text-white">Tutup Shift Kasir</h3>
                <p class="mt-1 text-xs text-slate-500">
                    Modal awal: <strong class="tabular text-slate-800 dark:text-slate-200">{{ \App\Support\Format::rupiah($this->shift->opening_float) }}</strong>
                </p>
                <div class="mt-4 space-y-3.5">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Total Uang Fisik Dihitung</label>
                        <input type="number" step="0.01" wire:model="countedCash"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-lg font-black text-slate-900 focus:border-navy-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        @error('countedCash') <p class="mt-1 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Catatan Shift (Opsional)</label>
                        <textarea wire:model="shiftNotes" rows="2" placeholder="Catatan selisih kas / serah terima shift..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-900 focus:border-navy-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" wire:click="$set('showCloseShiftModal', false)"
                        class="flex-1 rounded-full border border-slate-200 bg-slate-50 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        Batal
                    </button>
                    <button type="button" wire:click="closeShift"
                        class="flex-1 rounded-full bg-danger-600 py-2.5 text-xs font-bold text-white shadow-md hover:bg-danger-700">
                        Tutup Shift &amp; Rekap
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: Payment Process -->
    @if ($showPaymentModal && $this->order)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-0 backdrop-blur-xs sm:items-center sm:p-4">
            <div class="max-h-[92dvh] w-full max-w-lg overflow-y-auto rounded-t-3xl bg-white p-5 shadow-2xl sm:rounded-3xl sm:p-6 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="font-serif text-lg font-black text-slate-900 dark:text-white">Proses Pembayaran</h3>
                        <p class="text-xs text-slate-500">Pesanan #{{ $this->order->number }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Total Tagihan</span>
                        <p class="font-serif text-xl font-black tabular text-navy-500 dark:text-navy-200">
                            {{ \App\Support\Format::rupiah($this->order->grand_total) }}
                        </p>
                    </div>
                </div>

                <!-- Payment Methods Grid -->
                <div class="mt-4">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Pilih Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($this->paymentMethods as $method)
                            <button type="button" wire:key="pm-{{ $method->id }}" wire:click="$set('paymentMethodId', {{ $method->id }})"
                                @class([
                                    'flex flex-col items-center justify-center rounded-2xl p-3 text-xs font-bold transition border',
                                    'border-navy-500 bg-navy-100/60 text-navy-900 ring-2 ring-navy-500/25 dark:border-navy-200 dark:bg-navy-900 dark:text-navy-200' => $paymentMethodId === $method->id,
                                    'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-200' => $paymentMethodId !== $method->id,
                                ])>
                                <span class="text-lg">
                                    @if ($method->code === 'cash') 💵
                                    @elseif ($method->code === 'qris') 📱
                                    @elseif ($method->code === 'transfer') 🏦
                                    @else 💳 @endif
                                </span>
                                <span class="mt-1">{{ $method->name }}</span>
                            </button>
                        @endforeach
                    </div>
                    @error('paymentMethodId') <p class="mt-1 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror
                </div>

                <!-- Quick Cash Amount Presets -->
                <div class="mt-4">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Jumlah Uang Diterima</label>
                        <span class="text-[11px] font-semibold text-slate-400">Preset Uang Cepat</span>
                    </div>

                    <!-- Quick buttons -->
                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                        <button type="button" wire:click="$set('amountPaid', {{ (float) $this->order->grand_total }})"
                            class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-navy-500/40 hover:bg-navy-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            Uang Pas
                        </button>
                        <button type="button" wire:click="$set('amountPaid', 20000)"
                            class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-navy-500/40 hover:bg-navy-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            20.000
                        </button>
                        <button type="button" wire:click="$set('amountPaid', 50000)"
                            class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-navy-500/40 hover:bg-navy-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            50.000
                        </button>
                        <button type="button" wire:click="$set('amountPaid', 100000)"
                            class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-navy-500/40 hover:bg-navy-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            100.000
                        </button>
                        <button type="button" wire:click="$set('amountPaid', 200000)"
                            class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-navy-500/40 hover:bg-navy-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            200.000
                        </button>
                    </div>

                    <input type="number" step="0.01" wire:model.live="amountPaid"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xl font-black text-slate-900 focus:border-navy-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('amountPaid') <p class="mt-1 text-xs font-semibold text-danger-600">{{ $message }}</p> @enderror

                    <!-- Kembalian / Sisa Indicator -->
                    <div class="mt-2.5 flex items-center justify-between rounded-xl p-3 text-xs font-bold {{ $this->totalPaidPreview >= (float) $this->order->grand_total ? 'bg-navy-100 text-navy-900 border border-navy-500/30 dark:bg-navy-900 dark:text-navy-200 dark:border-navy-500/40' : 'bg-danger-50 text-danger-700 border border-danger-500/30 dark:bg-navy-900 dark:text-danger-300 dark:border-danger-500/40' }}">
                        @if ($this->totalPaidPreview >= (float) $this->order->grand_total)
                            <span>Kembalian Kasir:</span>
                            <span class="tabular font-black text-base">{{ \App\Support\Format::rupiah($this->totalPaidPreview - (float) $this->order->grand_total) }}</span>
                        @else
                            <span>Kurang / Sisa:</span>
                            <span class="tabular font-black text-base">{{ \App\Support\Format::rupiah((float) $this->order->grand_total - $this->totalPaidPreview) }}</span>
                        @endif
                    </div>
                </div>

                @if ($this->paymentMethods->firstWhere('id', $paymentMethodId)?->requires_reference)
                    <div class="mt-3.5 flex items-center gap-2 rounded-xl border border-navy-100 bg-navy-50/60 px-3 py-2.5 text-xs text-navy-900 dark:border-navy-800 dark:bg-navy-950/40 dark:text-navy-100">
                        <x-filament::icon icon="heroicon-o-shield-check" class="h-4 w-4 shrink-0 text-navy-500" />
                        <span><strong>Referensi dibuat otomatis</strong> saat pembayaran diselesaikan.</span>
                    </div>
                @endif

                <!-- Split Payment Support -->
                <div class="mt-3.5 border-t border-slate-100 pt-3 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Pembayaran Tambahan (Split Bill)</span>
                        <button type="button" wire:click="addPaymentLine" class="text-xs font-bold text-navy-500 hover:underline dark:text-navy-200">+ Tambah Metode</button>
                    </div>
                    @foreach ($extraPayments as $index => $line)
                        <div wire:key="extra-{{ $index }}" class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                            <select wire:model="extraPayments.{{ $index }}.method_id"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-navy-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 sm:flex-1">
                                @foreach ($this->paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="0.01" wire:model.live="extraPayments.{{ $index }}.amount" placeholder="Nominal"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-800 focus:border-navy-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 sm:w-28 sm:text-xs">
                            <button type="button" wire:click="removePaymentLine({{ $index }})" class="self-start rounded-lg p-1 font-bold text-danger-600 hover:bg-danger-50 sm:self-auto">✕</button>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" wire:click="$set('showPaymentModal', false)"
                        class="flex-1 rounded-full border border-slate-200 bg-slate-50 py-3 text-xs font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        Batal
                    </button>
                    <button type="button" wire:click="pay"
                        class="flex-[2] rounded-full bg-navy-500 py-3 text-sm font-black text-white shadow-lg shadow-navy-600/25 hover:bg-navy-900">
                        Selesaikan &amp; Cetak Struk
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
