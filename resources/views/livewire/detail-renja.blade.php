<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Breadcrumbs -->
    <nav class="flex text-sm text-[#5b403d] dark:text-[#a0a0a0] font-medium mb-6 transition-colors">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center hover:text-[#004f58] dark:hover:text-white transition-colors">
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <span class="mx-2">/</span>
                    <a href="{{ route('renja.index') }}" class="hover:text-[#004f58] dark:hover:text-white transition-colors">Realisasi Keuangan (Renja)</a>
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
            <div class="w-12 h-12 rounded-lg bg-[#dbf9ff] dark:bg-[#003840] text-[#004f58] dark:text-[#4fd8e6] flex items-center justify-center shrink-0 transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-[#191c1e] dark:text-white tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">{{ $skpd->nama_skpd ?? 'SKPD' }}</h2>
                <p class="text-[#5b403d] dark:text-[#a0a0a0] mt-1 transition-colors">Realisasi Keuangan (Renja) - Tahun {{ $tahun }}</p>
            </div>
        </div>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- Target Card -->
        <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <span class="font-semibold text-sm text-[#5b403d] dark:text-[#a0a0a0]" style="font-family: 'Manrope', sans-serif;">Total Pagu / Target</span>
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
            <div class="absolute inset-0 bg-[#004f58]/5 dark:bg-[#4fd8e6]/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-bold text-sm text-[#004f58] dark:text-[#4fd8e6] transition-colors" style="font-family: 'Manrope', sans-serif;">Total Realisasi Keuangan</span>
                </div>
                <div>
                    <div class="text-[28px] font-bold text-[#191c1e] dark:text-white mb-2 tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Rp {{ number_format($detail['realisasi'], 0, ',', '.') }}
                    </div>
                    <div class="mt-3">
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-[#5b403d] dark:text-[#a0a0a0]">Persentase Realisasi</span>
                            <span class="text-[#004f58] dark:text-[#4fd8e6] transition-colors">{{ $detail['persentase'] }}%</span>
                        </div>
                        <div class="w-full bg-[#e6e8eb] dark:bg-[#2a2a2a] rounded-full h-2 transition-colors">
                            <div class="bg-[#004f58] dark:bg-[#4fd8e6] h-2 rounded-full transition-colors" style="width: {{ min($detail['persentase'], 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sisa Target Card -->
        <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <span class="font-semibold text-sm text-[#5b403d] dark:text-[#a0a0a0]" style="font-family: 'Manrope', sans-serif;">Total Sisa Pagu</span>
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

        <!-- Per Sub Kegiatan Bar Chart -->
        <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-6 shadow-sm lg:col-span-2 transition-colors">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-[#191c1e] dark:text-white transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">Grafik Realisasi Keuangan per Sub Kegiatan</h3>
            </div>
            <div id="renja-chart" style="min-height: 350px;"></div>
        </div>
        
    </div>

    <!-- Fisik Chart Section -->
    <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-6 shadow-sm mb-8 transition-colors" wire:ignore>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[#191c1e] dark:text-white transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">Grafik Realisasi Fisik per Sub Kegiatan (%)</h3>
        </div>
        <div id="fisik-chart" style="min-height: 350px;"></div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl shadow-sm overflow-hidden flex flex-col transition-colors">
        <div class="p-5 border-b border-[#e0e3e6] dark:border-[#1f1f1f] bg-[#f7f9fc] dark:bg-[#1a1a1a] flex justify-between items-center transition-colors">
            <div>
                <h3 class="text-lg font-bold text-[#191c1e] dark:text-white transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">Rincian Realisasi Fisik dan Keuangan</h3>
                <p class="text-sm text-[#5b403d] dark:text-[#a0a0a0] mt-1 transition-colors">Detail rincian per program, kegiatan, dan sub kegiatan.</p>
            </div>
        </div>
        <div class="overflow-x-auto p-4">
            <style>
                .report-table {
                    border-collapse: collapse !important;
                    width: 100%;
                    font-size: 11px;
                    color: #1e293b;
                }
                .dark .report-table {
                    color: #e0e0e0;
                }
                .report-table th,
                .report-table td {
                    border: 1px solid #cbd5e1 !important;
                    padding: 6px 8px !important;
                    line-height: 1.2;
                }
                .dark .report-table th,
                .dark .report-table td {
                    border-color: #2a2a2a !important;
                }
                .report-table thead th {
                    background-color: #004f58 !important;
                    color: white !important;
                    text-align: center;
                    vertical-align: middle;
                    text-transform: uppercase;
                }
                .dark .report-table thead th {
                    background-color: #003840 !important;
                }
                .row-program {
                    background-color: #dbf9ff !important;
                    font-weight: bold;
                }
                .dark .row-program {
                    background-color: rgba(0, 79, 88, 0.3) !important;
                }
                .row-kegiatan {
                    background-color: #f1f5f9 !important;
                    font-weight: 600;
                }
                .dark .row-kegiatan {
                    background-color: rgba(255, 255, 255, 0.05) !important;
                }
                .row-sub {
                    background-color: #ffffff;
                }
                .dark .row-sub {
                    background-color: transparent;
                }
                .negative-dev {
                    color: #dc2626;
                    font-weight: bold;
                }
                .dark .negative-dev {
                    color: #ffb4ab;
                }
            </style>
            <table class="report-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 40px;">No</th>
                        <th rowspan="2">Program / Kegiatan / Sub Kegiatan</th>
                        <th rowspan="2" style="width: 100px;">Pagu (Rp)</th>
                        <th colspan="3">Fisik (%)</th>
                        <th colspan="5">Keuangan</th>
                    </tr>
                    <tr>
                        <th style="width: 50px;">Target</th>
                        <th style="width: 50px;">Real.</th>
                        <th style="width: 50px;">Dev.</th>
                        <th style="width: 90px;">Target (Rp)</th>
                        <th style="width: 45px;">%</th>
                        <th style="width: 90px;">Real. (Rp)</th>
                        <th style="width: 45px;">%</th>
                        <th style="width: 50px;">Dev. (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $noProg = 1;
                    @endphp

                    @forelse($tableData as $program)
                        <tr class="row-program">
                            <td class="text-center">{{ $noProg++ }}</td>
                            <td>{{ $program['nama'] }}</td>
                            <td class="text-right">{{ number_format($program['pagu'], 0, ',', '.') }}</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-right">{{ number_format($program['target_keuangan'], 0, ',', '.') }}</td>
                            <td class="text-center">
                                {{ $program['pagu'] > 0 ? number_format(($program['target_keuangan'] / $program['pagu']) * 100, 2) : 0 }}%
                            </td>
                            <td class="text-right">{{ number_format($program['realisasi_keuangan'], 0, ',', '.') }}</td>
                            <td class="text-center">
                                {{ $program['pagu'] > 0 ? number_format(($program['realisasi_keuangan'] / $program['pagu']) * 100, 2) : 0 }}%
                            </td>
                            <td class="text-center">-</td>
                        </tr>

                        @php $noKeg = 1; @endphp
                        @foreach ($program['kegiatans'] as $kegiatan)
                            <tr class="row-kegiatan">
                                <td class="text-center">{{ $noProg - 1 }}.{{ $noKeg++ }}</td>
                                <td style="padding-left: 15px !important;">{{ $kegiatan['nama'] }}</td>
                                <td class="text-right">{{ number_format($kegiatan['pagu'], 0, ',', '.') }}</td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                                <td class="text-right">{{ number_format($kegiatan['target_keuangan'], 0, ',', '.') }}</td>
                                <td class="text-center">
                                    {{ $kegiatan['pagu'] > 0 ? number_format(($kegiatan['target_keuangan'] / $kegiatan['pagu']) * 100, 2) : 0 }}%
                                </td>
                                <td class="text-right">{{ number_format($kegiatan['realisasi_keuangan'], 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    {{ $kegiatan['pagu'] > 0 ? number_format(($kegiatan['realisasi_keuangan'] / $kegiatan['pagu']) * 100, 2) : 0 }}%
                                </td>
                                <td class="text-center">-</td>
                            </tr>

                            @foreach ($kegiatan['sub_kegiatans'] as $sub)
                                <tr class="row-sub hover:bg-[#f2f4f7] dark:hover:bg-[#1a1a1a] transition-colors">
                                    <td></td>
                                    <td style="padding-left: 30px !important; color: inherit;" class="dark:text-[#a0a0a0]">{{ $sub['nama'] }}</td>
                                    <td class="text-right">{{ number_format($sub['pagu'], 0, ',', '.') }}</td>
                                    <td class="text-center">{{ number_format($sub['target_fisik'], 2) }}</td>
                                    <td class="text-center">{{ number_format($sub['realisasi_fisik'], 2) }}</td>
                                    @php $df = $sub['realisasi_fisik'] - $sub['target_fisik']; @endphp
                                    <td class="text-center {{ $df < 0 ? 'negative-dev' : '' }}">
                                        {{ number_format($df, 2) }}</td>
                                    <td class="text-right">{{ number_format($sub['target_keuangan'], 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        {{ $sub['pagu'] > 0 ? number_format(($sub['target_keuangan'] / $sub['pagu']) * 100, 2) : 0 }}%
                                    </td>
                                    <td class="text-right font-medium text-[#004f58] dark:text-[#4fd8e6] transition-colors">{{ number_format($sub['realisasi_keuangan'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-center font-bold">
                                        {{ $sub['pagu'] > 0 ? number_format(($sub['realisasi_keuangan'] / $sub['pagu']) * 100, 2) : 0 }}%
                                    </td>
                                    @php
                                        $tp = $sub['pagu'] > 0 ? ($sub['target_keuangan'] / $sub['pagu']) * 100 : 0;
                                        $rp = $sub['pagu'] > 0 ? ($sub['realisasi_keuangan'] / $sub['pagu']) * 100 : 0;
                                        $dk = $rp - $tp;
                                    @endphp
                                    <td class="text-center {{ $dk < 0 ? 'negative-dev' : '' }}">
                                        {{ number_format($dk, 2) }}%</td>
                                </tr>
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-8 text-[#5b403d] dark:text-[#a0a0a0] italic transition-colors">Data Renja belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
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
                labels: ['Realisasi', 'Sisa Pagu'],
                colors: ['#004f58', '#e0e3e6'],
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
                                    label: 'Total Pagu',
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
            document.querySelector("#overall-donut-chart").innerHTML = "<p class='text-center text-[#5b403d] mt-10'>Data pagu tidak tersedia</p>";
        }


        // Per Sub Kegiatan Bar Chart
        const kegiatanData = @json($kegiatanData);
        
        if (kegiatanData.length === 0) {
            document.querySelector("#renja-chart").innerHTML = "<p class='text-center text-[#5b403d] mt-10'>Tidak ada data untuk ditampilkan</p>";
            return;
        }

        const categories = kegiatanData.map(item => item.nama_rekening.length > 25 ? item.nama_rekening.substring(0, 25) + '...' : item.nama_rekening);
        const targetData = kegiatanData.map(item => item.target);
        const realisasiData = kegiatanData.map(item => item.realisasi);

        var options = {
            series: [{
                name: 'Pagu (Rp)',
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
            colors: ['#005faf', '#004f58'],
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

        var chart = new ApexCharts(document.querySelector("#renja-chart"), options);
        chart.render();

        // Fisik Bar Chart
        const targetFisikData = kegiatanData.map(item => item.target_fisik);
        const realisasiFisikData = kegiatanData.map(item => item.realisasi_fisik);

        var fisikOptions = {
            series: [{
                name: 'Target Fisik (%)',
                data: targetFisikData
            }, {
                name: 'Realisasi Fisik (%)',
                data: realisasiFisikData
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
                max: 100,
                labels: {
                    formatter: function (val) {
                        return val.toFixed(0) + '%';
                    },
                    style: {
                        colors: '#5b403d'
                    }
                }
            },
            colors: ['#001c3a', '#005faf'],
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toFixed(2) + "%"
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        };

        var fisikChart = new ApexCharts(document.querySelector("#fisik-chart"), fisikOptions);
        fisikChart.render();
    });
</script>
