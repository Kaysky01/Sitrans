<div class="min-h-screen bg-gradient-to-br from-orange-50/40 via-navy-50/30 to-slate-50 dark:from-espresso-950 dark:via-espresso-900 dark:to-espresso-950">
    <div class="mx-auto grid min-h-screen max-w-6xl items-center gap-10 px-4 py-8 lg:grid-cols-12 sm:px-6">
        <!-- Left Brand & Imagery Showcase Panel (5 cols) -->
        <aside class="relative hidden h-full min-h-[640px] flex-col justify-between overflow-hidden rounded-[32px] bg-gradient-to-br from-navy-900 to-navy-500 p-10 text-white shadow-xl lg:col-span-5 lg:flex">
            <!-- Background Image with Overlay -->
            <img src="{{ asset('images/pos-counter.jpg') }}" alt="Cafe ambiance" class="absolute inset-0 h-full w-full object-cover mix-blend-overlay opacity-30" />
            <div class="pointer-events-none absolute -top-24 -left-24 h-64 w-64 rounded-full bg-orange-400/25 blur-3xl"></div>

            <div class="relative z-10">
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-2.5">
                    <img src="{{ asset('images/sitrans-logo.png') }}" alt="Sitrans" class="h-11 w-11 rounded-xl bg-white object-contain p-0.5 shadow-md" />
                    <span class="font-display text-2xl font-black tracking-tight text-white">Sitrans</span>
                </a>

                <div class="mt-12">
                    <span class="rounded-full bg-navy-500/20 px-3.5 py-1 text-xs font-bold text-navy-200 backdrop-blur-sm">
                        Uji Coba 14 Hari Bebas Biaya
                    </span>
                    <h2 class="mt-4 font-display text-3xl font-black leading-tight text-white">
                        Wujudkan operasional kafe yang rapi, cepat, & modern.
                    </h2>
                    <p class="mt-4 text-sm leading-relaxed text-navy-100">
                        Dari pesanan meja, transaksi kasir instan, hingga pembukuan stok & pajak PB1 tanpa ribet.
                    </p>
                </div>
            </div>

            <div class="relative z-10 space-y-4">
                <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/avatar-1.jpg') }}" alt="Sarah" class="h-10 w-10 rounded-full object-cover" />
                        <div>
                            <p class="text-xs font-bold text-white">Sarah Amanda</p>
                            <p class="text-[11px] text-navy-200">Owner, Bloom Bakery & Coffee</p>
                        </div>
                    </div>
                    <p class="mt-2.5 text-xs text-navy-100">
                        "Setup-nya gak sampai 5 menit, langsung bisa dipakai kasir buat cetak struk dan terima QRIS!"
                    </p>
                </div>

                <ul class="space-y-2 text-xs font-semibold text-navy-200">
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-orange-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        Kasir ramah sentuh untuk tablet, iPad, & PC
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-orange-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        Perhitungan pajak restoran PB1 otomatis
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-orange-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        Dukungan struk Bluetooth 58mm &amp; 80mm
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Right Form Panel (7 cols) -->
        <main class="flex items-center justify-center lg:col-span-7">
            <div class="w-full max-w-lg rounded-3xl border border-slate-200/80 bg-white/95 p-6 shadow-xl backdrop-blur-md sm:p-10 dark:border-cream-100/10 dark:bg-espresso-900/95">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-navy-50 px-3 py-0.5 text-xs font-bold text-navy-500 dark:bg-navy-900 dark:text-navy-200">
                            <span>✨ Registrasi Cepat</span>
                        </div>
                        <h1 class="mt-2 font-display text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-cream-100">
                            Daftarkan Kafe Anda
                        </h1>
                        <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-cream-300">
                            Sudah memiliki akun Sitrans?
                            <a href="/app/login" class="font-bold text-navy-500 hover:text-navy-500 hover:underline dark:text-navy-200">Masuk di sini &rarr;</a>
                        </p>
                    </div>

                    <a href="{{ route('landing') }}" class="lg:hidden text-xs font-semibold text-slate-500 hover:text-slate-800">
                        &larr; Beranda
                    </a>
                </div>

                <form wire:submit="register" class="mt-6 space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">Nama Usaha / Kafe</label>
                        <input type="text" wire:model="cafe_name" placeholder="Contoh: Kopi Titik Cerita"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100 dark:placeholder:text-espresso-600">
                        @error('cafe_name') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">Nama Outlet Pertama <span class="font-normal lowercase text-slate-400">(opsional)</span></label>
                        <input type="text" wire:model="outlet_name" placeholder="Contoh: Cabang Kemang"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100 dark:placeholder:text-espresso-600">
                        @error('outlet_name') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">Nama Pemilik</label>
                            <input type="text" wire:model="name" placeholder="Budi Santoso"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100 dark:placeholder:text-espresso-600">
                            @error('name') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">No. WhatsApp <span class="font-normal lowercase text-slate-400">(opsional)</span></label>
                            <input type="tel" wire:model="phone" placeholder="08123456789"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100 dark:placeholder:text-espresso-600">
                            @error('phone') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">Email Bisnis</label>
                        <input type="email" wire:model="email" placeholder="owner@kafe.id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100 dark:placeholder:text-espresso-600">
                        @error('email') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">Kata Sandi</label>
                            <input type="password" wire:model="password" placeholder="Minimal 8 karakter"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100">
                            @error('password') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-cream-200">Ulangi Sandi</label>
                            <input type="password" wire:model="password_confirmation" placeholder="Ulangi kata sandi"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100">
                        </div>
                    </div>

                    <label class="flex items-start gap-2.5 pt-1 text-xs text-slate-600 dark:text-cream-300">
                        <input type="checkbox" wire:model="agree" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-navy-500 focus:ring-navy-500/40">
                        <span>Saya menyetujui syarat layanan dan kebijakan privasi penggunaan Sitrans POS.</span>
                    </label>
                    @error('agree') <p class="text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror

                    <button type="submit" wire:loading.attr="disabled"
                        class="mt-2 w-full rounded-xl bg-gradient-to-r from-navy-500 to-navy-400 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-navy-500/30 transition hover:from-navy-900 hover:to-navy-500 disabled:opacity-60">
                        <span wire:loading.remove class="inline-flex items-center gap-2">
                            <span>Mulai Uji Coba Gratis 14 Hari</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </span>
                        <span wire:loading>Menyiapkan Akun Kafe Anda…</span>
                    </button>
                </form>
            </div>
        </main>
    </div>
</div>
