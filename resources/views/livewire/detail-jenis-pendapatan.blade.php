<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Breadcrumbs -->
    <nav class="flex text-sm text-[#5b403d] dark:text-[#a0a0a0] font-medium mb-6 transition-colors">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center hover:text-[#af101a] dark:hover:text-[#ffb4ab] transition-colors">
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2">/</span>
                    <a href="{{ route('pendapatan.index') }}" class="hover:text-[#af101a] dark:hover:text-[#ffb4ab] transition-colors">Pendapatan</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <span class="mx-2">/</span>
                    <span class="text-[#191c1e] dark:text-[#e0e0e0] font-semibold transition-colors">Detail {{ $title }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-[#d4e3ff] dark:bg-[#005faf]/30 text-[#001c3a] dark:text-[#7bb4ff] flex items-center justify-center shrink-0 transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-[#191c1e] dark:text-white tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">{{ $title }}</h2>
                <p class="text-[#5b403d] dark:text-[#a0a0a0] mt-1 transition-colors">Rincian pendapatan berdasarkan kode rekening - Tahun {{ $tahun }}</p>
            </div>
        </div>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- Target Card -->
        <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <span class="font-semibold text-sm text-[#005faf] dark:text-[#7bb4ff] transition-colors" style="font-family: 'Manrope', sans-serif;">Total Target</span>
                <span class="bg-[#eceef1] dark:bg-[#2a2a2a] text-[#5b403d] dark:text-[#a0a0a0] text-xs font-bold px-2 py-1 rounded transition-colors">{{ $tahun }}</span>
            </div>
            <div>
                <div class="text-[28px] font-bold text-[#191c1e] dark:text-white mb-2 tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    Rp {{ number_format($summary['target'], 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Realisasi Card -->
        <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute inset-0 bg-[#af101a]/5 dark:bg-[#ffb4ab]/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-bold text-sm text-[#af101a] dark:text-[#ffb4ab] transition-colors" style="font-family: 'Manrope', sans-serif;">Total Realisasi</span>
                </div>
                <div>
                    <div class="text-[28px] font-bold text-[#191c1e] dark:text-white mb-2 tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Rp {{ number_format($summary['realisasi'], 0, ',', '.') }}
                    </div>
                    <div class="mt-3">
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-[#5b403d] dark:text-[#a0a0a0] transition-colors">Persentase Realisasi</span>
                            <span class="text-[#af101a] dark:text-[#ffb4ab] transition-colors">{{ $summary['persentase'] }}%</span>
                        </div>
                        <div class="w-full bg-[#e6e8eb] dark:bg-[#2a2a2a] rounded-full h-2 transition-colors">
                            <div class="bg-[#af101a] dark:bg-[#ffb4ab] h-2 rounded-full transition-colors" style="width: {{ min($summary['persentase'], 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sisa Target Card -->
        <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <span class="font-semibold text-sm text-[#5b403d] dark:text-[#a0a0a0] transition-colors" style="font-family: 'Manrope', sans-serif;">Total Sisa Target</span>
            </div>
            <div>
                <div class="text-[28px] font-bold text-[#191c1e] dark:text-white mb-2 tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    Rp {{ number_format($summary['sisa'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl shadow-sm overflow-hidden flex flex-col transition-colors">
        <div class="p-5 border-b border-[#e0e3e6] dark:border-[#1f1f1f] bg-[#f7f9fc] dark:bg-[#1a1a1a] flex justify-between items-center transition-colors">
            <div>
                <h3 class="text-lg font-bold text-[#191c1e] dark:text-white transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">Rincian Rekening</h3>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#005faf] dark:bg-[#003c73] text-white text-sm uppercase tracking-wider transition-colors" style="font-family: 'Manrope', sans-serif;">
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Kode Rekening</th>
                        <th class="px-6 py-4 font-semibold min-w-[300px]">Uraian Rekening</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Target (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Realisasi (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-center whitespace-nowrap">%</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-[#191c1e] dark:text-[#e0e0e0] divide-y divide-[#e0e3e6] dark:divide-[#1f1f1f] transition-colors">
                    @forelse($rekeningData as $index => $item)
                    <tr class="hover:bg-[#f2f4f7] dark:hover:bg-[#1a1a1a] transition-colors {{ $index % 2 == 0 ? '' : 'bg-[#005faf]/5 dark:bg-[#ffffff]/5' }}">
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-[#191c1e] dark:text-white">{{ $item['kode'] ?? '-' }}</td>
                        <td class="px-6 py-4 font-medium text-[#005faf] dark:text-[#7bb4ff]">{{ $item['uraian'] ?? '-' }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">{{ number_format($item['target'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap font-medium">{{ number_format($item['realisasi'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $item['persentase'] < 50 ? 'bg-[#ffdad6] dark:bg-[#93000a]/30 text-[#93000a] dark:text-[#ffb4ab]' : 'bg-[#dbf9ff] dark:bg-[#004f58]/30 text-[#004f58] dark:text-[#4fd8e6]' }}">
                                {{ $item['persentase'] }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-[#5b403d] dark:text-[#a0a0a0] transition-colors">Belum ada rincian data rekening untuk kategori ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($rekeningData) > 0)
                <tfoot class="bg-[#f7f9fc] dark:bg-[#1a1a1a] border-t-2 border-[#e0e3e6] dark:border-[#1f1f1f] text-sm transition-colors">
                    <tr>
                        <td class="px-6 py-4 text-right font-bold text-[#191c1e] dark:text-white transition-colors" colspan="2">TOTAL</td>
                        <td class="px-6 py-4 text-right font-bold text-[#191c1e] dark:text-white whitespace-nowrap transition-colors">{{ number_format($summary['target'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-bold text-[#af101a] dark:text-[#ffb4ab] whitespace-nowrap transition-colors">{{ number_format($summary['realisasi'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center font-bold text-[#af101a] dark:text-[#ffb4ab] transition-colors">{{ $summary['persentase'] }}%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
