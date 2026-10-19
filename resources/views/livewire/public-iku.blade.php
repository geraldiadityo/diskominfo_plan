<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 transition-colors duration-200">
    @php
        $runningNo = 1;
        $totalColumns = 3 + (count($years) * 2) + 1;
    @endphp

    <!-- Breadcrumb & Header Box -->
    <div class="bg-white dark:bg-[#121212] rounded-xl shadow-sm border border-[#e0e3e6] dark:border-[#1f1f1f] overflow-hidden mb-6">
        <!-- Breadcrumb -->
        <div class="px-6 py-3 bg-[#f7f9fc] dark:bg-[#181818] border-b border-[#e0e3e6] dark:border-[#1f1f1f]">
            <nav class="flex text-xs font-medium text-gray-500 dark:text-gray-400">
                <a href="{{ route('dashboard') }}" class="hover:text-[#af101a] transition-colors">Home</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-700 dark:text-gray-300">Kinerja</span>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-[#af101a] font-semibold">Indikator Kinerja Utama (IKU)</span>
            </nav>
        </div>

        <!-- Banner Title -->
        <div class="px-6 py-5 text-center">
            <span class="inline-block px-3 py-1 text-xs font-semibold uppercase tracking-wider text-[#af101a] bg-red-50 dark:bg-red-950/30 rounded-full mb-2">
                Evaluasi & Pelaporan
            </span>
            <h1 class="text-xl sm:text-2xl font-extrabold uppercase text-[#1a3c6b] dark:text-blue-400 tracking-tight">
                Indikator Kinerja Utama (IKU) Pemerintah Daerah
            </h1>
        </div>

        <!-- Filter Panel -->
        <div class="px-6 py-4 bg-[#f1f5f9] dark:bg-[#1a1a1a] border-t border-[#e0e3e6] dark:border-[#1f1f1f]">
            <form method="GET" action="{{ route('iku.index') }}" wire:submit.prevent class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                <!-- Period 6 Years Filter -->
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mr-1 flex items-center gap-1">
                        <svg class="w-4 h-4 text-[#af101a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Periode (6 Tahun):
                    </span>
                    <select
                        wire:model.live="filterStartYear"
                        name="tahun_awal"
                        onchange="this.form.submit()"
                        class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#242424] text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 shadow-sm cursor-pointer">
                        @foreach ($availablePeriods as $sYear => $label)
                            <option value="{{ $sYear }}" {{ $filterStartYear == $sYear ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search & SKPD Filter -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Search Input -->
                    <div class="relative min-w-[200px]">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari indikator / SKPD..."
                            class="w-full text-xs pl-8 pr-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#242424] text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- SKPD Filter -->
                    <select
                        wire:model.live="selectedSkpd"
                        name="skpd"
                        onchange="this.form.submit()"
                        class="text-xs px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#242424] text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 max-w-[260px] cursor-pointer">
                        <option value="">Semua SKPD</option>
                        @foreach ($skpdList as $skpdName)
                            <option value="{{ $skpdName }}" {{ $selectedSkpd === $skpdName ? 'selected' : '' }}>
                                {{ $skpdName }}
                            </option>
                        @endforeach
                    </select>

                    <noscript>
                        <button type="submit" class="px-3 py-1.5 text-xs bg-[#1a3c6b] text-white rounded-lg font-semibold">
                            Terapkan
                        </button>
                    </noscript>

                    <!-- Reset Button -->
                    @if (!empty($search) || !empty($selectedSkpd) || $filterStartYear !== 2025)
                        <a
                            href="{{ route('iku.index') }}"
                            wire:click.prevent="resetFilter"
                            class="px-2.5 py-1.5 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors font-medium border border-red-200 dark:border-red-900/50 cursor-pointer">
                            Reset
                        </a>
                    @endif
                </div>

            </form>

            <!-- Notes Bar -->
            <div class="mt-3 pt-2 border-t border-gray-200 dark:border-gray-800 flex flex-wrap items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                <div>
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500 mr-1.5 align-middle"></span>
                    Menampilkan target dan realisasi per tahun untuk setiap Indikator Kinerja Utama (IKU).
                </div>
                <div>
                    Menampilkan data per SKPD Penanggung Jawab.
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="bg-white dark:bg-[#121212] rounded-xl shadow-sm border border-[#e0e3e6] dark:border-[#1f1f1f] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse border border-gray-300 dark:border-gray-700">

                <!-- Table Header -->
                <thead>
                    <!-- Row 1: Main Column Headers -->
                    <tr class="bg-[#1a3c6b] text-white">
                        <th rowspan="2" class="px-3 py-3 border border-blue-900 text-center font-bold align-middle w-12 uppercase tracking-wider">
                            No.
                        </th>
                        <th rowspan="2" class="px-4 py-3 border border-blue-900 text-center font-bold align-middle min-w-[280px] uppercase tracking-wider">
                            Indikator
                        </th>
                        <th rowspan="2" class="px-3 py-3 border border-blue-900 text-center font-bold align-middle w-24 uppercase tracking-wider">
                            Satuan
                        </th>
                        @foreach ($years as $year)
                            <th colspan="2" class="px-3 py-2 border border-blue-900 text-center font-bold uppercase tracking-wider">
                                {{ $year }}
                            </th>
                        @endforeach
                        <th rowspan="2" class="px-4 py-3 border border-blue-900 text-center font-bold align-middle min-w-[220px] uppercase tracking-wider">
                            Sumber Data / SKPD Penanggung Jawab
                        </th>
                    </tr>

                    <!-- Row 2: Sub-headers for Years (Target & Realisasi) -->
                    <tr class="bg-[#214a82] text-white">
                        @foreach ($years as $year)
                            <th class="px-2 py-1.5 border border-blue-900 text-center font-semibold text-[11px] w-20">
                                Target
                            </th>
                            <th class="px-2 py-1.5 border border-blue-900 text-center font-semibold text-[11px] w-20 bg-[#1c3e6d]">
                                Realisasi
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($kategoriData as $kategori)
                        @foreach ($kategori['indikators'] as $ind)
                            <tr class="hover:bg-blue-50/40 dark:hover:bg-[#1a2333]/50 transition-colors {{ $loop->even ? 'bg-gray-50/50 dark:bg-[#161616]' : 'bg-white dark:bg-[#121212]' }}">
                                <!-- No -->
                                <td class="px-3 py-2 border border-gray-300 dark:border-gray-700 text-center font-medium text-gray-700 dark:text-gray-300">
                                    {{ $runningNo++ }}
                                </td>

                                <!-- Indikator -->
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 font-medium text-gray-900 dark:text-gray-100 whitespace-normal">
                                    {{ $ind['nama_indikator'] }}
                                </td>

                                <!-- Satuan -->
                                <td class="px-3 py-2 border border-gray-300 dark:border-gray-700 text-center text-gray-600 dark:text-gray-400">
                                    {{ $ind['satuan'] }}
                                </td>

                                <!-- Target & Realisasi per Tahun (Tanpa Baseline) -->
                                @foreach ($years as $year)
                                    <!-- Target -->
                                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-700 text-center text-gray-800 dark:text-gray-200">
                                        {{ isset($ind['years'][$year]['target']) && $ind['years'][$year]['target'] !== null && $ind['years'][$year]['target'] !== ''
                                            ? (is_numeric($ind['years'][$year]['target']) ? floatval($ind['years'][$year]['target']) : $ind['years'][$year]['target'])
                                            : '-' }}
                                    </td>
                                    <!-- Realisasi -->
                                    <td class="px-2 py-2 border border-gray-300 dark:border-gray-700 text-center font-semibold text-blue-700 dark:text-blue-400 bg-blue-50/30 dark:bg-blue-950/20">
                                        {{ isset($ind['years'][$year]['realisasi']) && $ind['years'][$year]['realisasi'] !== null && $ind['years'][$year]['realisasi'] !== ''
                                            ? (is_numeric($ind['years'][$year]['realisasi']) ? floatval($ind['years'][$year]['realisasi']) : $ind['years'][$year]['realisasi'])
                                            : '-' }}
                                    </td>
                                @endforeach

                                <!-- Sumber Data / SKPD Penanggung Jawab -->
                                <td class="px-4 py-2 border border-gray-300 dark:border-gray-700 text-center text-gray-700 dark:text-gray-300 whitespace-normal">
                                    {{ $ind['skpd'] }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="{{ $totalColumns }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">Tidak ada data indikator kinerja utama yang ditemukan.</span>
                                    <a
                                        href="{{ route('iku.index') }}"
                                        wire:click.prevent="resetFilter"
                                        class="mt-2 text-xs font-semibold text-blue-600 hover:underline cursor-pointer">
                                        Reset Filter Pencarian
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>
</div>
