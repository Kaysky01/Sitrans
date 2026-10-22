<x-layouts.base title="Halaman Tidak Ditemukan — Sitrans">
    <div class="flex min-h-screen flex-col items-center justify-center gap-6 bg-gradient-to-b from-orange-50/40 via-navy-50/30 to-slate-50 px-4 text-center dark:from-espresso-950 dark:via-espresso-900 dark:to-espresso-950">
        <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-navy-100 text-navy-500 shadow-md">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <p class="font-display text-7xl font-black text-slate-300 dark:text-cream-100/20">404</p>
        <div class="max-w-md">
            <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-cream-100">Halaman tidak ditemukan</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-cream-300">Halaman yang Anda tuju tidak ditemukan atau tautan sudah berpindah.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ url('/') }}" class="rounded-2xl bg-gradient-to-r from-navy-500 to-navy-400 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-navy-500/30 transition hover:from-navy-900 hover:to-navy-500">
                Kembali ke Beranda
            </a>
            <a href="{{ url('/app') }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100">
                Ke Dashboard
            </a>
        </div>
    </div>
</x-layouts.base>
