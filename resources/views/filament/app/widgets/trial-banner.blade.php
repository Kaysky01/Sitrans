<x-filament-widgets::widget>
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-gradient-to-r from-orange-500/15 via-navy-500/10 to-orange-500/10 p-4 border border-navy-500/20 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-navy-500 to-navy-400 text-white shadow-sm">
                <x-filament::icon icon="heroicon-o-sparkles" class="h-5 w-5" />
            </div>
            <div>
                <p class="text-sm font-bold text-slate-900">
                    Masa Uji Coba Aktif &middot; Sisa <span class="text-orange-600 font-extrabold">{{ $daysLeft }} Hari Lagi</span>
                </p>
                @if ($endsAt)
                    <p class="text-xs text-slate-500">Berlaku sampai {{ $endsAt->translatedFormat('d F Y') }}</p>
                @endif
            </div>
        </div>
        <a href="{{ \App\Filament\App\Pages\Subscription::getUrl(panel: 'app') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-navy-600 shadow-sm border border-slate-200 transition hover:bg-slate-50 hover:border-navy-500">
            <span>Kelola Langganan</span>
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>
</x-filament-widgets::widget>
