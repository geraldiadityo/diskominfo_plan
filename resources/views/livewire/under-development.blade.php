<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 flex items-center justify-center min-h-[calc(100vh-12rem)]">
    <div class="max-w-2xl w-full text-center">
        <!-- Status Badge -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-xs font-semibold uppercase tracking-wider mb-6 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            Sedang Dalam Pengembangan
        </div>

        <!-- Illustration / Icon Container -->
        <div class="relative mx-auto w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-tr from-[#af101a]/10 via-amber-500/10 to-[#af101a]/5 dark:from-[#af101a]/20 dark:via-amber-500/20 dark:to-transparent flex items-center justify-center border border-[#af101a]/20 dark:border-[#af101a]/30 shadow-inner mb-8">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white dark:bg-[#1a1a1a] shadow-md flex items-center justify-center text-[#af101a] dark:text-[#ffb4ab]">
                <svg class="w-9 h-9 sm:w-11 sm:h-11" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                </svg>
            </div>
            <!-- Decorative dots -->
            <div class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-amber-400 rounded-full border-2 border-white dark:border-[#121212]"></div>
        </div>

        <!-- Title -->
        <h1 class="text-3xl sm:text-4xl font-extrabold text-[#191c1e] dark:text-white tracking-tight mb-4" style="font-family: 'Plus Jakarta Sans', sans-serif;">
            @if(filled($feature) && $feature !== 'Fitur Ini' && $feature !== 'Halaman Ini')
                Halaman <span class="text-[#af101a] dark:text-[#ffb4ab]">{{ $feature }}</span> Sedang Disiapkan
            @else
                Halaman Ini Sedang Disiapkan
            @endif
        </h1>

        <!-- Subtitle Description -->
        <p class="text-base sm:text-lg text-[#5b403d] dark:text-[#a0a0a0] max-w-xl mx-auto leading-relaxed mb-8" style="font-family: 'Inter', sans-serif;">
            Kami sedang bekerja keras menyempurnakan modul ini agar dapat menyajikan data dan layanan yang terintegrasi, transparan, dan akurat untuk masyarakat. Fitur ini akan segera dapat diakses.
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#af101a] hover:bg-[#8f0d15] text-white font-semibold text-sm transition-all shadow-sm hover:shadow focus:ring-2 focus:ring-[#af101a]/40" style="font-family: 'Manrope', sans-serif;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>

            <a href="https://www.lapor.go.id" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-white dark:bg-[#1a1a1a] hover:bg-[#f2f4f7] dark:hover:bg-[#252525] text-[#5b403d] dark:text-[#e0e0e0] border border-[#cbd5e1] dark:border-[#2f2f2f] font-semibold text-sm transition-all shadow-sm" style="font-family: 'Manrope', sans-serif;">
                <span>Layanan Pengaduan (SP4N-LAPOR!)</span>
                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </a>
        </div>
    </div>
</div>

