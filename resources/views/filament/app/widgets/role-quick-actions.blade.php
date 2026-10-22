<x-filament-widgets::widget>
    @php($posUrl = route('pos'))
    @php($ordersUrl = \App\Filament\App\Resources\Orders\OrderResource::getUrl('index'))
    @php($stockUrl = \App\Filament\App\Resources\StockItems\StockItemResource::getUrl('index'))
    @php($menuUrl = \App\Filament\App\Resources\Products\ProductResource::getUrl('index'))

    <div class="grid gap-4 xl:grid-cols-12">
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-navy-950 via-navy-700 to-navy-500 p-5 text-white shadow-xl shadow-navy-950/15 sm:p-6 xl:col-span-7">
            <div class="pointer-events-none absolute -right-12 -top-16 h-56 w-56 rounded-full bg-orange-400/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>

            <div class="relative flex h-full flex-col justify-between gap-7">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wide ring-1 ring-white/15">{{ $roleLabel }} &middot; {{ $tenant?->name }}</span>
                    </div>
                    <h2 class="mt-4 font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
                        @if ($role === 'cashier') Fokus pada antrean dan pembayaran.
                        @elseif ($role === 'kitchen') Pesanan yang perlu disiapkan.
                        @elseif ($role === 'manager') Operasional kafe dalam kendali.
                        @else Gambaran usaha Anda hari ini.
                        @endif
                    </h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-navy-100">
                        @if ($role === 'cashier') Mulai transaksi baru atau lihat pesanan yang masih berjalan.
                        @elseif ($role === 'kitchen') Buka antrean untuk melihat detail pesanan masuk dan menjaga pelayanan tetap cepat.
                        @elseif ($role === 'manager') Pantau antrean, stok, dan penjualan—lalu ambil tindakan dari satu layar.
                        @else Lihat sinyal operasional utama, performa penjualan, serta hal yang perlu ditindaklanjuti.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap gap-2.5">
                    @if ($canUsePos)
                        <a href="{{ $posUrl }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-orange-950/20 transition hover:-translate-y-0.5 hover:bg-orange-400">
                            <span>▣</span> Buka Kasir
                        </a>
                    @endif
                    @if ($canViewOrders)
                        <a href="{{ $ordersUrl }}" class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-xs font-bold text-white backdrop-blur transition hover:bg-white/20">
                            Lihat Riwayat &amp; Antrean <span aria-hidden="true">→</span>
                        </a>
                    @endif
                    @if ($canViewReports)
                        <a href="{{ \App\Filament\App\Pages\SalesReport::getUrl() }}" class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-xs font-bold text-white backdrop-blur transition hover:bg-white/20">
                            Laporan Hari Ini
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-3 xl:col-span-5 xl:grid-cols-1">
            @if ($canViewOrders)
                <a href="{{ $ordersUrl }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-navy-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Perlu diproses</span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-orange-100 text-orange-700">◷</span>
                    </div>
                    <p class="mt-2 font-display text-3xl font-extrabold text-slate-900">{{ $openOrders }}</p>
                    <p class="mt-1 text-xs font-medium text-slate-500">pesanan masih terbuka</p>
                </a>
            @endif
            <a href="{{ $canViewOrders ? $ordersUrl : $posUrl }}" @if ($canUsePos) target="_blank" @endif class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-navy-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Aktivitas hari ini</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-navy-100 text-navy-700">↗</span>
                </div>
                <p class="mt-2 font-display text-3xl font-extrabold text-slate-900">{{ $todayOrders }}</p>
                <p class="mt-1 text-xs font-medium text-slate-500">transaksi dibuat hari ini</p>
            </a>
            @if ($canManageInventory)
                <a href="{{ $stockUrl }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-navy-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Perhatian stok</span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $lowStock > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $lowStock > 0 ? '!' : '✓' }}</span>
                    </div>
                    <p class="mt-2 font-display text-3xl font-extrabold text-slate-900">{{ $lowStock }}</p>
                    <p class="mt-1 text-xs font-medium text-slate-500">{{ $lowStock > 0 ? 'item perlu dicek' : 'stok dalam kondisi aman' }}</p>
                </a>
            @endif
        </section>
    </div>

    @if ($canManageSettings || $canManageInventory || $canViewReports)
        <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-extrabold text-slate-900">Ruang kerja cepat</p>
                    <p class="mt-0.5 text-xs text-slate-500">Akses tugas rutin tanpa mencari-cari di navigasi.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($canManageInventory)
                        <a href="{{ $stockUrl }}" class="rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-bold text-slate-700 transition hover:bg-navy-50 hover:text-navy-700">Kelola Stok</a>
                    @endif
                    @if ($canManageSettings)
                        <a href="{{ $menuUrl }}" class="rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-bold text-slate-700 transition hover:bg-navy-50 hover:text-navy-700">Atur Menu</a>
                        @if ($tenant?->slug)
                            <a href="{{ route('storefront', $tenant) }}" target="_blank" class="rounded-xl bg-navy-50 px-3.5 py-2 text-xs font-bold text-navy-700 transition hover:bg-navy-100">Lihat Menu Digital</a>
                        @endif
                    @endif
                </div>
            </div>
        </section>
    @endif
</x-filament-widgets::widget>
