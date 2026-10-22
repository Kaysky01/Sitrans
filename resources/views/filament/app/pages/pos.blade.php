<x-filament-panels::page>
    <div class="flex flex-col items-center gap-4 py-20 text-center">
        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Membuka layar kasir&hellip;</p>
        <a href="{{ route('pos') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-navy-500 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-navy-500/30 transition hover:bg-navy-500">
            <x-filament::icon icon="heroicon-o-shopping-cart" class="h-4 w-4" />
            Buka Kasir (POS)
        </a>
    </div>
</x-filament-panels::page>
