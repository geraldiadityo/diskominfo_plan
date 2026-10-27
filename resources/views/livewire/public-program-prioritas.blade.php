<div class="relative bg-[#07181a] dark:bg-[#061214] text-white min-h-[calc(100vh-4rem)] py-8 transition-colors duration-200">
    <!-- Subtle Blueprint Cyber Grid Background (Matching Mockup) -->
    <div class="absolute inset-0 pointer-events-none opacity-20"
        style="background-image: linear-gradient(to right, rgba(45, 212, 191, 0.15) 1px, transparent 1px), linear-gradient(to bottom, rgba(45, 212, 191, 0.15) 1px, transparent 1px); background-size: 44px 44px;">
    </div>

    <div class="relative max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb & Header Box -->
        <div class="mb-8">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-5 font-medium">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 text-gray-400 hover:text-teal-300 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Beranda</span>
                </a>
                <span class="text-teal-700">></span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-teal-950/80 border border-teal-500/50 text-teal-300 shadow-sm shadow-teal-950">
                    Program Prioritas
                </span>
            </div>

            <!-- Main Title Header -->
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-[#00b497] to-[#047857] flex items-center justify-center shadow-lg shadow-teal-500/25 text-white flex-shrink-0 mt-0.5">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Program Prioritas {{ \App\Models\AppSetting::getAppName() }}
                    </h1>
                    <p class="text-xs text-teal-300/80 font-medium tracking-wide mt-0.5">
                        Akselerasi Kinerja & Capaian Program Strategis Daerah
                    </p>
                </div>
            </div>

            <!-- Description Paragraph -->
            <p class="mt-4 text-xs sm:text-sm text-gray-300/80 leading-relaxed max-w-4xl">
                Pantau kinerja dan capaian program prioritas pemerintah Kota Sungai Penuh hingga tingkat sub kegiatan. Data target dan realisasi diambil langsung dari Renja SKPD per triwulan. Pilih tahun evaluasi dan klik program untuk melihat rincian kegiatan.
            </p>
        </div>

        <!-- 3-Column Program Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-10">
            @foreach ($programs as $program)
                @php
                    $isSelected = ($selectedProgramId === $program->id);
                    $iconType = $program->icon;
                @endphp

                <div
                    wire:key="prog-card-{{ $program->id }}"
                    wire:click="selectProgram({{ $program->id }})"
                    class="group relative overflow-hidden rounded-2xl p-6 transition-all duration-300 cursor-pointer select-none
                        {{ $isSelected
                            ? 'bg-[#103a3d] border-2 border-teal-400 ring-4 ring-teal-400/20 shadow-xl shadow-teal-950/80 -translate-y-1'
                            : 'bg-[#0c2a2b]/85 hover:bg-[#0f3436] border border-[#164e50] hover:border-teal-400/40 shadow-md hover:-translate-y-0.5' }}">

                    <!-- Card Body -->
                    <div class="relative z-10 flex flex-col justify-between h-full min-h-[92px]">
                        <div>
                            <h3 class="text-lg font-bold text-white group-hover:text-teal-200 tracking-tight transition-colors line-clamp-1">
                                {{ $program->nama_program }}
                            </h3>
                            <p class="text-xs font-medium text-teal-200/70 mt-1">
                                {{ $program->indikators_count }} Sub Kegiatan Terdaftar
                            </p>
                        </div>

                        <!-- Action Link -->
                        <div class="mt-4 flex items-center">
                            @if ($isSelected)
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-300 tracking-wide">
                                    <span>Sedang Dilihat</span>
                                    <svg class="w-3.5 h-3.5 text-teal-300 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-teal-400/60 group-hover:text-teal-300 transition-colors">
                                    <span>Lihat Detail Sub Kegiatan</span>
                                    <svg class="w-3 h-3 text-teal-400/60 group-hover:text-teal-300 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Faded Background Watermark Icon -->
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none transition-transform duration-500 group-hover:scale-110">
                        @if ($iconType === 'shield-check')
                            <svg class="w-20 h-20 text-teal-400/15 group-hover:text-teal-400/25 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        @elseif ($iconType === 'heart')
                            <svg class="w-20 h-20 text-teal-400/15 group-hover:text-teal-400/25 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        @elseif ($iconType === 'academic-cap')
                            <svg class="w-20 h-20 text-teal-400/15 group-hover:text-teal-400/25 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                        @elseif ($iconType === 'plus-circle')
                            <svg class="w-20 h-20 text-teal-400/15 group-hover:text-teal-400/25 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 8v8m-4-4h8m6 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif ($iconType === 'star')
                            <svg class="w-20 h-20 text-teal-400/15 group-hover:text-teal-400/25 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        @elseif ($iconType === 'user-group')
                            <svg class="w-20 h-20 text-teal-400/15 group-hover:text-teal-400/25 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        @elseif ($iconType === 'light-bulb')
                            <svg class="w-20 h-20 text-teal-400/15 group-hover:text-teal-400/25 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        @elseif ($iconType === 'face-smile')
                            <svg class="w-20 h-20 text-teal-400/15 group-hover:text-teal-400/25 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="w-20 h-20 text-teal-400/15 group-hover:text-teal-400/25 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Detail Table Section (Appears when a program card is clicked) -->
        @if ($selectedProgram)
            <div id="tabel-indikator" class="bg-white dark:bg-[#121212] rounded-2xl shadow-xl border border-[#e0e3e6] dark:border-[#1f1f1f] overflow-hidden mb-12 scroll-mt-24 transition-all duration-300">
                <!-- Top Header Banner of Table -->
                <div class="px-6 py-5 bg-[#f7f9fc] dark:bg-[#181818] border-b border-[#e0e3e6] dark:border-[#1f1f1f] flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-block px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-teal-700 bg-teal-50 dark:bg-teal-950/40 dark:text-teal-300 rounded-md border border-teal-200 dark:border-teal-800">
                                Program Prioritas #{{ $selectedProgram->urutan }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                • {{ $indikators->count() }} Sub Kegiatan Terdaftar
                            </span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-extrabold uppercase text-[#1a3c6b] dark:text-teal-400 tracking-tight" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            {{ $selectedProgram->nama_program }}
                        </h2>
                        @if ($selectedProgram->deskripsi)
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 max-w-3xl">
                                {{ $selectedProgram->deskripsi }}
                            </p>
                        @endif
                    </div>

                    <!-- Close Table Button -->
                    <button
                        wire:click="closeTable"
                        type="button"
                        class="self-start md:self-center inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#242424] text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#2e2e2e] transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Tutup Tabel</span>
                    </button>
                </div>

                <!-- Filter & Search Panel -->
                <div class="px-6 py-4 bg-[#f1f5f9] dark:bg-[#1a1a1a] border-b border-[#e0e3e6] dark:border-[#1f1f1f]">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <!-- Year Selector Pills -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5 mr-1">
                                <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Tahun:</span>
                            </span>
                            <div class="inline-flex rounded-xl p-1 bg-white dark:bg-[#242424] border border-gray-300 dark:border-gray-700 shadow-sm">
                                @foreach ($years as $y)
                                    <button
                                        type="button"
                                        wire:click="setTahun({{ $y }})"
                                        class="px-3.5 py-1 text-xs font-bold rounded-lg transition-all {{ $tahun === $y ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-sm shadow-teal-500/25' : 'text-gray-600 dark:text-gray-400 hover:text-teal-600 dark:hover:text-teal-300 hover:bg-gray-100 dark:hover:bg-[#2e2e2e]' }}">
                                        {{ $y }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Search & SKPD Filter -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Search Input -->
                            <div class="relative min-w-[240px]">
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="Cari rincian, sub kegiatan..."
                                    class="w-full text-xs pl-8 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#242424] text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <!-- SKPD Filter -->
                            @if (count($skpdList) > 0)
                                <select
                                    wire:model.live="selectedSkpd"
                                    class="text-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#242424] text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-teal-500 max-w-[260px] cursor-pointer">
                                    <option value="">Semua Penanggung Jawab</option>
                                    @foreach ($skpdList as $skpdName)
                                        <option value="{{ $skpdName }}">{{ $skpdName }}</option>
                                    @endforeach
                                </select>
                            @endif

                            @if (!empty($search) || !empty($selectedSkpd))
                                <button
                                    wire:click="resetFilter"
                                    type="button"
                                    class="px-2.5 py-2 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors font-medium border border-red-200 dark:border-red-900/50">
                                    Reset
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Table Content: Grouped by Rincian (No duplicates, matching screenshot) -->
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse border border-gray-300 dark:border-gray-700">
                        <!-- Table Header -->
                        <thead>
                            <!-- Row 1: Main Headers -->
                            <tr class="bg-[#1a3c6b] text-white">
                                <th rowspan="2" class="px-3 py-3 border border-blue-900 text-left font-bold align-middle min-w-[180px] uppercase tracking-wider">
                                    Rincian Program Prioritas
                                </th>
                                <th rowspan="2" class="px-3 py-3 border border-blue-900 text-left font-bold align-middle min-w-[220px] uppercase tracking-wider">
                                    Penjelasan
                                </th>
                                <th rowspan="2" class="px-3 py-3 border border-blue-900 text-left font-bold align-middle min-w-[280px] uppercase tracking-wider">
                                    Program / Kegiatan / Sub Kegiatan
                                </th>
                                <th colspan="2" class="px-2 py-2 border border-blue-900 text-center font-bold uppercase tracking-wider">
                                    Realisasi Triwulan I
                                </th>
                                <th colspan="2" class="px-2 py-2 border border-blue-900 text-center font-bold uppercase tracking-wider">
                                    Realisasi Triwulan II
                                </th>
                                <th colspan="2" class="px-2 py-2 border border-blue-900 text-center font-bold uppercase tracking-wider">
                                    Realisasi Triwulan III
                                </th>
                                <th colspan="2" class="px-2 py-2 border border-blue-900 text-center font-bold uppercase tracking-wider">
                                    Realisasi Triwulan IV
                                </th>
                                <th rowspan="2" class="px-3 py-3 border border-blue-900 text-center font-bold align-middle min-w-[160px] uppercase tracking-wider">
                                    Penanggung Jawab
                                </th>
                            </tr>

                            <!-- Row 2: Sub-headers for Triwulans -->
                            <tr class="bg-[#214a82] text-white text-[11px]">
                                <th class="px-2 py-1.5 border border-blue-900 text-center font-semibold w-20">Fisik</th>
                                <th class="px-2 py-1.5 border border-blue-900 text-center font-semibold w-24 bg-[#1c3e6d]">Keuangan</th>
                                <th class="px-2 py-1.5 border border-blue-900 text-center font-semibold w-20">Fisik</th>
                                <th class="px-2 py-1.5 border border-blue-900 text-center font-semibold w-24 bg-[#1c3e6d]">Keuangan</th>
                                <th class="px-2 py-1.5 border border-blue-900 text-center font-semibold w-20">Fisik</th>
                                <th class="px-2 py-1.5 border border-blue-900 text-center font-semibold w-24 bg-[#1c3e6d]">Keuangan</th>
                                <th class="px-2 py-1.5 border border-blue-900 text-center font-semibold w-20">Fisik</th>
                                <th class="px-2 py-1.5 border border-blue-900 text-center font-semibold w-24 bg-[#1c3e6d]">Keuangan</th>
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-[#121212] text-gray-900 dark:text-gray-100">
                            @php
                                $groupedRincian = $indikators->groupBy(fn ($i) => $i->rincian ?: 'Umum');
                            @endphp

                            @forelse ($groupedRincian as $rincianName => $items)
                                @php
                                    $itemsList = $items->values();
                                    $totalItems = $itemsList->count();
                                    $firstItem = $itemsList->first();

                                    // Precalculate contiguous SKPD spans within this rincian block
                                    $skpdSpans = [];
                                    $currSkpd = null;
                                    $startIdx = 0;
                                    $count = 0;

                                    foreach ($itemsList as $idx => $ind) {
                                        $skpd = $ind->urusan->first()?->nama_urusan
                                            ?? ($ind->realisasis->first()?->skpd?->nama_skpd ?? 'Lintas SKPD');
                                        if ($skpd !== $currSkpd) {
                                            if ($currSkpd !== null) {
                                                $skpdSpans[$startIdx] = ['name' => $currSkpd, 'span' => $count];
                                            }
                                            $currSkpd = $skpd;
                                            $startIdx = $idx;
                                            $count = 1;
                                        } else {
                                            $count++;
                                        }
                                    }
                                    if ($currSkpd !== null) {
                                        $skpdSpans[$startIdx] = ['name' => $currSkpd, 'span' => $count];
                                    }
                                @endphp

                                @foreach ($itemsList as $itemIdx => $ind)
                                    @php
                                        $sub = $ind->subKegiatan;
                                        $kodeLengkap = $sub?->kode_lengkap;
                                        $progName = $sub?->kegiatan?->program?->nomenklatur;
                                        $kegName = $sub?->kegiatan?->nomenklatur;
                                        $subName = $sub?->nomenklatur ?? $ind->nama_indikator;
                                    @endphp

                                    <tr class="hover:bg-blue-50/40 dark:hover:bg-[#1a2333]/50 transition-colors {{ $loop->even ? 'bg-gray-50/30 dark:bg-[#151515]' : 'bg-white dark:bg-[#121212]' }}">
                                        <!-- Rincian Program Prioritas (Rowspan across this group, no double) -->
                                        @if ($itemIdx === 0)
                                            <td rowspan="{{ $totalItems }}" class="px-3.5 py-3 border border-gray-300 dark:border-gray-700 align-top font-bold text-gray-900 dark:text-white text-[12px] leading-snug bg-gray-50/70 dark:bg-[#161616]">
                                                {{ $rincianName }}
                                            </td>

                                            <!-- Penjelasan (Rowspan across this group, no double) -->
                                            <td rowspan="{{ $totalItems }}" class="px-3.5 py-3 border border-gray-300 dark:border-gray-700 align-top text-[11px] text-gray-600 dark:text-gray-300 leading-relaxed bg-gray-50/40 dark:bg-[#141414]">
                                                {{ $firstItem->detail ?? '-' }}
                                            </td>
                                        @endif

                                        <!-- Program / Kegiatan / Sub Kegiatan -->
                                        <td class="px-3 py-2.5 border border-gray-300 dark:border-gray-700 align-top whitespace-normal">
                                            @if ($progName)
                                                <div class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                                    {{ $progName }}
                                                </div>
                                            @endif

                                            @if ($kegName)
                                                <div class="text-[10px] text-gray-600 dark:text-gray-300 ml-1.5 pl-1.5 border-l border-teal-500/30">
                                                    {{ $kegName }}
                                                </div>
                                            @endif

                                            <div class="font-bold text-gray-900 dark:text-teal-300 text-[12px] mt-0.5 ml-2 pl-1.5 border-l-2 border-teal-500">
                                                {{ $subName }}
                                            </div>
                                        </td>

                                        <!-- 4 Triwulans: Real-time from Renja SKPD (or fallback to seeder, - if no data) -->
                                        @for ($tw = 1; $tw <= 4; $tw++)
                                            @php
                                                // 1. Check live Renja first
                                                $rk = $ind->liveRenja?->realisasi_kegiatans?->firstWhere('triwulan', $tw);
                                                // 2. Fallback to ProgulRealisasi
                                                $pr = $ind->realisasis->firstWhere('triwulan', $tw);

                                                $targetF = $rk?->target_fisik ?? $pr?->target_fisik;
                                                $realF   = $rk?->realisasi_fisik ?? $pr?->realisasi_fisik;
                                                $targetK = $rk?->target_keuangan ?? $pr?->target_keuangan;
                                                $realK   = $rk?->realisasi_keuangan ?? $pr?->realisasi_keuangan;

                                                // Clean display: '-' if null or 0 without renja
                                                $hasFisik = ($targetF !== null || $realF !== null);
                                                $hasKeu   = ($targetK !== null || $realK !== null);
                                            @endphp

                                            <!-- Fisik -->
                                            <td class="px-2 py-2 border border-gray-300 dark:border-gray-700 text-center align-middle font-medium">
                                                @if ($hasFisik)
                                                    <div class="flex flex-col items-center gap-0.5">
                                                        <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                                            Tgt: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $targetF !== null ? (float)$targetF . '%' : '-' }}</span>
                                                        </div>
                                                        <div class="text-[11px] font-extrabold {{ $realF !== null ? 'text-teal-600 dark:text-teal-400' : 'text-gray-400' }}">
                                                            {{ $realF !== null ? (float)$realF . '%' : '-' }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-xs">-</span>
                                                @endif
                                            </td>

                                            <!-- Keuangan -->
                                            <td class="px-2 py-2 border border-gray-300 dark:border-gray-700 text-center align-middle bg-blue-50/15 dark:bg-blue-950/10">
                                                @if ($hasKeu && ($targetK > 0 || $realK > 0))
                                                    <div class="flex flex-col items-center gap-0.5">
                                                        <div class="text-[9px] text-gray-500 dark:text-gray-400">
                                                            {{ $targetK > 0 ? 'Rp ' . number_format($targetK, 0, ',', '.') : '-' }}
                                                        </div>
                                                        <div class="text-[10px] font-bold {{ $realK > 0 ? 'text-blue-700 dark:text-blue-400' : 'text-gray-400' }}">
                                                            {{ $realK > 0 ? 'Rp ' . number_format($realK, 0, ',', '.') : '-' }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-xs">-</span>
                                                @endif
                                            </td>
                                        @endfor

                                        <!-- Penanggung Jawab (Merged by contiguous SKPD block, no double) -->
                                        @if (isset($skpdSpans[$itemIdx]))
                                            <td rowspan="{{ $skpdSpans[$itemIdx]['span'] }}" class="px-3 py-3 border border-gray-300 dark:border-gray-700 text-center align-middle font-semibold text-[11px] text-gray-800 dark:text-gray-200 bg-gray-50/60 dark:bg-[#161616] whitespace-normal">
                                                <span class="inline-block px-2.5 py-1 rounded bg-white dark:bg-[#242424] border border-gray-300 dark:border-gray-700/80 shadow-sm">
                                                    {{ $skpdSpans[$itemIdx]['name'] }}
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="12" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-sm font-medium">Tidak ada sub kegiatan yang cocok dengan filter tahun {{ $tahun }} atau pencarian.</span>
                                            <button
                                                wire:click="resetFilter"
                                                class="mt-2 text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline cursor-pointer">
                                                Reset Filter Pencarian
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Helper Hint when no program is clicked yet -->
            <div class="text-center py-6 px-4 bg-[#0a2729]/60 rounded-xl border border-teal-900/40 max-w-xl mx-auto mb-10">
                <svg class="w-6 h-6 text-teal-400 mx-auto mb-2 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
                <p class="text-xs text-teal-200 font-medium">
                    Silakan klik salah satu kartu program prioritas di atas untuk membuka tabel sub kegiatan, target, dan realisasinya per triwulan.
                </p>
            </div>
        @endif
    </div>
</div>
