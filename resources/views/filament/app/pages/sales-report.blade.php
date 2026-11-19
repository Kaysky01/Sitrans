<x-filament-panels::page>
    @php($summary = $this->summary)

    <x-filament::section>
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:gap-4">
            <label class="flex w-full flex-col gap-1 text-xs font-bold uppercase tracking-wider text-slate-700 sm:w-auto">
                <span>Dari</span>
                <input type="date" wire:model.live="from"
                    class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20 sm:w-auto">
            </label>
            <label class="flex w-full flex-col gap-1 text-xs font-bold uppercase tracking-wider text-slate-700 sm:w-auto">
                <span>Sampai</span>
                <input type="date" wire:model.live="to"
                    class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20 sm:w-auto">
            </label>
            @if ($rangeError)
                <p class="w-full text-xs font-semibold text-amber-600" x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show">{{ $rangeError }}</p>
            @endif
            @if ($this->outlets->count() > 1)
                <label class="flex w-full flex-col gap-1 text-xs font-bold uppercase tracking-wider text-slate-700 sm:w-auto">
                    <span>Outlet</span>
                    <select wire:model.live="outletId"
                        class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20 sm:w-auto">
                        <option value="">Semua Outlet</option>
                        @foreach ($this->outlets as $outlet)
                            <option value="{{ $outlet->getKey() }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <div class="grid grid-cols-3 gap-2 sm:flex sm:flex-wrap">
                <button type="button" wire:click="setRangeToday" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-navy-500">Hari ini</button>
                <button type="button" wire:click="setRangeLastSevenDays" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-navy-500">7 Hari</button>
                <button type="button" wire:click="setRangeThisMonth" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:border-navy-500">Bulan ini</button>
            </div>
        </div>
    </x-filament::section>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="relative overflow-hidden rounded-2xl border border-navy-500/20 bg-gradient-to-br from-navy-500/10 to-orange-500/5 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-navy-900">Total Penjualan</p>
            <p class="mt-2 break-words font-display text-xl font-black text-slate-900 sm:text-2xl">{{ \App\Support\Format::rupiah($summary['revenue']) }}</p>
            <p class="mt-1 text-xs font-semibold text-navy-500">{{ $summary['orders'] }} transaksi sukses</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Rata-rata / Tiket</p>
            <p class="mt-2 break-words font-display text-xl font-black text-slate-900 sm:text-2xl">{{ \App\Support\Format::rupiah($summary['avg_ticket']) }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $summary['items_sold'] }} item terjual</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl border border-orange-500/20 bg-gradient-to-br from-orange-500/10 to-orange-500/5 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-orange-800">Laba Kotor</p>
            <p class="mt-2 break-words font-display text-xl font-black text-slate-900 sm:text-2xl">{{ \App\Support\Format::rupiah($summary['gross_margin']) }}</p>
            <p class="mt-1 text-xs font-semibold text-orange-700">HPP {{ \App\Support\Format::rupiah($summary['cogs']) }}</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl border border-amber-500/20 bg-gradient-to-br from-amber-500/10 to-orange-500/5 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Pajak PB1 & Service</p>
            <p class="mt-2 break-words font-display text-xl font-black text-amber-700 sm:text-2xl">{{ \App\Support\Format::rupiah($summary['tax'] + $summary['service']) }}</p>
            <p class="mt-1 text-xs font-semibold text-amber-600">Titipan daerah</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Subtotal Menu</p>
            <p class="mt-1 text-base font-extrabold text-slate-900">{{ \App\Support\Format::rupiah($summary['subtotal']) }}</p>
            <p class="mt-0.5 text-[11px] text-slate-500">Sebelum diskon, pajak, dan service.</p>
        </div>
        @foreach ([
            ['Diskon Diberikan', \App\Support\Format::rupiah($summary['discounts']), 'Potongan yang diberikan ke pelanggan.'],
            ['Penjualan Bersih', \App\Support\Format::rupiah($summary['revenue']), 'Subtotal setelah diskon (pendapatan kafe).'],
            ['Pajak (PB1/PPN)', \App\Support\Format::rupiah($summary['tax']), 'Pajak daerah yang dipungut dari pelanggan.'],
            ['Service Charge', \App\Support\Format::rupiah($summary['service']), 'Biaya layanan, dicatat terpisah sebagai titipan.'],
            ['Total Dibayar', \App\Support\Format::rupiah($summary['gross']), 'Jumlah yang dibayar pelanggan.'],
        ] as [$label, $value, $hint])
            <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                <p class="mt-1 text-base font-extrabold text-slate-900">{{ $value }}</p>
                <p class="mt-0.5 text-[11px] text-slate-500">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section heading="Menu Terlaris (Top Products)">
            <div class="overflow-x-auto">
            <table class="min-w-[32rem] w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                        <th class="pb-3">Menu</th>
                        <th class="pb-3 text-right">Terjual</th>
                        <th class="pb-3 text-right">Omzet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($this->topProducts as $row)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 font-semibold text-slate-900">{{ $row->name }}</td>
                            <td class="py-2.5 text-right tabular font-bold text-slate-700">{{ (float) $row->qty }}x</td>
                            <td class="py-2.5 text-right tabular font-extrabold text-navy-500">{{ \App\Support\Format::rupiah($row->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-xs text-slate-400">Belum ada penjualan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </x-filament::section>

        <x-filament::section heading="Pecahan Metode Pembayaran">
            <div class="overflow-x-auto">
            <table class="min-w-[32rem] w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                        <th class="pb-3">Metode Bayar</th>
                        <th class="pb-3 text-right">Transaksi</th>
                        <th class="pb-3 text-right">Total Diterima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($this->payments as $row)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 font-semibold text-slate-900">
                                <span class="rounded-lg bg-navy-50 px-2 py-0.5 text-xs font-bold text-navy-900">
                                    {{ $row->method_name }}
                                </span>
                            </td>
                            <td class="py-2.5 text-right tabular font-bold text-slate-700">{{ (int) $row->payments }}x</td>
                            <td class="py-2.5 text-right tabular font-extrabold text-slate-900">{{ \App\Support\Format::rupiah($row->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-xs text-slate-400">Belum ada pembayaran pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </x-filament::section>
    </div>

    <x-filament::section heading="Rincian Penjualan Harian">
        <div class="max-h-96 overflow-x-auto overflow-y-auto">
            <table class="min-w-[28rem] w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                        <th class="pb-3">Hari & Tanggal</th>
                        <th class="pb-3 text-right">Total Omzet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($this->series as $day => $revenue)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 font-medium text-slate-800">{{ \Carbon\CarbonImmutable::parse($day)->translatedFormat('l, d F Y') }}</td>
                            <td class="py-2.5 text-right tabular font-extrabold text-navy-500">{{ \App\Support\Format::rupiah($revenue) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
