<x-layouts.base title="Sitrans — Sistem Kasir & Manajemen Kafe Modern">
    <!-- Ambient Bright Glows Background -->
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-b from-orange-50/40 via-navy-50/30 to-slate-50 dark:from-espresso-950 dark:via-espresso-900 dark:to-espresso-950">
        <!-- Decorative Luminous Light Orbs -->
        <div class="pointer-events-none absolute -top-40 left-1/2 -z-10 h-[500px] w-[800px] -translate-x-1/2 rounded-full bg-gradient-to-tr from-navy-400/20 via-navy-200/25 to-orange-300/20 blur-3xl dark:opacity-20"></div>
        <div class="pointer-events-none absolute top-96 -left-32 -z-10 h-96 w-96 rounded-full bg-orange-400/15 blur-3xl dark:opacity-10"></div>
        <div class="pointer-events-none absolute top-[600px] -right-32 -z-10 h-96 w-96 rounded-full bg-amber-400/15 blur-3xl dark:opacity-10"></div>

        <!-- Top Announcement Bar -->
        <div class="bg-gradient-to-r from-navy-500 via-navy-400 to-navy-900 px-4 py-2.5 text-center text-xs font-semibold text-white shadow-sm">
            <div class="mx-auto flex max-w-6xl items-center justify-center gap-2">
                <span class="inline-flex items-center rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase">Terbaru</span>
                <span>Sitrans POS 2.0: Dukung Multi-Outlet, QRIS Instan & Laporan Pajak PB1 Otomatis!</span>
                <a href="{{ route('register') }}" class="underline decoration-white/60 underline-offset-2 transition hover:text-amber-200">
                    Coba Gratis 14 Hari &rarr;
                </a>
            </div>
        </div>

        <!-- Modern Floating Glass Navigation -->
        <header class="sticky top-4 z-50 mx-auto max-w-6xl px-4 sm:px-6">
            <nav class="flex items-center justify-between rounded-2xl border border-white/80 bg-white/85 px-5 py-3.5 shadow-soft backdrop-blur-md dark:border-cream-100/10 dark:bg-espresso-900/85">
                <div class="flex items-center gap-3">
                    <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
                        <img src="{{ asset('images/sitrans-logo.png') }}" alt="Sitrans" class="h-11 w-11 rounded-xl bg-white object-contain p-0.5 shadow-md shadow-navy-500/20 transition group-hover:scale-105" />
                        <div class="flex flex-col">
                            <span class="font-display text-xl font-extrabold tracking-tight text-slate-900 dark:text-cream-100">Sitrans</span>
                            <span class="text-[10px] font-bold tracking-wider uppercase text-navy-500 dark:text-navy-200">Cafe Tech POS</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden items-center gap-6 text-sm font-semibold text-slate-600 md:flex dark:text-cream-200">
                    <a href="#fitur" class="transition hover:text-navy-500 dark:hover:text-orange-300">Fitur</a>
                    <a href="#tampilan-pos" class="transition hover:text-navy-500 dark:hover:text-orange-300">Tampilan POS</a>
                    <a href="#menu-digital" class="transition hover:text-navy-500 dark:hover:text-orange-300">Menu Digital</a>
                    <a href="#testimoni" class="transition hover:text-navy-500 dark:hover:text-orange-300">Testimoni</a>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2.5">
                    <!-- Theme toggle button -->
                    <button
                        type="button"
                        onclick="(function(){const d=document.documentElement.classList.toggle('dark');localStorage.setItem('sitrans-theme',d?'dark':'light');})()"
                        class="rounded-xl border border-slate-200/80 p-2.5 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-cream-100/10 dark:text-cream-200 dark:hover:bg-espresso-800"
                        title="Ganti Mode Terang / Gelap"
                    >
                        <svg class="h-4 w-4 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg class="hidden h-4 w-4 dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    @auth
                        <a href="{{ route('pos') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-navy-500 px-4 py-2 text-xs font-bold text-white shadow-md shadow-navy-500/30 transition hover:bg-navy-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>Buka Kasir</span>
                        </a>
                        <a href="{{ url('/app') }}" class="rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-xs font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-200">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ url('/app/login') }}" class="px-3 py-2 text-xs font-semibold text-slate-700 transition hover:text-navy-500 dark:text-cream-300 dark:hover:text-cream-100">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-navy-500 to-navy-400 px-4 py-2 text-xs font-bold text-white shadow-md shadow-navy-500/30 transition hover:from-navy-900 hover:to-navy-500 hover:shadow-lg hover:shadow-navy-500/40">
                            <span>Daftar Gratis</span>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @endauth
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="mx-auto max-w-6xl px-4 pt-14 pb-20 text-center sm:px-6 sm:pt-20">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2.5 rounded-full border border-navy-500/20 bg-navy-500/10 px-4 py-1.5 text-xs font-bold text-navy-900 shadow-sm backdrop-blur-sm dark:border-navy-500/30 dark:bg-navy-500/20 dark:text-navy-200">
                <span class="flex h-2 w-2 rounded-full bg-navy-500 animate-pulse"></span>
                <span>POS & Manajemen Kafe Generasi Baru Indonesia</span>
                <span class="text-navy-1000">★ 4.9/5</span>
            </div>

            <!-- Main Heading with Bright Contrast -->
            <h1 class="mx-auto mt-6 max-w-4xl font-display text-4xl font-extrabold tracking-tight text-slate-900 sm:text-6xl sm:leading-[1.15] dark:text-cream-100">
                Kelola pesanan, stok bahan, & cabang dalam
                <span class="bg-gradient-to-r from-navy-500 via-navy-400 to-orange-500 bg-clip-text text-transparent">satu sentuhan cepat.</span>
            </h1>

            <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-slate-600 sm:text-lg dark:text-cream-300">
                Sistem kasir cepat ramah layar sentuh, manajemen resep & inventaris otomatis, menu digital mandiri per meja, hingga laporan pajak daerah PB1 siap lapor.
            </p>

            <!-- CTA Buttons -->
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                @auth
                    <a href="{{ route('pos') }}" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-navy-500 to-navy-400 px-8 py-4 text-sm font-bold text-white shadow-lg shadow-navy-500/30 transition hover:from-navy-900 hover:to-navy-500 hover:shadow-xl hover:shadow-navy-500/40">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Buka Aplikasi Kasir (POS)</span>
                    </a>
                    <a href="{{ url('/app') }}" class="rounded-2xl border border-slate-300/80 bg-white px-8 py-4 text-sm font-bold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100">
                        Masuk Dashboard Kafe
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2.5 rounded-2xl bg-gradient-to-r from-navy-500 to-navy-400 px-8 py-4 text-sm font-bold text-white shadow-lg shadow-navy-500/35 transition hover:from-navy-900 hover:to-navy-500 hover:shadow-xl hover:shadow-navy-500/45">
                        <span>Coba Gratis 14 Hari</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <a href="{{ url('/app/login') }}" class="rounded-2xl border border-slate-300/80 bg-white px-8 py-4 text-sm font-bold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-100">
                        Masuk Akun
                    </a>
                @endauth
            </div>

            <!-- Micro Guarantees -->
            <div class="mt-6 flex flex-wrap items-center justify-center gap-6 text-xs font-semibold text-slate-500 dark:text-cream-300">
                <span class="inline-flex items-center gap-1.5 text-navy-500 dark:text-navy-200">
                    <svg class="h-4 w-4 text-navy-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    Setup mudah 2 menit
                </span>
                <span class="inline-flex items-center gap-1.5 text-navy-500 dark:text-navy-200">
                    <svg class="h-4 w-4 text-navy-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    Tanpa perlu kartu kredit
                </span>
                <span class="inline-flex items-center gap-1.5 text-navy-500 dark:text-navy-200">
                    <svg class="h-4 w-4 text-navy-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    Kompatibel Tablet, iPad & PC
                </span>
            </div>

            <!-- Hero Showcase Mockup with Real Cafe & Food Imagery -->
            <div id="tampilan-pos" class="relative mx-auto mt-14 max-w-5xl">
                <!-- Glowing Backdrop Ring -->
                <div class="absolute -inset-1 rounded-[36px] bg-gradient-to-r from-navy-500 via-navy-200 to-orange-400 opacity-30 blur-xl"></div>

                <!-- Main Tablet / App Frame -->
                <div class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-2xl dark:border-cream-100/10 dark:bg-espresso-900">
                    <!-- Tablet Screen Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/90 px-6 py-3 dark:border-cream-100/5 dark:bg-espresso-950/70">
                        <div class="flex items-center gap-3">
                            <span class="flex h-3 w-3 rounded-full bg-navy-500"></span>
                            <span class="font-display text-sm font-bold text-slate-800 dark:text-cream-100">Kopi Senja — Cabang Kemang</span>
                            <span class="rounded-md bg-navy-100 px-2 py-0.5 text-[10px] font-bold text-navy-900 dark:bg-navy-900 dark:text-navy-200">Shift Pagi Aktif</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-cream-300">
                            <span class="font-semibold text-slate-700 dark:text-cream-200">Kasir: Budi Santoso</span>
                            <span class="text-slate-300 dark:text-espresso-700">&bull;</span>
                            <span class="rounded bg-slate-200/70 px-1.5 py-0.5 font-mono text-[11px] font-bold text-slate-700 dark:bg-espresso-800 dark:text-cream-200">POS-01</span>
                        </div>
                    </div>

                    <!-- App UI Split Preview (Menu Grid + Cart Panel) -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 text-left">
                        <!-- Menu Items Area (8 cols) -->
                        <div class="p-5 sm:p-6 lg:col-span-8 bg-slate-50/50 dark:bg-espresso-900/50">
                            <!-- Category Filter Tabs -->
                            <div class="flex flex-wrap gap-2 pb-4">
                                <span class="rounded-full bg-navy-500 px-3.5 py-1 text-xs font-bold text-white shadow-sm">Semua Menu</span>
                                <span class="rounded-full bg-white px-3.5 py-1 text-xs font-semibold text-slate-700 shadow-sm border border-slate-200/60 dark:bg-espresso-800 dark:text-cream-200 dark:border-transparent">Kopi Signature</span>
                                <span class="rounded-full bg-white px-3.5 py-1 text-xs font-semibold text-slate-700 shadow-sm border border-slate-200/60 dark:bg-espresso-800 dark:text-cream-200 dark:border-transparent">Non-Kopi</span>
                                <span class="rounded-full bg-white px-3.5 py-1 text-xs font-semibold text-slate-700 shadow-sm border border-slate-200/60 dark:bg-espresso-800 dark:text-cream-200 dark:border-transparent">Bakery & Camilan</span>
                            </div>

                            <!-- Product Cards Grid with Real Imagery -->
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                <!-- Product Card 1 -->
                                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-2.5 shadow-sm transition hover:border-navy-500 hover:shadow-md dark:border-cream-100/10 dark:bg-espresso-800">
                                    <div class="relative h-24 w-full overflow-hidden rounded-xl bg-amber-50">
                                        <img src="{{ asset('images/coffee.jpg') }}" alt="Kopi Susu Senja" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                                        <span class="absolute top-1.5 left-1.5 rounded-md bg-navy-500/90 px-1.5 py-0.5 text-[9px] font-bold text-white backdrop-blur-sm">Favorit</span>
                                    </div>
                                    <div class="mt-2 flex-1">
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-cream-100">Kopi Susu Senja</h4>
                                        <p class="text-[11px] font-semibold text-navy-500 dark:text-navy-200">Rp 24.000</p>
                                    </div>
                                </div>

                                <!-- Product Card 2 -->
                                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-2.5 shadow-sm transition hover:border-navy-500 hover:shadow-md dark:border-cream-100/10 dark:bg-espresso-800">
                                    <div class="relative h-24 w-full overflow-hidden rounded-xl bg-navy-50">
                                        <img src="{{ asset('images/matcha.jpg') }}" alt="Matcha Latte" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                                        <span class="absolute top-1.5 left-1.5 rounded-md bg-orange-600/90 px-1.5 py-0.5 text-[9px] font-bold text-white backdrop-blur-sm">Oat Milk Ready</span>
                                    </div>
                                    <div class="mt-2 flex-1">
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-cream-100">Matcha Latte</h4>
                                        <p class="text-[11px] font-semibold text-navy-500 dark:text-navy-200">Rp 30.000</p>
                                    </div>
                                </div>

                                <!-- Product Card 3 -->
                                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-2.5 shadow-sm transition hover:border-navy-500 hover:shadow-md dark:border-cream-100/10 dark:bg-espresso-800">
                                    <div class="relative h-24 w-full overflow-hidden rounded-xl bg-amber-50">
                                        <img src="{{ asset('images/croissant.jpg') }}" alt="Croissant Butter" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                                        <span class="absolute top-1.5 left-1.5 rounded-md bg-amber-500/90 px-1.5 py-0.5 text-[9px] font-bold text-white backdrop-blur-sm">Fresh Baked</span>
                                    </div>
                                    <div class="mt-2 flex-1">
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-cream-100">Croissant Butter</h4>
                                        <p class="text-[11px] font-semibold text-navy-500 dark:text-navy-200">Rp 32.000</p>
                                    </div>
                                </div>

                                <!-- Product Card 4 -->
                                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-2.5 shadow-sm transition hover:border-navy-500 hover:shadow-md dark:border-cream-100/10 dark:bg-espresso-800">
                                    <div class="relative h-24 w-full overflow-hidden rounded-xl bg-amber-50">
                                        <img src="{{ asset('images/toast.jpg') }}" alt="Roti Bakar Srikaya" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                                    </div>
                                    <div class="mt-2 flex-1">
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-cream-100">Roti Srikaya</h4>
                                        <p class="text-[11px] font-semibold text-navy-500 dark:text-navy-200">Rp 28.000</p>
                                    </div>
                                </div>

                                <!-- Product Card 5 -->
                                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-2.5 shadow-sm transition hover:border-navy-500 hover:shadow-md dark:border-cream-100/10 dark:bg-espresso-800">
                                    <div class="relative h-24 w-full overflow-hidden rounded-xl bg-amber-50">
                                        <img src="{{ asset('images/fries.jpg') }}" alt="Kentang Goreng" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                                    </div>
                                    <div class="mt-2 flex-1">
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-cream-100">Kentang Crispy</h4>
                                        <p class="text-[11px] font-semibold text-navy-500 dark:text-navy-200">Rp 25.000</p>
                                    </div>
                                </div>

                                <!-- Product Card 6 -->
                                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-2.5 shadow-sm transition hover:border-navy-500 hover:shadow-md dark:border-cream-100/10 dark:bg-espresso-800">
                                    <div class="relative h-24 w-full overflow-hidden rounded-xl bg-navy-50">
                                        <img src="{{ asset('images/tea.jpg') }}" alt="Iced Lemon Tea" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                                    </div>
                                    <div class="mt-2 flex-1">
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-cream-100">Lemon Tea Segar</h4>
                                        <p class="text-[11px] font-semibold text-navy-500 dark:text-navy-200">Rp 22.000</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cashier Bill & Checkout Area (4 cols) -->
                        <div class="border-t border-slate-200/80 bg-white p-5 lg:col-span-4 lg:border-t-0 lg:border-l dark:border-cream-100/10 dark:bg-espresso-900">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-cream-100/5">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pesanan Aktif</p>
                                    <p class="font-display text-sm font-bold text-slate-900 dark:text-cream-100">#ORD-2026-089</p>
                                </div>
                                <span class="rounded-full bg-navy-100 px-2 py-0.5 text-[10px] font-bold text-navy-900 dark:bg-navy-900 dark:text-navy-200">Dine-in (Meja 04)</span>
                            </div>

                            <!-- Cart Items List -->
                            <div class="mt-3 space-y-2.5 text-xs">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-slate-800 dark:text-cream-100">2x Kopi Susu Senja</p>
                                        <p class="text-[10px] text-slate-400">Less Sugar &bull; Normal Ice</p>
                                    </div>
                                    <span class="font-bold text-slate-900 dark:text-cream-100">Rp 48.000</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-slate-800 dark:text-cream-100">1x Croissant Butter</p>
                                        <p class="text-[10px] text-slate-400">Warm</p>
                                    </div>
                                    <span class="font-bold text-slate-900 dark:text-cream-100">Rp 32.000</span>
                                </div>
                            </div>

                            <!-- Calculation Breakdown -->
                            <div class="mt-4 border-t border-slate-100 pt-3 text-[11px] space-y-1.5 dark:border-cream-100/5">
                                <div class="flex justify-between text-slate-500 dark:text-cream-300">
                                    <span>Subtotal</span>
                                    <span>Rp 80.000</span>
                                </div>
                                <div class="flex justify-between text-navy-500 font-semibold dark:text-navy-200">
                                    <span>Pajak Daerah (PB1 10%)</span>
                                    <span>Rp 8.000</span>
                                </div>
                                <div class="flex justify-between text-xs font-extrabold text-slate-900 pt-1 border-t border-dashed border-slate-200 dark:border-cream-100/10 dark:text-cream-100">
                                    <span>Total Akhir</span>
                                    <span class="text-sm text-navy-500 dark:text-navy-200">Rp 88.000</span>
                                </div>
                            </div>

                            <!-- Instant Payment Buttons -->
                            <div class="mt-4 grid grid-cols-3 gap-1.5">
                                <button type="button" class="rounded-xl border border-navy-500/40 bg-navy-50 py-2 text-center text-[11px] font-bold text-navy-500 transition hover:bg-navy-100 dark:bg-navy-900 dark:text-navy-200">
                                    QRIS
                                </button>
                                <button type="button" class="rounded-xl border border-slate-200 bg-slate-50 py-2 text-center text-[11px] font-bold text-slate-700 transition hover:bg-slate-100 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-200">
                                    Tunai
                                </button>
                                <button type="button" class="rounded-xl border border-slate-200 bg-slate-50 py-2 text-center text-[11px] font-bold text-slate-700 transition hover:bg-slate-100 dark:border-cream-100/10 dark:bg-espresso-800 dark:text-cream-200">
                                    Debit/EDC
                                </button>
                            </div>

                            <button type="button" class="mt-2.5 w-full rounded-xl bg-gradient-to-r from-navy-500 to-navy-400 py-2.5 text-center text-xs font-bold text-white shadow-md shadow-navy-500/30 transition hover:from-navy-900 hover:to-navy-500">
                                Bayar & Cetak Struk ⚡
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Floating Glass Badge 1 (Top Left) -->
                <div class="absolute -top-6 -left-4 hidden sm:flex items-center gap-3 rounded-2xl border border-white/80 bg-white/95 p-3.5 shadow-xl backdrop-blur-md dark:border-cream-100/10 dark:bg-espresso-900/95">
                    <img src="{{ asset('images/avatar-2.jpg') }}" alt="Barista" class="h-10 w-10 rounded-xl object-cover shadow-sm" />
                    <div class="text-left">
                        <p class="text-[11px] font-bold text-slate-900 dark:text-cream-100">Kecepatan Transaksi 2.4s</p>
                        <p class="text-[10px] text-navy-500 font-semibold dark:text-navy-200">Antrean kafe 3x lebih lancar</p>
                    </div>
                </div>

                <!-- Floating Glass Badge 2 (Bottom Right) -->
                <div class="absolute -bottom-6 -right-4 hidden sm:flex items-center gap-3 rounded-2xl border border-white/80 bg-white/95 p-3.5 shadow-xl backdrop-blur-md dark:border-cream-100/10 dark:bg-espresso-900/95">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-navy-500/15 text-navy-500 font-bold dark:bg-navy-500/20 dark:text-navy-200">
                        ✓
                    </div>
                    <div class="text-left">
                        <p class="text-[11px] font-bold text-slate-900 dark:text-cream-100">Pajak PB1 Terlapor Otomatis</p>
                        <p class="text-[10px] text-slate-500 dark:text-cream-300">Sesuai aturan dispenda daerah</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Counter Section -->
        <section class="border-y border-slate-200/80 bg-white/60 py-10 backdrop-blur-sm dark:border-cream-100/10 dark:bg-espresso-900/60">
            <div class="mx-auto grid max-w-6xl grid-cols-2 gap-6 px-4 text-center sm:grid-cols-4 sm:px-6">
                <div>
                    <p class="font-display text-3xl font-black tracking-tight text-navy-500 sm:text-4xl dark:text-navy-200">99.98%</p>
                    <p class="mt-1 text-xs font-semibold text-slate-600 dark:text-cream-300">Uptime POS Tanpa Gangguan</p>
                </div>
                <div>
                    <p class="font-display text-3xl font-black tracking-tight text-orange-600 sm:text-4xl dark:text-orange-400">500+</p>
                    <p class="mt-1 text-xs font-semibold text-slate-600 dark:text-cream-300">Kafe & Kedai Kopi Aktif</p>
                </div>
                <div>
                    <p class="font-display text-3xl font-black tracking-tight text-amber-500 sm:text-4xl dark:text-amber-400">&lt; 3 Detik</p>
                    <p class="mt-1 text-xs font-semibold text-slate-600 dark:text-cream-300">Rata-rata Waktu Checkout</p>
                </div>
                <div>
                    <p class="font-display text-3xl font-black tracking-tight text-navy-500 sm:text-4xl dark:text-navy-200">100%</p>
                    <p class="mt-1 text-xs font-semibold text-slate-600 dark:text-cream-300">Akurat Pajak PB1 & Kas</p>
                </div>
            </div>
        </section>

        <!-- Main Features with Real Photography -->
        <section id="fitur" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="text-center">
                <span class="rounded-full bg-navy-100 px-3.5 py-1 text-xs font-bold text-navy-900 dark:bg-navy-900 dark:text-navy-200">Fitur Lengkap Kafe Modern</span>
                <h2 class="mt-4 font-display text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-cream-100">
                    Dibuat khusus untuk barista, kasir, dan pemilik kafe.
                </h2>
                <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-600 sm:text-base dark:text-cream-300">
                    Tidak ada lagi bon hilang, salah hitung stok bahan baku, atau antrean kasir yang menumpuk.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="mt-14 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Card 1 -->
                <div class="group overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-soft transition hover:-translate-y-1 hover:shadow-xl dark:border-cream-100/10 dark:bg-espresso-900">
                    <div class="relative h-48 w-full overflow-hidden bg-slate-100">
                        <img src="{{ asset('images/hero-cafe.jpg') }}" alt="Barista membuat kopi" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                        <span class="absolute bottom-3 left-4 rounded-lg bg-navy-500 px-2.5 py-1 text-xs font-bold text-white shadow-sm">Kasir Cepat</span>
                    </div>
                    <div class="p-6">
                        <h3 class="font-display text-lg font-bold text-slate-900 dark:text-cream-100">Navigasi Sentuh Khusus Tablet</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-cream-300">
                            Tombol menu besar dan responsif. Mendukung varian (Hot/Iced), opsi gula, ekstra shot espresso, hingga split payment dalam hitungan detik.
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div id="menu-digital" class="group overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-soft transition hover:-translate-y-1 hover:shadow-xl dark:border-cream-100/10 dark:bg-espresso-900">
                    <div class="relative h-48 w-full overflow-hidden bg-slate-100">
                        <img src="{{ asset('images/pos-counter.jpg') }}" alt="Storefront dan Counter Kafe" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                        <span class="absolute bottom-3 left-4 rounded-lg bg-orange-500 px-2.5 py-1 text-xs font-bold text-white shadow-sm">Menu Digital Mandiri</span>
                    </div>
                    <div class="p-6">
                        <h3 class="font-display text-lg font-bold text-slate-900 dark:text-cream-100">Storefront & QR Order dari Meja</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-cream-300">
                            Setiap cabang otomatis punya website menu online publik. Pelanggan bisa scan barcode di meja dan pesan langsung tanpa mengantre di kasir.
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="group overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-soft transition hover:-translate-y-1 hover:shadow-xl dark:border-cream-100/10 dark:bg-espresso-900">
                    <div class="relative h-48 w-full overflow-hidden bg-slate-100">
                        <img src="{{ asset('images/croissant.jpg') }}" alt="Pastry dan Kontrol Stok" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                        <span class="absolute bottom-3 left-4 rounded-lg bg-amber-500 px-2.5 py-1 text-xs font-bold text-white shadow-sm">Inventaris Cerdas</span>
                    </div>
                    <div class="p-6">
                        <h3 class="font-display text-lg font-bold text-slate-900 dark:text-cream-100">Buku Besar Stok & Resep Otomatis</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-cream-300">
                            Stok cup, susu, biji kopi, dan pastry otomatis terpotong saat transaksi dibayar. Dapatkan peringatan dini saat stok menipis sebelum habis.
                        </p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="group overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-6 shadow-soft transition hover:-translate-y-1 hover:shadow-xl dark:border-cream-100/10 dark:bg-espresso-900">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-navy-100 text-navy-500 dark:bg-navy-900 dark:text-navy-200">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-cream-100">Laporan Pajak Daerah PB1 Instan</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-cream-300">
                        Pemisahan omzet kotor, omzet bersih, dan kewajiban pajak PB1 restoran secara otomatis. Siap ekspor ke file CSV/Excel kapan saja.
                    </p>
                </div>

                <!-- Card 5 -->
                <div class="group overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-6 shadow-soft transition hover:-translate-y-1 hover:shadow-xl dark:border-cream-100/10 dark:bg-espresso-900">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-700 dark:bg-navy-900 dark:text-orange-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-cream-100">Multi-Outlet & Multi-Role User</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-cream-300">
                        Kelola puluhan cabang dalam satu akun induk. Hak akses terisolasi untuk Owner, Store Manager, Barista Kasir, hingga Akuntan.
                    </p>
                </div>

                <!-- Card 6 -->
                <div class="group overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-6 shadow-soft transition hover:-translate-y-1 hover:shadow-xl dark:border-cream-100/10 dark:bg-espresso-900">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-cream-100">Audit Trail & Keamanan PIN Kasir</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-cream-300">
                        Pembatalan pesanan dan void transaksi wajib otorisasi PIN manager. Setiap buka-tutup kas tercatat lengkap tanpa celah manipulasi.
                    </p>
                </div>
            </div>
        </section>

        <!-- Visual Cafe Showcase Banner -->
        <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-navy-900 via-navy-500 to-navy-900 p-8 sm:p-12 text-white shadow-2xl">
                <div class="grid items-center gap-8 lg:grid-cols-2">
                    <div>
                        <span class="rounded-full bg-navy-500/20 px-3 py-1 text-xs font-bold text-orange-300">Pengalaman Terbaik Pelanggan</span>
                        <h2 class="mt-4 font-display text-3xl font-black tracking-tight sm:text-4xl text-white">
                            Dari kedai kopi mungil hingga rantai resto berkembang.
                        </h2>
                        <p class="mt-4 text-sm leading-relaxed text-slate-300 sm:text-base">
                            Sitrans dirancang dengan arsitektur multi-tenant modern yang ringan, cepat, dan tidak memberatkan koneksi internet kafe Anda. Struk dapat dicetak langsung melalui printer thermal USB maupun Bluetooth.
                        </p>
                        <div class="mt-6 flex flex-wrap gap-4 text-xs font-semibold text-navy-200">
                            <span>✓ Dukung Printer Thermal 58mm & 80mm</span>
                            <span>✓ Struk Digital via WhatsApp & Email</span>
                            <span>✓ Mode Offline Fallback</span>
                        </div>
                    </div>
                    <div class="relative grid grid-cols-2 gap-3">
                        <img src="{{ asset('images/coffee.jpg') }}" alt="Espresso extraction" class="h-44 w-full rounded-2xl object-cover shadow-md" />
                        <img src="{{ asset('images/pos-counter.jpg') }}" alt="Cafe bar" class="h-44 w-full rounded-2xl object-cover shadow-md mt-6" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section with Authentic Avatars -->
        <section id="testimoni" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="text-center">
                <span class="rounded-full bg-navy-100 px-3.5 py-1 text-xs font-bold text-navy-900 dark:bg-navy-900 dark:text-navy-200">Cerita Pengguna</span>
                <h2 class="mt-4 font-display text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-cream-100">
                    Dipercaya oleh barista & pemilik kafe di seluruh Indonesia
                </h2>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-3">
                <!-- Testimonial 1 -->
                <div class="flex flex-col justify-between rounded-3xl border border-slate-200/80 bg-white p-6 shadow-soft dark:border-cream-100/10 dark:bg-espresso-900">
                    <div>
                        <div class="flex text-amber-400">★★★★★</div>
                        <p class="mt-4 text-sm leading-relaxed text-slate-700 dark:text-cream-200">
                            "Sebelum pakai Sitrans, tiap akhir bulan pusing hitung pajak restoran PB1 dan selisih stok biji kopi. Sekarang semuanya otomatis terekap rapi. Barista saya juga langsung bisa pakai tanpa training lama."
                        </p>
                    </div>
                    <div class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-4 dark:border-cream-100/5">
                        <img src="{{ asset('images/avatar-2.jpg') }}" alt="Dimas Prasetyo" class="h-10 w-10 rounded-full object-cover" />
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-cream-100">Dimas Prasetyo</h4>
                            <p class="text-[11px] text-slate-500 dark:text-cream-300">Owner, Senja Coffee Lab (3 Cabang Jakarta)</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="flex flex-col justify-between rounded-3xl border border-slate-200/80 bg-white p-6 shadow-soft dark:border-cream-100/10 dark:bg-espresso-900">
                    <div>
                        <div class="flex text-amber-400">★★★★★</div>
                        <p class="mt-4 text-sm leading-relaxed text-slate-700 dark:text-cream-200">
                            "Fitur Menu Digital mandiri via QR code benar-benar penyelamat waktu jam sibuk makan siang. Customer pesan dari meja, kasir cuma tinggal verifikasi. Antrean jadi jauh lebih tertib!"
                        </p>
                    </div>
                    <div class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-4 dark:border-cream-100/5">
                        <img src="{{ asset('images/avatar-1.jpg') }}" alt="Sarah Amanda" class="h-10 w-10 rounded-full object-cover" />
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-cream-100">Sarah Amanda</h4>
                            <p class="text-[11px] text-slate-500 dark:text-cream-300">Co-founder, Bloom Matcha & Bakery Bandung</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="flex flex-col justify-between rounded-3xl border border-slate-200/80 bg-white p-6 shadow-soft dark:border-cream-100/10 dark:bg-espresso-900">
                    <div>
                        <div class="flex text-amber-400">★★★★★</div>
                        <p class="mt-4 text-sm leading-relaxed text-slate-700 dark:text-cream-200">
                            "UI-nya cerah, modern, dan sangat intuitif. Tombol-tombol kasir sangat pas di layar tablet iPad kami. Split payment antara tunai dan QRIS tidak pernah ada selisih di buku kas lagi."
                        </p>
                    </div>
                    <div class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-4 dark:border-cream-100/5">
                        <img src="{{ asset('images/avatar-3.jpg') }}" alt="Reza Firmansyah" class="h-10 w-10 rounded-full object-cover" />
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-cream-100">Reza Firmansyah</h4>
                            <p class="text-[11px] text-slate-500 dark:text-cream-300">Operational Manager, Teduh Artisan Surabaya</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bright Radiant Call to Action (CTA) -->
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="relative overflow-hidden rounded-[32px] bg-gradient-to-r from-navy-500 via-navy-400 to-navy-900 px-6 py-14 text-center text-white shadow-2xl sm:px-12">
                <!-- Background decorative glow -->
                <div class="pointer-events-none absolute -top-24 -right-24 h-64 w-64 rounded-full bg-white/20 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-amber-300/20 blur-2xl"></div>

                <div class="relative z-10 mx-auto max-w-2xl">
                    <span class="inline-flex rounded-full bg-white/20 px-4 py-1 text-xs font-bold tracking-wide uppercase backdrop-blur-sm">
                        Mulai Hari Ini Tanpa Resiko
                    </span>
                    <h2 class="mt-4 font-display text-3xl font-black tracking-tight sm:text-5xl">
                        Siap jadikan kafe Anda lebih rapi, cepat, & menguntungkan?
                    </h2>
                    <p class="mt-4 text-sm sm:text-base text-navy-100">
                        Coba Sitrans gratis 14 hari penuh. Semua fitur terbuka tanpa syarat, tidak butuh kartu kredit, dan langsung siap pakai di kafe Anda.
                    </p>
                    <div class="mt-8 flex flex-wrap justify-center gap-4">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-2xl bg-white px-8 py-4 text-sm font-extrabold text-navy-900 shadow-xl transition hover:bg-slate-50 hover:scale-105">
                            <span>Daftar Sekarang — Gratis 14 Hari</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                        <a href="{{ url('/app/login') }}" class="rounded-2xl border border-white/30 bg-white/10 px-8 py-4 text-sm font-bold text-white backdrop-blur-sm transition hover:bg-white/20">
                            Masuk ke Akun
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modern Footer -->
        <footer class="border-t border-slate-200/80 bg-white/80 py-12 text-slate-600 backdrop-blur-sm dark:border-cream-100/10 dark:bg-espresso-950 dark:text-cream-300">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('images/sitrans-logo.png') }}" alt="Sitrans" class="h-9 w-9 rounded-lg bg-white object-contain p-0.5 shadow-sm" />
                        <span class="font-display text-lg font-extrabold text-slate-900 dark:text-cream-100">Sitrans</span>
                        <span class="text-xs text-slate-400">&mdash; Sistem Kasir & Manajemen Kafe Modern</span>
                    </div>

                    <div class="flex items-center gap-6 text-xs font-semibold">
                        <a href="{{ route('register') }}" class="hover:text-navy-500">Daftar Akun</a>
                        <a href="{{ url('/app/login') }}" class="hover:text-navy-500">Login</a>
                        <a href="{{ route('pos') }}" class="hover:text-navy-500">Aplikasi Kasir</a>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-100 pt-6 text-center text-xs text-slate-500 dark:border-cream-100/5 dark:text-cream-300 flex flex-col sm:flex-row items-center justify-between gap-2">
                    <p>&copy; {{ date('Y') }} Sitrans Indonesia. Hak Cipta Dilindungi.</p>
                    <p class="inline-flex items-center gap-1.5 text-[11px]">
                        <span class="h-2 w-2 rounded-full bg-navy-500"></span>
                        Semua sistem beroperasi normal
                    </p>
                </div>
            </div>
        </footer>
    </div>
</x-layouts.base>
