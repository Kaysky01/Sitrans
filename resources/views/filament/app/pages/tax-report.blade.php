<x-filament-panels::page>
    @php($summary = $this->summary)

    <div class="rounded-2xl border border-navy-500/15 bg-navy-50/60 p-4 text-xs text-navy-900">
        <p class="font-bold">Informasi Pelaporan Pajak Daerah (PB1 / PBJT):</p>
        <p class="mt-0.5 text-slate-600">Halaman ini mengkhususkan rekap pungutan Pajak Restoran (PB1 10%) dan Service Charge untuk keperluan pelaporan pajak daerah atau rekonsiliasi kas. Pungutan pajak ini juga sudah otomatis terhitung di dalam <a href="/app/sales-report" class="font-bold underline text-navy-500 hover:text-navy-700">Laporan Penjualan</a>.</p>
    </div>

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

    <!-- Tax Metrics -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="relative overflow-hidden rounded-2xl border border-navy-500/20 bg-gradient-to-br from-navy-500/10 to-orange-500/5 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-navy-900">PB1 Terkumpul (10%)</p>
            <p class="mt-2 break-words font-display text-xl font-black text-navy-500 sm:text-2xl">{{ \App\Support\Format::rupiah($summary['tax']) }}</p>
            <p class="mt-1 text-xs font-semibold text-navy-500">Kewajiban setor daerah</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Service Charge</p>
            <p class="mt-2 break-words font-display text-xl font-black text-slate-900 sm:text-2xl">{{ \App\Support\Format::rupiah($summary['service']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Titipan untuk tim kafe</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl border border-amber-500/20 bg-gradient-to-br from-amber-500/10 to-orange-500/5 p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Total Titipan Non-Omzet</p>
            <p class="mt-2 break-words font-display text-xl font-black text-amber-700 sm:text-2xl">{{ \App\Support\Format::rupiah($summary['tax'] + $summary['service']) }}</p>
            <p class="mt-1 text-xs text-amber-600">PB1 + Service Charge</p>
        </div>
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Bruto Penjualan</p>
            <p class="mt-2 break-words font-display text-xl font-black text-slate-900 sm:text-2xl">{{ \App\Support\Format::rupiah($summary['gross']) }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $summary['orders'] }} transaksi</p>
        </div>
    </div>

    <x-filament::section heading="Rincian Pajak Harian">
        <div class="max-h-96 overflow-x-auto overflow-y-auto">
            <table class="min-w-[42rem] w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                        <th class="pb-3">Hari & Tanggal</th>
                        <th class="pb-3 text-right">Transaksi</th>
                        <th class="pb-3 text-right">Pajak PB1</th>
                        <th class="pb-3 text-right">Service</th>
                        <th class="pb-3 text-right">Total Bruto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($this->series as $day => $row)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 font-medium text-slate-800">{{ \Carbon\CarbonImmutable::parse($day)->translatedFormat('l, d F Y') }}</td>
                            <td class="py-2.5 text-right tabular font-bold text-slate-600">{{ $row['orders'] }}x</td>
                            <td class="py-2.5 text-right tabular font-extrabold text-navy-500">{{ \App\Support\Format::rupiah($row['tax']) }}</td>
                            <td class="py-2.5 text-right tabular font-bold text-slate-700">{{ \App\Support\Format::rupiah($row['service']) }}</td>
                            <td class="py-2.5 text-right tabular font-extrabold text-slate-900">{{ \App\Support\Format::rupiah($row['gross']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
