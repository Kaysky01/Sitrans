<div class="min-h-screen bg-slate-100 px-4 py-10 antialiased dark:bg-slate-950">
    <style>
        @media print {
            body * { visibility: hidden; }
            #struk, #struk * { visibility: visible; }
            #struk { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; border: none !important; }
            .no-print { display: none !important; }
        }
    </style>

    <div class="mx-auto max-w-sm">
        <!-- Physical Receipt Card Preview -->
        <div id="struk" class="rounded-3xl border border-slate-200/80 bg-white p-6 font-mono text-sm text-slate-800 shadow-xl dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100">
            <!-- Receipt Header -->
            <div class="text-center">
                <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white font-black text-sm">
                    ☕
                </div>
                <p class="font-display text-base font-black uppercase tracking-wider text-slate-900 dark:text-white">
                    {{ $receipt?->header ?? $order->outlet?->name }}
                </p>
                @if ($order->outlet?->address)
                    <p class="mt-1 text-[11px] text-slate-500">{{ $order->outlet->address }}</p>
                @endif
                @if ($order->outlet?->phone)
                    <p class="text-[11px] text-slate-500">Telp: {{ $order->outlet->phone }}</p>
                @endif
            </div>

            <div class="my-3 border-t border-dashed border-slate-300 dark:border-slate-700"></div>

            <!-- Receipt Meta -->
            <div class="flex justify-between text-xs text-slate-600 dark:text-slate-400">
                <span>No: <strong class="text-slate-900 dark:text-white">#{{ $order->number }}</strong></span>
                <span>{{ $order->created_at?->format('d/m/Y H:i') }}</span>
            </div>
            <div class="mt-1 flex justify-between text-xs text-slate-600 dark:text-slate-400">
                <span>Tipe: <strong class="text-slate-900 dark:text-white">{{ $order->type->shortLabel() }}</strong></span>
                <span>Kasir: {{ $order->cashier?->name ?? 'Kasir' }}</span>
            </div>
            @if ($order->customer_name)
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                    Pelanggan: <strong class="text-slate-900 dark:text-white">{{ $order->customer_name }}</strong>@if ($order->type === \App\Modules\Pos\Enums\OrderType::DineIn && $order->table_number)
                        &middot; Meja: <strong class="text-slate-900 dark:text-white">{{ $order->table_number }}</strong>
                    @endif
                </p>
            @endif

            <div class="my-3 border-t border-dashed border-slate-300 dark:border-slate-700"></div>

            <!-- Order Lines -->
            <ul class="space-y-2.5">
                @foreach ($order->items as $item)
                    <li>
                        <div class="flex justify-between font-bold text-slate-800 dark:text-slate-100">
                            <span>{{ $item->name }}{{ $item->variant_name ? ' ('.$item->variant_name.')' : '' }}</span>
                            <span class="tabular">{{ \App\Support\Format::rupiah($item->line_total) }}</span>
                        </div>
                        @foreach ($item->modifiers as $modifier)
                            <p class="pl-2 text-[11px] text-navy-500 dark:text-navy-200">+ {{ $modifier->name }}</p>
                        @endforeach
                        <p class="text-[11px] text-slate-400">
                            {{ (int) $item->quantity }} &times; {{ \App\Support\Format::rupiah($item->unit_price) }}
                        </p>
                    </li>
                @endforeach
            </ul>

            <div class="my-3 border-t border-dashed border-slate-300 dark:border-slate-700"></div>

            <!-- Totals & Payment Summary -->
            <div class="space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Subtotal</span>
                    <span class="tabular font-medium">{{ \App\Support\Format::rupiah($order->subtotal) }}</span>
                </div>
                @if ($order->discount_total > 0)
                    <div class="flex justify-between text-rose-600 font-semibold">
                        <span>Diskon</span>
                        <span class="tabular">− {{ \App\Support\Format::rupiah($order->discount_total) }}</span>
                    </div>
                @endif
                @if ($receipt?->show_tax ?? true)
                    @if ($order->service_amount > 0)
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Service</span>
                            <span class="tabular">{{ \App\Support\Format::rupiah($order->service_amount) }}</span>
                        </div>
                    @endif
                    @if ($order->tax_amount > 0)
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>PB1</span>
                            <span class="tabular">{{ \App\Support\Format::rupiah($order->tax_amount) }}</span>
                        </div>
                    @endif
                @endif
                <div class="flex justify-between border-t border-slate-900/10 pt-2 text-base font-black text-slate-900 dark:border-slate-700 dark:text-white">
                    <span>TOTAL</span>
                    <span class="tabular text-navy-500 dark:text-navy-200">{{ \App\Support\Format::rupiah($order->grand_total) }}</span>
                </div>
                @foreach ($order->payments as $payment)
                    <div class="flex justify-between text-slate-700 dark:text-slate-300 pt-1">
                        <span>Bayar ({{ $payment->method_name }})</span>
                        <span class="tabular font-semibold">{{ \App\Support\Format::rupiah($payment->amount) }}</span>
                    </div>
                @endforeach
                @if ($order->change_total > 0)
                    <div class="flex justify-between text-navy-500 dark:text-navy-200 font-bold">
                        <span>Kembali</span>
                        <span class="tabular">{{ \App\Support\Format::rupiah($order->change_total) }}</span>
                    </div>
                @endif
            </div>

            <div class="my-4 border-t border-dashed border-slate-300 dark:border-slate-700"></div>

            <!-- Footer Message -->
            <p class="text-center text-xs font-semibold text-slate-500 dark:text-slate-400">
                {{ $receipt?->footer ?? 'Terima kasih atas kunjungan Anda!' }}
            </p>
        </div>

        <!-- Receipt Actions (Print, Email, New Order) -->
        <div class="no-print mt-6 space-y-3.5">
            @if ($sentTo)
                <div class="rounded-2xl border border-navy-500/30 bg-navy-50 px-4 py-3 text-xs font-bold text-navy-900 dark:border-navy-500/40 dark:bg-navy-900 dark:text-navy-200">
                    ✓ Struk digital berhasil dikirim ke {{ $sentTo }}.
                </div>
            @endif

            <form wire:submit="sendEmail" class="flex gap-2">
                <input type="email" wire:model="email" placeholder="Kirim ke email pelanggan..."
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-navy-500 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100">
                <button type="submit"
                    class="shrink-0 rounded-2xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700">
                    Kirim Email
                </button>
            </form>
            @error('email') <p class="text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror

            <div class="flex gap-3 pt-1">
                <button type="button" onclick="window.print()"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-navy-500 to-navy-400 px-5 py-3 text-xs font-black text-white shadow-lg shadow-navy-500/25 transition hover:from-navy-900 hover:to-navy-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Struk (Print)</span>
                </button>
                <button type="button" wire:click="newOrder"
                    class="flex-1 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
                    + Transaksi Baru
                </button>
            </div>
        </div>
    </div>
</div>
