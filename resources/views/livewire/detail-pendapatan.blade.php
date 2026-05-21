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
                    <span class="text-[#191c1e] dark:text-[#e0e0e0] font-semibold transition-colors">Detail {{ $skpd->nama_skpd ?? 'SKPD' }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-[#ffdad6] dark:bg-[#93000a] text-[#af101a] dark:text-[#ffb4ab] flex items-center justify-center shrink-0 transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-[#191c1e] dark:text-white tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">{{ $skpd->nama_skpd ?? 'SKPD' }}</h2>
                <p class="text-[#5b403d] dark:text-[#a0a0a0] mt-1 transition-colors">Pendapatan Daerah (PAD) - Tahun {{ $tahun }}</p>
            </div>
        </div>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- Target Card -->
        <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <span class="font-semibold text-sm text-[#005faf] dark:text-[#7bb4ff] transition-colors" style="font-family: 'Manrope', sans-serif;">Total Target PAD</span>
                <span class="bg-[#eceef1] dark:bg-[#2a2a2a] text-[#5b403d] dark:text-[#a0a0a0] text-xs font-bold px-2 py-1 rounded transition-colors">{{ $tahun }}</span>
            </div>
            <div>
                <div class="text-[28px] font-bold text-[#191c1e] dark:text-white mb-2 tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    Rp {{ number_format($detail['target'], 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Realisasi Card -->
        <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute inset-0 bg-[#af101a]/5 dark:bg-[#ffb4ab]/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-bold text-sm text-[#af101a] dark:text-[#ffb4ab] transition-colors" style="font-family: 'Manrope', sans-serif;">Total Realisasi PAD</span>
                </div>
                <div>
                    <div class="text-[28px] font-bold text-[#191c1e] dark:text-white mb-2 tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Rp {{ number_format($detail['realisasi'], 0, ',', '.') }}
                    </div>
                    <div class="mt-3">
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-[#5b403d] dark:text-[#a0a0a0] transition-colors">Persentase Realisasi</span>
                            <span class="text-[#af101a] dark:text-[#ffb4ab] transition-colors">{{ $detail['persentase'] }}%</span>
                        </div>
                        <div class="w-full bg-[#e6e8eb] dark:bg-[#2a2a2a] rounded-full h-2 transition-colors">
                            <div class="bg-[#af101a] dark:bg-[#ffb4ab] h-2 rounded-full transition-colors" style="width: {{ min($detail['persentase'], 100) }}%"></div>
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
                    Rp {{ number_format($detail['sisa'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8" wire:ignore>
        
        <!-- Overall Donut Chart -->
        <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-6 shadow-sm lg:col-span-1 transition-colors">
            <h3 class="text-lg font-bold text-[#191c1e] dark:text-white mb-4 transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">Pencapaian Realisasi</h3>
            <div id="overall-donut-chart" style="min-height: 350px;"></div>
        </div>

        <!-- Per Rekening Bar Chart -->
        <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-6 shadow-sm lg:col-span-2 transition-colors">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-[#191c1e] dark:text-white transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">Grafik Pendapatan per Rekening</h3>
            </div>
            <div id="pendapatan-chart" style="min-height: 350px;"></div>
        </div>
        
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl shadow-sm overflow-hidden flex flex-col transition-colors">
        <div class="p-5 border-b border-[#e0e3e6] dark:border-[#1f1f1f] bg-[#f7f9fc] dark:bg-[#1a1a1a] flex justify-between items-center transition-colors">
            <div>
                <h3 class="text-lg font-bold text-[#191c1e] dark:text-white transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">Rekening Pendapatan</h3>
                <p class="text-sm text-[#5b403d] dark:text-[#a0a0a0] mt-1 transition-colors">Detail rincian pendapatan berdasarkan kode rekening.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#005faf] dark:bg-[#003c73] text-white text-sm uppercase tracking-wider transition-colors" style="font-family: 'Manrope', sans-serif;">
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Kode Rekening</th>
                        <th class="px-6 py-4 font-semibold min-w-[300px]">Nama Rekening</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Target (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Realisasi (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-center whitespace-nowrap">%</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Selisih (Rp)</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-[#191c1e] dark:text-[#e0e0e0] divide-y divide-[#e0e3e6] dark:divide-[#1f1f1f] transition-colors">
                    @forelse($rekeningData as $index => $item)
                    <tr class="hover:bg-[#f2f4f7] dark:hover:bg-[#1a1a1a] transition-colors {{ $index % 2 == 0 ? '' : 'bg-[#005faf]/5 dark:bg-[#ffffff]/5' }}">
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-[#191c1e] dark:text-white">{{ $item['kode_rekening'] }}</td>
                        <td class="px-6 py-4 font-medium text-[#005faf] dark:text-[#7bb4ff]">{{ $item['nama_rekening'] }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">{{ number_format($item['target'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap font-medium">{{ number_format($item['realisasi'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $item['persentase'] < 50 ? 'bg-[#ffdad6] dark:bg-[#93000a]/30 text-[#93000a] dark:text-[#ffb4ab]' : 'bg-[#dbf9ff] dark:bg-[#004f58]/30 text-[#004f58] dark:text-[#4fd8e6]' }}">
                                {{ $item['persentase'] }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap font-medium {{ $item['selisih'] > 0 ? 'text-[#ba1a1a] dark:text-[#ffb4ab]' : 'text-[#00606b] dark:text-[#4fd8e6]' }}">
                            {{ $item['selisih'] > 0 ? '-' : '+' }}{{ number_format(abs($item['selisih']), 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-[#5b403d] dark:text-[#a0a0a0] transition-colors">Belum ada data rekening pendapatan untuk tahun ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($rekeningData) > 0)
                <tfoot class="bg-[#f7f9fc] dark:bg-[#1a1a1a] border-t-2 border-[#e0e3e6] dark:border-[#1f1f1f] text-sm transition-colors">
                    <tr>
                        <td class="px-6 py-4 text-right font-bold text-[#191c1e] dark:text-white transition-colors" colspan="2">TOTAL</td>
                        <td class="px-6 py-4 text-right font-bold text-[#191c1e] dark:text-white whitespace-nowrap transition-colors">{{ number_format($detail['target'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-bold text-[#af101a] dark:text-[#ffb4ab] whitespace-nowrap transition-colors">{{ number_format($detail['realisasi'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center font-bold text-[#af101a] dark:text-[#ffb4ab] transition-colors">{{ $detail['persentase'] }}%</td>
                        <td class="px-6 py-4 text-right font-bold text-[#ba1a1a] dark:text-[#ffb4ab] whitespace-nowrap transition-colors">-{{ number_format($detail['sisa'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        // Overall Donut Chart
        const detailTarget = {{ $detail['target'] }};
        const detailRealisasi = {{ $detail['realisasi'] }};
        const detailSisa = Math.max(detailTarget - detailRealisasi, 0);

        if (detailTarget > 0 || detailRealisasi > 0) {
            var donutOptions = {
                series: [detailRealisasi, detailSisa],
                chart: {
                    type: 'donut',
                    height: 350,
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
                                name: { show: true },
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
                                        return "Rp " + detailTarget.toLocaleString('id-ID')
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
                    position: 'bottom'
                }
            };
            var donutChart = new ApexCharts(document.querySelector("#overall-donut-chart"), donutOptions);
            donutChart.render();
        } else {
            document.querySelector("#overall-donut-chart").innerHTML = "<p class='text-center text-[#5b403d] mt-10'>Data target tidak tersedia</p>";
        }


        // Per Rekening Bar Chart
        const rekeningData = @json($rekeningData);
        
        if (rekeningData.length === 0) {
            document.querySelector("#pendapatan-chart").innerHTML = "<p class='text-center text-[#5b403d] mt-10'>Tidak ada data untuk ditampilkan</p>";
            return;
        }

        const categories = rekeningData.map(item => item.nama_rekening.length > 25 ? item.nama_rekening.substring(0, 25) + '...' : item.nama_rekening);
        const targetData = rekeningData.map(item => item.target);
        const realisasiData = rekeningData.map(item => item.realisasi);

        var options = {
            series: [{
                name: 'Target (Rp)',
                data: targetData
            }, {
                name: 'Realisasi (Rp)',
                data: realisasiData
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                },
                fontFamily: 'Inter, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded',
                    borderRadius: 4
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: categories,
                labels: {
                    style: {
                        colors: '#5b403d'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        if(val >= 1000000000) {
                            return (val / 1000000000).toFixed(1) + ' M';
                        }
                        if(val >= 1000000) {
                            return (val / 1000000).toFixed(1) + ' Jt';
                        }
                        return val;
                    },
                    style: {
                        colors: '#5b403d'
                    }
                }
            },
            colors: ['#005faf', '#af101a'],
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "Rp " + val.toLocaleString('id-ID')
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        };

        var chart = new ApexCharts(document.querySelector("#pendapatan-chart"), options);
        chart.render();
    });
</script>
