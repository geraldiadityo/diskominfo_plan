<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Breadcrumbs -->
    <nav class="flex text-sm text-[#5b403d] dark:text-[#a0a0a0] font-medium mb-6 transition-colors">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center hover:text-[#af101a] dark:hover:text-white transition-colors">
                    Home
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <span class="mx-2">/</span>
                    <span class="text-[#191c1e] dark:text-[#e0e0e0] font-semibold">Pendapatan Daerah</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-[#d4e3ff] dark:bg-[#003840] text-[#001c3a] dark:text-[#4fd8e6] flex items-center justify-center shrink-0 transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-[#191c1e] dark:text-white tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">Rekapitulasi Pendapatan Daerah</h2>
                <p class="text-[#5b403d] dark:text-[#a0a0a0] mt-1 transition-colors">Daftar target dan realisasi pendapatan per Organisasi Perangkat Daerah (OPD) - Tahun {{ $tahun }}</p>
            </div>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('public.pendapatan') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-[#af101a] hover:bg-[#93000a] text-white rounded-lg font-semibold text-sm transition-colors shadow-sm" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                Pendapatan Keseluruhan
            </a>
        </div>
    </div>

    <!-- Summary Chart & Cards Section -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-stretch mb-8">
        
        <!-- Left Side: Overall Chart -->
        <div class="xl:col-span-7 bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-6 shadow-sm transition-colors">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-[#191c1e] dark:text-white" style="font-family: 'Plus Jakarta Sans', sans-serif;">Grafik Pendapatan Keseluruhan</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <div class="md:col-span-1 flex flex-col gap-4">
                    <div class="p-4 bg-[#f2f4f7] dark:bg-[#1a1a1a] rounded-lg border border-transparent dark:border-[#2a2a2a] transition-colors">
                        <p class="text-sm font-semibold text-[#5b403d] dark:text-[#a0a0a0] mb-1">Total Target</p>
                        <p class="text-xl font-bold text-[#005faf] dark:text-[#7bb4ff]">Rp {{ number_format($skpds->sum('target'), 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-[#ffdad6]/30 dark:bg-[#af101a]/20 rounded-lg border border-transparent dark:border-[#af101a]/30 transition-colors">
                        <p class="text-sm font-semibold text-[#af101a] dark:text-[#ffb4ab] mb-1">Total Realisasi</p>
                        <p class="text-xl font-bold text-[#af101a] dark:text-[#ffb4ab]">Rp {{ number_format($skpds->sum('realisasi'), 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="md:col-span-2" wire:ignore>
                    <div id="overall-chart" style="min-height: 250px;"></div>
                </div>
            </div>
        </div>

        <!-- Right Side: Jenis Rekening Dark Cards -->
        <div class="xl:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @php
            if (!function_exists('formatShortRp')) {
                function formatShortRp($num) {
                    if ($num >= 1000000000000) return round($num / 1000000000000, 1) . ' T';
                    if ($num >= 1000000000) return round($num / 1000000000, 1) . ' M';
                    if ($num >= 1000000) return round($num / 1000000, 1) . ' Jt';
                    return number_format($num);
                }
            }
            $colorConfig = [
                'pajak' => ['border' => 'border-[#113a37]', 'iconBg' => 'bg-[#0f2924]', 'text' => 'text-[#00e1cc]'],
                'retribusi' => ['border' => 'border-[#3a250a]', 'iconBg' => 'bg-[#291705]', 'text' => 'text-[#f59e0b]'],
                'kekayaan' => ['border' => 'border-[#0f203a]', 'iconBg' => 'bg-[#0a1629]', 'text' => 'text-[#3b82f6]'],
                'lain_lain' => ['border' => 'border-[#2c103a]', 'iconBg' => 'bg-[#1e0a29]', 'text' => 'text-[#a855f7]'],
            ];
            @endphp

            @foreach(['pajak', 'retribusi', 'kekayaan', 'lain_lain'] as $key)
                @if(isset($summaryByJenis[$key]))
                    @php $item = $summaryByJenis[$key]; $cfg = $colorConfig[$key]; @endphp
                    <div class="rounded-xl border {{ $cfg['border'] }} p-5 flex flex-col justify-between relative overflow-hidden" 
                         style="background-color: #0a0a0a; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 16px 16px;">
                        
                        <!-- Icon & Title -->
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ $cfg['iconBg'] }}">
                                <svg class="w-5 h-5 {{ $cfg['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="7" stroke-width="2"/>
                                    <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                </svg>
                            </div>
                            <h4 class="text-white font-semibold text-[13px] leading-tight" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                {{ $item['nama'] }}
                            </h4>
                        </div>
                        
                        <!-- Target -->
                        <div class="mb-4">
                            <p class="text-[#6b7280] text-[10px] font-bold uppercase tracking-wider mb-0.5">Target</p>
                            <p class="text-white text-lg font-bold" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                Rp {{ formatShortRp($item['target']) }}
                            </p>
                        </div>

                        <!-- Realisasi -->
                        <div class="mb-4">
                            <p class="{{ $cfg['text'] }} text-[10px] font-bold uppercase tracking-wider mb-0.5">
                                Realisasi ({{ $item['persentase'] }}%)
                            </p>
                            <p class="{{ $cfg['text'] }} text-lg font-bold" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                Rp {{ formatShortRp($item['realisasi']) }}
                            </p>
                        </div>

                        <!-- Link -->
                        <div class="text-right mt-auto">
                            <a href="{{ route('pendapatan.jenis', ['jenis' => $key]) }}" class="{{ $cfg['text'] }} text-[10px] font-bold uppercase tracking-wider hover:opacity-80 transition-opacity inline-flex items-center gap-1 focus:outline-none cursor-pointer">
                                Lihat Detail 
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19L19 5M19 5v10M19 5H9"></path></svg>
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl shadow-sm overflow-hidden flex flex-col transition-colors">
        <div class="p-5 border-b border-[#e0e3e6] dark:border-[#1f1f1f] bg-[#f7f9fc] dark:bg-[#1a1a1a] flex justify-between items-center transition-colors">
            <div>
                <h3 class="text-lg font-bold text-[#191c1e] dark:text-white" style="font-family: 'Plus Jakarta Sans', sans-serif;">Daftar OPD</h3>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#005faf] dark:bg-[#003c73] text-white text-sm uppercase tracking-wider transition-colors" style="font-family: 'Manrope', sans-serif;">
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Nama OPD / SKPD</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Target (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Realisasi (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-center whitespace-nowrap">%</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Sisa Target (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-[#191c1e] dark:text-[#e0e0e0] divide-y divide-[#e0e3e6] dark:divide-[#1f1f1f] transition-colors">
                    @forelse($skpds as $index => $skpd)
                    <tr class="hover:bg-[#f2f4f7] dark:hover:bg-[#1a1a1a] transition-colors {{ $index % 2 == 0 ? '' : 'bg-[#005faf]/5 dark:bg-[#ffffff]/5' }}">
                        <td class="px-6 py-4 font-medium text-[#191c1e] dark:text-white">{{ $skpd['nama_skpd'] }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">{{ number_format($skpd['target'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap font-medium text-[#005faf] dark:text-[#7bb4ff]">{{ number_format($skpd['realisasi'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $skpd['persentase'] < 50 ? 'bg-[#ffdad6] dark:bg-[#93000a]/30 text-[#93000a] dark:text-[#ffb4ab]' : 'bg-[#dbf9ff] dark:bg-[#004f58]/30 text-[#004f58] dark:text-[#4fd8e6]' }}">
                                {{ $skpd['persentase'] }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap font-medium text-[#ba1a1a] dark:text-[#ffb4ab]">
                            {{ number_format($skpd['sisa'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <a href="{{ route('pendapatan.detail', $skpd['id']) }}" class="inline-flex items-center gap-1 text-[#005faf] dark:text-[#7bb4ff] hover:text-[#af101a] dark:hover:text-[#ffb4ab] font-semibold transition-colors bg-white dark:bg-[#121212] hover:bg-[#ffdad6] dark:hover:bg-[#2a2a2a] border border-[#005faf] dark:border-[#7bb4ff] hover:border-[#af101a] dark:hover:border-[#ffb4ab] px-3 py-1.5 rounded-lg text-xs" style="font-family: 'Manrope', sans-serif;">
                                Lihat Detail
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-[#5b403d] dark:text-[#a0a0a0]">Belum ada data OPD yang memiliki target pendapatan tahun ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table Section End -->

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #2a2a2a;
        border-radius: 20px;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        const totalTarget = {{ $skpds->sum('target') }};
        const totalRealisasi = {{ $skpds->sum('realisasi') }};
        const sisaTarget = Math.max(totalTarget - totalRealisasi, 0);
        
        var options = {
            series: [totalRealisasi, sisaTarget],
            chart: {
                type: 'donut',
                height: 320,
                fontFamily: 'Inter, sans-serif'
            },
            labels: ['Realisasi', 'Sisa Target'],
            colors: ['#af101a', '#e0e3e6'],
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toFixed(1) + "%"
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                            },
                            value: {
                                show: true,
                                formatter: function (val) {
                                    return "Rp " + parseInt(val).toLocaleString('id-ID')
                                }
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Total Target',
                                formatter: function (w) {
                                    return "Rp " + totalTarget.toLocaleString('id-ID')
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "Rp " + parseInt(val).toLocaleString('id-ID')
                    }
                }
            },
            legend: {
                position: 'right',
                offsetY: 0,
                height: 230,
            }
        };

        var chart = new ApexCharts(document.querySelector("#overall-chart"), options);
        chart.render();
    });
</script>
</div>
