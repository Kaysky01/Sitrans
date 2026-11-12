@php
    $positions = $this->queuePositions;
    $total = $this->totalActive();
    $filters = [
        'all' => ['label' => 'Semua', 'count' => $total],
        'pending' => ['label' => 'Baru', 'count' => $this->countByStatus(\App\Modules\Pos\Enums\OrderFulfillmentStatus::Pending)],
        'accepted' => ['label' => 'Diterima', 'count' => $this->countByStatus(\App\Modules\Pos\Enums\OrderFulfillmentStatus::Accepted)],
        'processing' => ['label' => 'Diproses', 'count' => $this->countByStatus(\App\Modules\Pos\Enums\OrderFulfillmentStatus::Processing)],
    ];
@endphp

<div class="space-y-4">
    @if ($toastMessage)
        <div x-data="{ show: true }" x-init="setTimeout(() => { show = false; $wire.clearToast(); }, 4000)" x-show="show"
            x-transition
            @class([
                'fixed right-4 top-20 z-[70] flex max-w-xs items-start gap-2 rounded-2xl px-4 py-3 text-xs font-bold shadow-xl ring-1',
                'bg-emerald-50 text-emerald-800 ring-emerald-500/30' => $toastType === 'success',
                'bg-rose-50 text-rose-800 ring-rose-500/30' => $toastType !== 'success',
            ])>
            <span>{{ $toastType === 'success' ? '✓' : '!' }}</span>
            <span class="flex-1">{{ $toastMessage }}</span>
            <button type="button" wire:click="clearToast" class="opacity-60 hover:opacity-100" aria-label="Tutup notifikasi">✕</button>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Papan kerja</p>
                <h2 class="mt-0.5 font-display text-lg font-black text-slate-900">Pesanan yang perlu ditangani</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Alur: <strong>Terima</strong> → <strong>Proses</strong> → <strong>Selesai</strong>.
                    Gunakan tombol pada tiap pesanan. Pesanan yang sudah selesai otomatis keluar dari antrean.
                </p>
            </div>
            <div class="flex items-center gap-2 rounded-xl bg-navy-50 px-3.5 py-2.5 text-navy-900 ring-1 ring-navy-500/15">
                <span class="text-2xl font-black tabular">{{ $total }}</span>
                <span class="text-[11px] font-bold leading-tight">pesanan<br>aktif</span>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($filters as $key => $filter)
                <button type="button" wire:click="setFilter('{{ $key }}')"
                    @class([
                        'inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-bold transition',
                        'bg-navy-500 text-white shadow-sm' => $this->filter === $key,
                        'bg-slate-100 text-slate-600 hover:bg-slate-200' => $this->filter !== $key,
                    ])>
                    {{ $filter['label'] }}
                    <span @class([
                        'rounded-full px-1.5 py-0.5 text-[10px] font-black',
                        'bg-white/20 text-white' => $this->filter === $key,
                        'bg-white text-slate-500' => $this->filter !== $key,
                    ])>{{ $filter['count'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    @forelse ($this->orders as $order)
        @php
            $status = $order->fulfillment_status ?? \App\Modules\Pos\Enums\OrderFulfillmentStatus::Pending;
            $position = $positions[$order->getKey()] ?? null;
            $ahead = $position !== null ? max(0, $position - 1) : null;
        @endphp
        <article wire:key="incoming-{{ $order->id }}"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div class="flex flex-wrap items-center gap-2">
                    <span @class([
                        'rounded-full px-2.5 py-1 text-[11px] font-black ring-1',
                        'bg-amber-50 text-amber-800 ring-amber-500/30' => $status === \App\Modules\Pos\Enums\OrderFulfillmentStatus::Pending,
                        'bg-sky-50 text-sky-800 ring-sky-500/30' => $status === \App\Modules\Pos\Enums\OrderFulfillmentStatus::Accepted,
                        'bg-navy-50 text-navy-800 ring-navy-500/20' => $status === \App\Modules\Pos\Enums\OrderFulfillmentStatus::Processing,
                        'bg-emerald-50 text-emerald-800 ring-emerald-500/30' => $status === \App\Modules\Pos\Enums\OrderFulfillmentStatus::Completed,
                    ])>{{ $status->label() }}</span>

                    @if ($position !== null)
                        <span class="rounded-full bg-navy-500 px-2.5 py-1 text-[11px] font-black text-white">
                            Antrian {{ $position }} dari {{ $total }}
                        </span>
                        <span class="text-[11px] font-semibold text-slate-500">
                            {{ $ahead > 0 ? $ahead.' pesanan di depan' : 'Paling depan' }}
                        </span>
                    @endif
                </div>
                <span class="text-[11px] font-semibold text-slate-500">
                    {{ $order->ordered_at?->translatedFormat('d M Y, H:i') ?? $order->created_at?->translatedFormat('d M Y, H:i') }}
                </span>
            </div>

            <div class="grid gap-4 px-4 py-4 sm:px-5 lg:grid-cols-[1.4fr_1fr_auto] lg:items-center">
                <div>
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                        <h3 class="font-display text-lg font-black text-slate-900">#{{ $order->number }}</h3>
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                            {{ $order->type?->label() ?? 'Tipe belum dipilih' }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs font-semibold text-slate-600">
                        {{ $order->customer_name ?: 'Pelanggan langsung' }}
                        @if ($order->table_number)
                            &middot; Meja {{ $order->table_number }}
                        @endif
                        &middot; {{ $order->cashier?->name ?? 'Kasir' }}
                    </p>
                    @if ($order->type)
                        <p class="mt-1 text-[11px] text-slate-400">{{ $order->type->description() }}</p>
                    @endif
                    @if ($order->notes)
                        <p class="mt-1 rounded-lg bg-amber-50 px-2.5 py-1.5 text-[11px] font-medium text-amber-900">
                            Catatan: {{ $order->notes }}
                        </p>
                    @endif
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Item pesanan</p>
                    <ul class="mt-1 space-y-0.5 text-xs text-slate-700">
                        @foreach ($order->items->take(3) as $item)
                            <li class="flex justify-between gap-3">
                                <span class="truncate">{{ (int) $item->quantity }}&times; {{ $item->name }}</span>
                                <span class="shrink-0 tabular font-semibold">{{ \App\Support\Format::rupiah($item->line_total) }}</span>
                            </li>
                        @endforeach
                        @if ($order->items->count() > 3)
                            <li class="text-[11px] font-semibold text-slate-400">+{{ $order->items->count() - 3 }} item lainnya</li>
                        @endif
                    </ul>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center lg:flex-col lg:items-stretch">
                    <div class="text-left sm:text-right lg:text-left">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total</p>
                        <p class="font-display text-xl font-black tabular text-navy-600">{{ \App\Support\Format::rupiah($order->grand_total) }}</p>
                        <p class="text-[10px] font-bold {{ $order->isPaid() ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $order->isPaid() ? 'Sudah dibayar' : 'Belum dibayar' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if ($status === \App\Modules\Pos\Enums\OrderFulfillmentStatus::Pending)
                            <button type="button" wire:click="accept({{ $order->id }})" wire:loading.attr="disabled"
                                class="rounded-xl bg-navy-500 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-navy-700 disabled:opacity-50">
                                Terima Pesanan
                            </button>
                        @endif
                        @if (in_array($status, [\App\Modules\Pos\Enums\OrderFulfillmentStatus::Pending, \App\Modules\Pos\Enums\OrderFulfillmentStatus::Accepted], true))
                            <button type="button" wire:click="process({{ $order->id }})" wire:loading.attr="disabled"
                                class="rounded-xl bg-sky-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-sky-700 disabled:opacity-50">
                                Proses
                            </button>
                        @endif
                        @if ($status === \App\Modules\Pos\Enums\OrderFulfillmentStatus::Processing)
                            <button type="button" wire:click="complete({{ $order->id }})" wire:loading.attr="disabled"
                                class="rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-50">
                                Selesai
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </article>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl">✓</div>
            <p class="mt-3 font-display text-base font-black text-slate-800">Tidak ada pesanan di antrean</p>
            <p class="mt-1 text-xs text-slate-500">
                Pesanan baru dari kasir atau storefront akan muncul otomatis di sini.
                @if ($filter !== 'all')
                    Coba pilih filter <strong>Semua</strong> untuk melihat seluruh antrean.
                @endif
            </p>
        </div>
    @endforelse
</div>
