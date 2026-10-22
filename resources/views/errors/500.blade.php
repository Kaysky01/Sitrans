<x-layouts.base title="Terjadi Kesalahan — Sitrans">
    <div class="flex min-h-screen flex-col items-center justify-center gap-6 bg-gradient-to-b from-orange-50/40 via-navy-50/30 to-slate-50 px-4 text-center dark:from-espresso-950 dark:via-espresso-900 dark:to-espresso-950">
        <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-rose-100 text-rose-600 shadow-md">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <p class="font-display text-7xl font-black text-slate-300 dark:text-cream-100/20">500</p>
        <div class="max-w-md">
            <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-cream-100">Terjadi Kesalahan Sistem</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-cream-300">Kami sedang menangani kendala teknis ini. Silakan coba kembali dalam beberapa saat.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ url('/') }}" class="rounded-2xl bg-gradient-to-r from-navy-500 to-navy-400 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-navy-500/30 transition hover:from-navy-900 hover:to-navy-500">
                Kembali ke Beranda
            </a>
            <a href="{{ url()->previous() }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100">
                Muat Ulang
            </a>
        </div>
    </div>
</x-layouts.base>
