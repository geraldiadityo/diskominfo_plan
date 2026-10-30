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
        <div class="mt-4 md:mt-0 flex flex-wrap items-center gap-3">
            <!-- Year Filter -->
            <div class="flex items-center gap-2 bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-lg px-3 py-1.5 shadow-sm">
                <label for="index-tahun" class="text-xs font-bold uppercase tracking-wider text-[#5b403d] dark:text-[#a0a0a0] flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-4 h-4 text-[#af101a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Tahun Realisasi:
                </label>
                <select id="index-tahun" wire:model.live="tahun" class="text-sm font-semibold rounded border-none bg-transparent text-[#191c1e] dark:text-white focus:outline-none focus:ring-0 cursor-pointer">
                    @foreach($availableYears as $yearOption)
                        <option value="{{ $yearOption }}" {{ $tahun == $yearOption ? 'selected' : '' }} class="bg-white dark:bg-[#1e1e1e] text-[#191c1e] dark:text-white">
                            {{ $yearOption }}
                        </option>
                    @endforeach
                </select>
            </div>

            <a href="{{ route('public.pendapatan', ['tahun' => $tahun]) }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-[#af101a] hover:bg-[#93000a] text-white rounded-lg font-semibold text-sm transition-colors shadow-sm" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                Pendapatan Asli Daerah
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
                        <p class="text-xl font-bold text-[#005faf] dark:text-[#7bb4ff]">Rp {{ number_format($totalTarget, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-[#ffdad6]/30 dark:bg-[#af101a]/20 rounded-lg border border-transparent dark:border-[#af101a]/30 transition-colors">
                        <p class="text-sm font-semibold text-[#af101a] dark:text-[#ffb4ab] mb-1">Total Realisasi</p>
                        <p class="text-xl font-bold text-[#af101a] dark:text-[#ffb4ab]">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</p>
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
            $cardColors = [
                ['border' => 'border-[#113a37]', 'iconBg' => 'bg-[#0f2924]', 'text' => 'text-[#00e1cc]'],
                ['border' => 'border-[#3a250a]', 'iconBg' => 'bg-[#291705]', 'text' => 'text-[#f59e0b]'],
                ['border' => 'border-[#0f203a]', 'iconBg' => 'bg-[#0a1629]', 'text' => 'text-[#3b82f6]'],
                ['border' => 'border-[#2c103a]', 'iconBg' => 'bg-[#1e0a29]', 'text' => 'text-[#a855f7]'],
            ];
            @endphp

            @forelse($summaryLevel2 as $idx => $item)
                @php
                    $cfg = $cardColors[$idx % count($cardColors)];
                @endphp
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
                            @if(!empty($item['is_target_zero_with_real']))
                                Realisasi (Melebihi Target)
                            @else
                                Realisasi ({{ $item['persentase'] }}%)
                            @endif
                        </p>
                        <p class="{{ $cfg['text'] }} text-lg font-bold" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            Rp {{ formatShortRp($item['realisasi']) }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center text-[#6b7280] text-sm py-8">
                    Belum ada data rekening level 2 untuk tahun {{ $tahun }}.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl shadow-sm overflow-hidden flex flex-col transition-colors">
        <div class="p-5 border-b border-[#e0e3e6] dark:border-[#1f1f1f] bg-[#f7f9fc] dark:bg-[#1a1a1a] flex justify-between items-center transition-colors">
            <h3 class="text-lg font-bold text-[#191c1e] dark:text-white" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                Rincian Pendapatan
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse" style="font-family: 'Inter', sans-serif;">
                <thead class="text-white text-center font-semibold bg-[#d32f2f]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    <tr>
                        <th rowspan="2" class="px-4 py-3 border border-[#ba1a20] w-[8%]">No / Kode</th>
                        <th rowspan="2" class="px-4 py-3 border border-[#ba1a20] w-[32%]">Jenis Pendapatan</th>
                        <th colspan="2" class="px-4 py-2 border border-[#ba1a20]">{{ $tahun }}</th>
                        <th colspan="2" class="px-4 py-2 border border-[#ba1a20]">+/-</th>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 border border-[#ba1a20] w-[15%]">Target</th>
                        <th class="px-4 py-2 border border-[#ba1a20] w-[15%]">Realisasi</th>
                        <th class="px-4 py-2 border border-[#ba1a20] w-[15%]">Rp</th>
                        <th class="px-4 py-2 border border-[#ba1a20] w-[15%]">%</th>
                    </tr>
                </thead>
                <tbody class="text-[#191c1e] dark:text-[#e0e0e0]">
                    @foreach($pendapatanData as $index => $row)
                        @php
                            if ($row['level'] == 1) {
                                $bgClass = 'bg-[#cce0ff] dark:bg-[#001c3a]';
                            } elseif ($row['level'] == 2) {
                                $bgClass = 'bg-[#e6f0ff] dark:bg-[#0a1f3a]';
                            } elseif ($row['level'] == 3) {
                                $bgClass = 'bg-[#f2f7ff] dark:bg-[#0f1f2e]';
                            } else {
                                $bgClass = $index % 2 === 0 ? 'bg-[#f7f9fc] dark:bg-[#121212]' : 'bg-white dark:bg-[#1a1a1a]';
                            }
                            $paddingLeft = max(0, ($row['level'] - 1) * 20);
                            $textClass   = $row['level'] <= 3 ? 'font-bold' : '';
                        @endphp
                        <tr class="{{ $bgClass }} hover:bg-[#e6e8eb] dark:hover:bg-[#1f1f1f] transition-colors">
                            <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] text-center font-semibold">{{ $row['kode'] }}</td>
                            <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] {{ $textClass }}" style="padding-left: {{ 16 + $paddingLeft }}px;">
                                {{ $row['uraian'] }}
                            </td>
                            <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] text-right font-mono">
                                {{ number_format($row['target'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] text-right font-mono font-medium">
                                {{ number_format($row['realisasi'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] text-right font-mono font-medium">
                                @if(($row['target'] == 0 && $row['realisasi'] > 0) || !empty($row['is_target_zero_with_real']))
                                    <span class="text-emerald-600 dark:text-emerald-400">
                                        +{{ number_format($row['realisasi'], 2, ',', '.') }}
                                    </span>
                                @elseif($row['target'] > 0 && $row['realisasi'] > $row['target'])
                                    <span class="text-emerald-600 dark:text-emerald-400">
                                        +{{ number_format($row['realisasi'] - $row['target'], 2, ',', '.') }}
                                    </span>
                                @elseif($row['target'] > 0 && $row['selisih'] > 0)
                                    <span class="text-[#ba1a1a] dark:text-[#ffb4ab]">
                                        -{{ number_format($row['selisih'], 2, ',', '.') }}
                                    </span>
                                @else
                                    {{ number_format($row['selisih'], 2, ',', '.') }}
                                @endif
                            </td>
                            <td class="px-4 py-2 border border-[#e0e3e6] dark:border-[#2a2a2a] text-center font-semibold">
                                @if(($row['target'] == 0 && $row['realisasi'] > 0) || !empty($row['is_target_zero_with_real']))
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                        Melebihi Target
                                    </span>
                                @elseif($row['target'] > 0 && $row['realisasi'] >= $row['target'])
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                        {{ number_format($row['persentase'], 2, ',', '.') }}%
                                    </span>
                                @else
                                    {{ number_format($row['persentase'] ?? 0, 2, ',', '.') }}%
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if(count($pendapatanData) === 0)
                <div class="p-8 text-center text-[#5b403d] dark:text-[#a0a0a0]">
                    Tidak ada data pendapatan untuk tahun {{ $tahun }}.
                </div>
            @endif
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

<script>
    (() => {
        let chart = null;

        function renderOrUpdateChart(totalTarget, totalRealisasi) {
            const el = document.querySelector("#overall-chart");
            if (!el) return;

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

            if (chart) {
                try {
                    chart.destroy();
                } catch (e) {}
                chart = null;
            }
            el.innerHTML = "";
            chart = new ApexCharts(el, options);
            chart.render();
        }

        function initChart() {
            renderOrUpdateChart({{ $totalTarget }}, {{ $totalRealisasi }});
        }

        // Jalankan saat load awal atau via Livewire navigate
        document.addEventListener('livewire:navigated', initChart);
        document.addEventListener('livewire:initialized', initChart);

        // Jika script dijalankan ulang setelah navigasi
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(initChart, 50);
        }

        Livewire.on('chart-data-updated', (data) => {
            const payload = Array.isArray(data) ? data[0] : data;
            renderOrUpdateChart(payload.target, payload.realisasi);
        });
    })();
</script>
</div>
