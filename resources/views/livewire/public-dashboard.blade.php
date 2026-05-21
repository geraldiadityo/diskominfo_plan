<div>
    <div class="max-w-[1200px] mx-auto px-4 py-8 sm:px-6 lg:px-8">

        <!-- Header Section -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-[#191c1e] dark:text-white tracking-tight"
                    style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    Anggaran Pendapatan Dan Belanja Daerah
                </h1>
                <p class="mt-2 text-[#5b403d] dark:text-[#a0a0a0] text-base" style="font-family: 'Inter', sans-serif;">
                    Visualisasi data statistik kemajuan rencana kerja dan pendapatan daerah.
                </p>
            </div>

            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <div wire:loading class="text-[#af101a] text-sm font-semibold flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-[#af101a]" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Memperbarui data...
                </div>
            </div>
        </div>

        <!-- Metrics Grid -->
        {{-- <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Card 1: SKPD Count -->
            <div
                class="bg-white rounded-[8px] border border-[#e0e3e6] p-5 shadow-sm hover:shadow-md transition-shadow duration-200 group">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#5b403d]" style="font-family: 'Manrope', sans-serif;">Total
                        SKPD</h3>
                    <div
                        class="p-2 bg-[#ffdad6] rounded-full text-[#93000a] group-hover:bg-[#93000a] group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-[28px] font-bold text-[#191c1e]"
                        style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        {{ number_format($skpdCount) }}
                    </p>
                </div>
            </div>

            <!-- Card 2: Realisasi Keuangan -->
            <div
                class="bg-white rounded-[8px] border border-[#e0e3e6] p-5 shadow-sm hover:shadow-md transition-shadow duration-200 group">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#5b403d]" style="font-family: 'Manrope', sans-serif;">
                        Realisasi Keuangan (Renja)</h3>
                    <div
                        class="p-2 bg-[#dbf9ff] rounded-full text-[#004f58] group-hover:bg-[#004f58] group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-[28px] font-bold text-[#191c1e]"
                        style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Rp {{ number_format($realisasiKeuangan, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Card 3: Target Pendapatan -->
            <div
                class="bg-white rounded-[8px] border border-[#e0e3e6] p-5 shadow-sm hover:shadow-md transition-shadow duration-200 group">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#5b403d]" style="font-family: 'Manrope', sans-serif;">Target
                        Pendapatan Total</h3>
                    <div
                        class="p-2 bg-[#d4e3ff] rounded-full text-[#001c3a] group-hover:bg-[#001c3a] group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-[28px] font-bold text-[#191c1e]"
                        style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        Rp {{ number_format($targetPendapatan, 0, ',', '.') }}
                    </p>
                </div>
            </div>

        </div> --}}

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mt-8">

            <!-- Detail Pendapatan Section -->
            <div class="bg-white dark:bg-[#121212] rounded-[8px] border border-[#e0e3e6] dark:border-[#1f1f1f] shadow-sm overflow-hidden transition-colors" wire:ignore>
                <div class="px-6 py-5 border-b border-[#e0e3e6] dark:border-[#1f1f1f] bg-[#f7f9fc] dark:bg-[#1a1a1a] flex justify-between items-center transition-colors">
                    <h3 class="text-lg font-semibold text-[#191c1e] dark:text-white"
                        style="font-family: 'Plus Jakarta Sans', sans-serif;">Pendapatan Daerah Keseluruhan</h3>
                    <a href="{{ route('pendapatan.index') }}"
                        class="text-sm font-semibold text-[#005faf] dark:text-[#7bb4ff] hover:text-[#004f58] dark:hover:text-[#4fd8e6] transition-colors flex items-center gap-1">Lihat
                        Detail <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg></a>
                </div>
                <div class="p-6">
                    <div class="flex flex-col gap-4 mb-6">
                        <div class="bg-[#f7f9fc] dark:bg-[#1a1a1a] rounded-[8px] p-4 border border-[#e0e3e6] dark:border-[#1f1f1f] transition-colors">
                            <div class="text-sm text-[#5b403d] dark:text-[#a0a0a0] mb-1 font-semibold">Total Target Keseluruhan</div>
                            <div class="text-xl font-bold text-[#191c1e] dark:text-white"
                                style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                Rp {{ number_format($chartDataPendapatan['target'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-[#dbf9ff]/30 dark:bg-[#004f58]/20 rounded-[8px] p-4 border border-[#004f58]/20 dark:border-[#004f58]/50 transition-colors">
                                <div class="text-sm text-[#004f58] dark:text-[#4fd8e6] mb-1 font-semibold">Total Realisasi</div>
                                <div class="text-xl font-bold text-[#004f58] dark:text-[#4fd8e6]"
                                    style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                    Rp {{ number_format($chartDataPendapatan['realisasi'], 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="bg-[#ffdad6]/30 dark:bg-[#af101a]/20 rounded-[8px] p-4 border border-[#af101a]/20 dark:border-[#af101a]/50 transition-colors">
                                <div class="text-sm text-[#af101a] dark:text-[#ffb4ab] mb-1 font-semibold">Sisa Target</div>
                                <div class="text-xl font-bold text-[#af101a] dark:text-[#ffb4ab]"
                                    style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                    Rp
                                    {{ number_format(max($chartDataPendapatan['target'] - $chartDataPendapatan['realisasi'], 0), 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div id="public-dashboard-chart" style="min-height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Detail Renja Section -->
            <div class="bg-white dark:bg-[#121212] rounded-[8px] border border-[#e0e3e6] dark:border-[#1f1f1f] shadow-sm overflow-hidden transition-colors" wire:ignore>
                <div class="px-6 py-5 border-b border-[#e0e3e6] dark:border-[#1f1f1f] bg-[#f7f9fc] dark:bg-[#1a1a1a] flex justify-between items-center transition-colors">
                    <h3 class="text-lg font-semibold text-[#191c1e] dark:text-white"
                        style="font-family: 'Plus Jakarta Sans', sans-serif;">Belanja Daerah</h3>
                    <a href="{{ route('renja.index') }}"
                        class="text-sm font-semibold text-[#004f58] dark:text-[#4fd8e6] hover:text-[#003840] dark:hover:text-white transition-colors flex items-center gap-1">Lihat
                        Detail <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg></a>
                </div>
                <div class="p-6">
                    <div class="flex flex-col gap-4 mb-6">
                        <div class="bg-[#f7f9fc] dark:bg-[#1a1a1a] rounded-[8px] p-4 border border-[#e0e3e6] dark:border-[#1f1f1f] transition-colors">
                            <div class="text-sm text-[#5b403d] dark:text-[#a0a0a0] mb-1 font-semibold">Total Pagu/Target Keseluruhan</div>
                            <div class="text-xl font-bold text-[#191c1e] dark:text-white"
                                style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                Rp {{ number_format($chartDataRenja['target'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-[#dbf9ff]/30 dark:bg-[#004f58]/20 rounded-[8px] p-4 border border-[#004f58]/20 dark:border-[#004f58]/50 transition-colors">
                                <div class="text-sm text-[#004f58] dark:text-[#4fd8e6] mb-1 font-semibold">Total Realisasi</div>
                                <div class="text-xl font-bold text-[#004f58] dark:text-[#4fd8e6]"
                                    style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                    Rp {{ number_format($chartDataRenja['realisasi'], 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="bg-[#ffdad6]/30 dark:bg-[#af101a]/20 rounded-[8px] p-4 border border-[#af101a]/20 dark:border-[#af101a]/50 transition-colors">
                                <div class="text-sm text-[#af101a] dark:text-[#ffb4ab] mb-1 font-semibold">Sisa Target</div>
                                <div class="text-xl font-bold text-[#af101a] dark:text-[#ffb4ab]"
                                    style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                    Rp
                                    {{ number_format(max($chartDataRenja['target'] - $chartDataRenja['realisasi'], 0), 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div id="public-renja-chart" style="min-height: 350px;"></div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        // --- PENDAPATAN CHART ---
        const totalTargetPendapatan = {{ $chartDataPendapatan['target'] }};
        const totalRealisasiPendapatan = {{ $chartDataPendapatan['realisasi'] }};
        const sisaTargetPendapatan = Math.max(totalTargetPendapatan - totalRealisasiPendapatan, 0);

        if (totalTargetPendapatan > 0 || totalRealisasiPendapatan > 0) {
            var optionsPendapatan = {
                series: [totalRealisasiPendapatan, sisaTargetPendapatan],
                chart: {
                    type: 'donut',
                    height: 350,
                    fontFamily: 'Inter, sans-serif'
                },
                labels: ['Realisasi', 'Sisa Target'],
                colors: ['#005faf', '#e0e3e6'],
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
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
                                    show: true
                                },
                                value: {
                                    show: true,
                                    formatter: function(val) {
                                        return "Rp " + parseInt(val).toLocaleString('id-ID')
                                    }
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Total Target',
                                    formatter: function(w) {
                                        return "Rp " + totalTargetPendapatan.toLocaleString('id-ID')
                                    }
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return "Rp " + parseInt(val).toLocaleString('id-ID')
                        }
                    }
                },
                legend: {
                    position: 'bottom'
                }
            };
            var chartPendapatan = new ApexCharts(document.querySelector("#public-dashboard-chart"),
                optionsPendapatan);
            chartPendapatan.render();
        } else {
            document.querySelector("#public-dashboard-chart").innerHTML =
                "<p class='text-center text-[#5b403d] mt-10'>Data pendapatan tidak tersedia</p>";
        }

        // --- RENJA CHART ---
        const totalTargetRenja = {{ $chartDataRenja['target'] }};
        const totalRealisasiRenja = {{ $chartDataRenja['realisasi'] }};
        const sisaTargetRenja = Math.max(totalTargetRenja - totalRealisasiRenja, 0);

        if (totalTargetRenja > 0 || totalRealisasiRenja > 0) {
            var optionsRenja = {
                series: [totalRealisasiRenja, sisaTargetRenja],
                chart: {
                    type: 'donut',
                    height: 350,
                    fontFamily: 'Inter, sans-serif'
                },
                labels: ['Realisasi', 'Sisa Target'],
                colors: ['#004f58', '#e0e3e6'],
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
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
                                    show: true
                                },
                                value: {
                                    show: true,
                                    formatter: function(val) {
                                        return "Rp " + parseInt(val).toLocaleString('id-ID')
                                    }
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Total Pagu',
                                    formatter: function(w) {
                                        return "Rp " + totalTargetRenja.toLocaleString('id-ID')
                                    }
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return "Rp " + parseInt(val).toLocaleString('id-ID')
                        }
                    }
                },
                legend: {
                    position: 'bottom'
                }
            };
            var chartRenja = new ApexCharts(document.querySelector("#public-renja-chart"), optionsRenja);
            chartRenja.render();
        } else {
            document.querySelector("#public-renja-chart").innerHTML =
                "<p class='text-center text-[#5b403d] mt-10'>Data renja tidak tersedia</p>";
        }
    });
</script>
