<x-filament-panels::page>
    @php($tenant = $this->tenant)

    @if ($tenant === null)
        <x-filament::section>
            <p class="text-sm text-slate-500">Tidak ada kafe aktif pada sesi ini.</p>
        </x-filament::section>
    @else
        <div class="relative overflow-hidden rounded-3xl border border-navy-500/20 bg-gradient-to-r from-navy-500/10 via-orange-500/10 to-navy-500/10 p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="rounded-full bg-navy-500/10 px-3 py-1 text-xs font-bold text-navy-900">Akun Kafe Terdaftar</span>
                    <h2 class="mt-2 font-display text-2xl font-black text-slate-900">{{ $tenant->name }}</h2>
                    <p class="mt-1 text-xs text-slate-500">Paket Langganan Kafe & Multi-Outlet Sitrans</p>
                </div>
                <x-filament::badge :color="$this->statusColor()" size="lg">
                    {{ $this->statusLabel() }}
                </x-filament::badge>
            </div>

            <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Berakhir Pada</dt>
                    <dd class="mt-1 text-lg font-extrabold text-slate-900">
                        {{ $tenant->trial_ends_at?->translatedFormat('d F Y') ?? '—' }}
                    </dd>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sisa Masa Aktif</dt>
                    <dd class="mt-1 text-lg font-extrabold text-navy-500">
                        {{ $this->daysLeft() !== null ? $this->daysLeft().' hari' : '—' }}
                    </dd>
                </div>
            </dl>
        </div>

        @if (! $tenant->isActive())
            <div class="rounded-3xl border border-rose-300 bg-rose-50/80 p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-600 text-white shadow-sm">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="font-display font-bold text-slate-900">Masa Langganan Telah Berakhir</p>
                        <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                            Akses ke menu dan kasir dinonaktifkan sementara. Hubungi tim Sitrans untuk memperpanjang langganan kafe Anda melalui WhatsApp atau email ke
                            <span class="font-bold text-navy-500">halo@sitrans.id</span>.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <x-filament::section>
            <x-slot name="heading">Kunci Akses API (Storefront & Integrasi)</x-slot>
            <x-slot name="description">
                Gunakan kunci ini untuk menghubungkan aplikasi pemesanan atau integrasi pihak ketiga via header <code>X-Api-Key</code>.
            </x-slot>

            <div class="flex flex-wrap items-center gap-3">
                <code class="break-all rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-xs font-bold text-navy-900">
                    {{ $tenant->api_key ?? 'Belum tersedia' }}
                </code>
                <x-filament::button
                    color="gray"
                    size="sm"
                    wire:click="regenerateApiKey"
                    wire:confirm="Buat ulang kunci API? Kunci lama akan berhenti bekerja."
                >
                    Buat Ulang Kunci
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
