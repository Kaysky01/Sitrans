<x-layouts.base title="Sesi Berakhir — Sitrans">
    <div class="flex min-h-screen flex-col items-center justify-center gap-6 bg-gradient-to-b from-terracotta-50/40 via-navy-50/30 to-slate-50 px-4 text-center dark:from-espresso-950 dark:via-espresso-900 dark:to-espresso-950">
        <img src="{{ asset('images/sitrans-logo.png') }}" alt="Sitrans" class="h-20 w-20 object-contain" />
        <p class="font-display text-7xl font-black text-slate-300 dark:text-cream-100/20">419</p>
        <div class="max-w-md">
            <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-cream-100">Sesi Anda berakhir</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-cream-300">Halaman telah terlalu lama tidak aktif. Muat ulang untuk melanjutkan dengan aman.</p>
        </div>
        <a href="{{ url()->current() }}" class="rounded-2xl bg-gradient-to-r from-navy-500 to-navy-400 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-navy-500/30 transition hover:from-navy-900 hover:to-navy-500">
            Muat Ulang Halaman
        </a>
    </div>
</x-layouts.base>
