<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Breadcrumbs -->
    <nav class="flex text-sm text-[#5b403d] dark:text-[#a0a0a0] font-medium mb-6 transition-colors">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center hover:text-[#004f58] dark:hover:text-white transition-colors">
                    Home
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <span class="mx-2">/</span>
                    <span class="text-[#191c1e] dark:text-[#e0e0e0] font-semibold transition-colors">Realisasi Keuangan (Renja)</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-[#dbf9ff] dark:bg-[#003840] text-[#004f58] dark:text-[#4fd8e6] flex items-center justify-center shrink-0 transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-[#191c1e] dark:text-white tracking-tight transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">Rekapitulasi Realisasi Keuangan</h2>
                <p class="text-[#5b403d] dark:text-[#a0a0a0] mt-1 transition-colors">Daftar pagu dan realisasi keuangan per Organisasi Perangkat Daerah (OPD) - Tahun {{ $tahun }}</p>
            </div>
        </div>
    </div>

    <!-- Summary Chart Section -->
    <div class="bg-white dark:bg-[#121212] border border-[#e0e3e6] dark:border-[#1f1f1f] rounded-xl p-6 shadow-sm mb-8 transition-colors" wire:ignore>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[#191c1e] dark:text-white transition-colors" style="font-family: 'Plus Jakarta Sans', sans-serif;">Grafik Realisasi Keuangan Keseluruhan (Semua OPD)</h3>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
            <div class="lg:col-span-1 flex flex-col gap-4">
                <div class="p-4 bg-[#f2f4f7] dark:bg-[#1a1a1a] border border-transparent dark:border-[#2a2a2a] rounded-lg transition-colors">
                    <p class="text-sm font-semibold text-[#5b403d] dark:text-[#a0a0a0] mb-1">Total Pagu Keseluruhan</p>
                    <p class="text-2xl font-bold text-[#191c1e] dark:text-white">Rp {{ number_format($skpds->sum('target'), 0, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-[#dbf9ff]/30 dark:bg-[#004f58]/20 rounded-lg border border-[#004f58]/20 dark:border-[#004f58]/50 transition-colors">
                    <p class="text-sm font-semibold text-[#004f58] dark:text-[#4fd8e6] mb-1">Total Realisasi Keseluruhan</p>
                    <p class="text-2xl font-bold text-[#004f58] dark:text-[#4fd8e6]">Rp {{ number_format($skpds->sum('realisasi'), 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="lg:col-span-2">
                <div id="overall-renja-chart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Top / Bottom Panels Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Top 5 Keuangan -->
        <div class="bg-white border-2 border-[#d4e3ff] rounded-xl overflow-hidden shadow-sm">
            <div class="p-4 text-center border-b border-[#e0e3e6]">
                <h4 class="text-[#ba1a1a] font-bold text-lg" style="font-family: 'Plus Jakarta Sans', sans-serif;">5 OPD dengan Realisasi Keuangan Tertinggi</h4>
                <p class="text-xs text-[#5b403d] mt-1">sampai dengan hari ini</p>
            </div>
            <table class="w-full text-sm" style="font-family: 'Inter', sans-serif;">
                <thead class="bg-[#b3cbe6] text-[#191c1e] font-semibold">
                    <tr>
                        <th class="px-4 py-2 text-left w-12 border-b border-[#e0e3e6]">No.</th>
                        <th class="px-4 py-2 text-left border-b border-[#e0e3e6]">OPD</th>
                        <th class="px-4 py-2 text-right border-b border-[#e0e3e6]">Persentase</th>
                    </tr>
                </thead>
                <tbody class="text-[#191c1e]">
                    @foreach($topKeuangan as $idx => $item)
                    <tr class="{{ $idx % 2 == 0 ? 'bg-white' : 'bg-[#f7f9fc]' }}">
                        <td class="px-4 py-2 border-b border-[#e0e3e6]">{{ $idx + 1 }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] uppercase">{{ $item['nama_skpd'] }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] text-right font-medium">{{ number_format($item['persentase'], 2, ',', '.') }} %</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Bottom 5 Keuangan -->
        <div class="bg-white border-2 border-[#d4e3ff] rounded-xl overflow-hidden shadow-sm">
            <div class="p-4 text-center border-b border-[#e0e3e6]">
                <h4 class="text-[#ba1a1a] font-bold text-lg" style="font-family: 'Plus Jakarta Sans', sans-serif;">5 OPD dengan Realisasi Keuangan Terendah</h4>
                <p class="text-xs text-[#5b403d] mt-1">sampai dengan hari ini</p>
            </div>
            <table class="w-full text-sm" style="font-family: 'Inter', sans-serif;">
                <thead class="bg-[#b3cbe6] text-[#191c1e] font-semibold">
                    <tr>
                        <th class="px-4 py-2 text-left w-12 border-b border-[#e0e3e6]">No.</th>
                        <th class="px-4 py-2 text-left border-b border-[#e0e3e6]">OPD</th>
                        <th class="px-4 py-2 text-right border-b border-[#e0e3e6]">Persentase</th>
                    </tr>
                </thead>
                <tbody class="text-[#191c1e]">
                    @foreach($bottomKeuangan as $idx => $item)
                    <tr class="{{ $idx % 2 == 0 ? 'bg-white' : 'bg-[#f7f9fc]' }}">
                        <td class="px-4 py-2 border-b border-[#e0e3e6]">{{ $idx + 1 }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] uppercase">{{ $item['nama_skpd'] }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] text-right font-medium">{{ number_format($item['persentase'], 2, ',', '.') }} %</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Top 5 Kinerja -->
        <div class="bg-white border-2 border-[#d4e3ff] rounded-xl overflow-hidden shadow-sm">
            <div class="p-4 text-center border-b border-[#e0e3e6]">
                <h4 class="text-[#ba1a1a] font-bold text-lg" style="font-family: 'Plus Jakarta Sans', sans-serif;">5 OPD dengan Realisasi Kinerja Tertinggi</h4>
                <p class="text-xs text-[#5b403d] mt-1">sampai dengan hari ini</p>
            </div>
            <table class="w-full text-sm" style="font-family: 'Inter', sans-serif;">
                <thead class="bg-[#b3cbe6] text-[#191c1e] font-semibold">
                    <tr>
                        <th class="px-4 py-2 text-left w-12 border-b border-[#e0e3e6]">No.</th>
                        <th class="px-4 py-2 text-left border-b border-[#e0e3e6]">OPD</th>
                        <th class="px-4 py-2 text-right border-b border-[#e0e3e6]">Deviasi</th>
                    </tr>
                </thead>
                <tbody class="text-[#191c1e]">
                    @foreach($topKinerja as $idx => $item)
                    <tr class="{{ $idx % 2 == 0 ? 'bg-white' : 'bg-[#f7f9fc]' }}">
                        <td class="px-4 py-2 border-b border-[#e0e3e6]">{{ $idx + 1 }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] uppercase">{{ $item['nama_skpd'] }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] text-right font-medium">{{ number_format($item['deviasi'], 2, ',', '.') }} %</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Bottom 5 Kinerja -->
        <div class="bg-white border-2 border-[#d4e3ff] rounded-xl overflow-hidden shadow-sm">
            <div class="p-4 text-center border-b border-[#e0e3e6]">
                <h4 class="text-[#ba1a1a] font-bold text-lg" style="font-family: 'Plus Jakarta Sans', sans-serif;">5 OPD dengan Realisasi Kinerja Terendah</h4>
                <p class="text-xs text-[#5b403d] mt-1">sampai dengan hari ini</p>
            </div>
            <table class="w-full text-sm" style="font-family: 'Inter', sans-serif;">
                <thead class="bg-[#b3cbe6] text-[#191c1e] font-semibold">
                    <tr>
                        <th class="px-4 py-2 text-left w-12 border-b border-[#e0e3e6]">No.</th>
                        <th class="px-4 py-2 text-left border-b border-[#e0e3e6]">OPD</th>
                        <th class="px-4 py-2 text-right border-b border-[#e0e3e6]">Deviasi</th>
                    </tr>
                </thead>
                <tbody class="text-[#191c1e]">
                    @foreach($bottomKinerja as $idx => $item)
                    <tr class="{{ $idx % 2 == 0 ? 'bg-white' : 'bg-[#f7f9fc]' }}">
                        <td class="px-4 py-2 border-b border-[#e0e3e6]">{{ $idx + 1 }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] uppercase">{{ $item['nama_skpd'] }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] text-right font-medium">{{ number_format($item['deviasi'], 2, ',', '.') }} %</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Top 5 Fisik -->
        <div class="bg-white border-2 border-[#d4e3ff] rounded-xl overflow-hidden shadow-sm">
            <div class="p-4 text-center border-b border-[#e0e3e6]">
                <h4 class="text-[#ba1a1a] font-bold text-lg" style="font-family: 'Plus Jakarta Sans', sans-serif;">5 OPD dengan Realisasi Fisik Tertinggi</h4>
                <p class="text-xs text-[#5b403d] mt-1">sampai dengan hari ini</p>
            </div>
            <table class="w-full text-sm" style="font-family: 'Inter', sans-serif;">
                <thead class="bg-[#b3cbe6] text-[#191c1e] font-semibold">
                    <tr>
                        <th class="px-4 py-2 text-left w-12 border-b border-[#e0e3e6]">No.</th>
                        <th class="px-4 py-2 text-left border-b border-[#e0e3e6]">OPD</th>
                        <th class="px-4 py-2 text-right border-b border-[#e0e3e6]">Persentase</th>
                    </tr>
                </thead>
                <tbody class="text-[#191c1e]">
                    @foreach($topFisik as $idx => $item)
                    <tr class="{{ $idx % 2 == 0 ? 'bg-white' : 'bg-[#f7f9fc]' }}">
                        <td class="px-4 py-2 border-b border-[#e0e3e6]">{{ $idx + 1 }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] uppercase">{{ $item['nama_skpd'] }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] text-right font-medium">{{ number_format($item['persentase_fisik'], 2, ',', '.') }} %</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Bottom 5 Fisik -->
        <div class="bg-white border-2 border-[#d4e3ff] rounded-xl overflow-hidden shadow-sm">
            <div class="p-4 text-center border-b border-[#e0e3e6]">
                <h4 class="text-[#ba1a1a] font-bold text-lg" style="font-family: 'Plus Jakarta Sans', sans-serif;">5 OPD dengan Realisasi Fisik Terendah</h4>
                <p class="text-xs text-[#5b403d] mt-1">sampai dengan hari ini</p>
            </div>
            <table class="w-full text-sm" style="font-family: 'Inter', sans-serif;">
                <thead class="bg-[#b3cbe6] text-[#191c1e] font-semibold">
                    <tr>
                        <th class="px-4 py-2 text-left w-12 border-b border-[#e0e3e6]">No.</th>
                        <th class="px-4 py-2 text-left border-b border-[#e0e3e6]">OPD</th>
                        <th class="px-4 py-2 text-right border-b border-[#e0e3e6]">Persentase</th>
                    </tr>
                </thead>
                <tbody class="text-[#191c1e]">
                    @foreach($bottomFisik as $idx => $item)
                    <tr class="{{ $idx % 2 == 0 ? 'bg-white' : 'bg-[#f7f9fc]' }}">
                        <td class="px-4 py-2 border-b border-[#e0e3e6]">{{ $idx + 1 }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] uppercase">{{ $item['nama_skpd'] }}</td>
                        <td class="px-4 py-2 border-b border-[#e0e3e6] text-right font-medium">{{ number_format($item['persentase_fisik'], 2, ',', '.') }} %</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
                    <tr class="bg-[#004f58] dark:bg-[#003840] text-white text-sm uppercase tracking-wider transition-colors" style="font-family: 'Manrope', sans-serif;">
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Nama OPD / SKPD</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Pagu/Target (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Realisasi (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-center whitespace-nowrap">%</th>
                        <th class="px-6 py-4 font-semibold text-right whitespace-nowrap">Sisa Pagu (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-[#191c1e] dark:text-[#e0e0e0] divide-y divide-[#e0e3e6] dark:divide-[#1f1f1f] transition-colors">
                    @forelse($skpds as $index => $skpd)
                    <tr class="hover:bg-[#f2f4f7] dark:hover:bg-[#1a1a1a] transition-colors {{ $index % 2 == 0 ? '' : 'bg-[#004f58]/5 dark:bg-[#ffffff]/5' }}">
                        <td class="px-6 py-4 font-medium text-[#191c1e] dark:text-white">{{ $skpd['nama_skpd'] }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">{{ number_format($skpd['target'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap font-medium text-[#004f58] dark:text-[#4fd8e6]">{{ number_format($skpd['realisasi'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $skpd['persentase'] < 50 ? 'bg-[#ffdad6] dark:bg-[#93000a]/30 text-[#93000a] dark:text-[#ffb4ab]' : 'bg-[#dbf9ff] dark:bg-[#004f58]/30 text-[#004f58] dark:text-[#4fd8e6]' }}">
                                {{ $skpd['persentase'] }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap font-medium text-[#ba1a1a] dark:text-[#ffb4ab]">
                            {{ number_format($skpd['sisa'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <a href="{{ route('renja.detail', $skpd['id']) }}" class="inline-flex items-center gap-1 text-[#004f58] dark:text-[#4fd8e6] hover:text-[#003840] dark:hover:text-[#ffffff] font-semibold transition-colors bg-white dark:bg-[#121212] hover:bg-[#dbf9ff] dark:hover:bg-[#2a2a2a] border border-[#004f58] dark:border-[#4fd8e6] hover:border-[#003840] dark:hover:border-[#ffffff] px-3 py-1.5 rounded-lg text-xs" style="font-family: 'Manrope', sans-serif;">
                                Lihat Detail
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-[#5b403d] dark:text-[#a0a0a0]">Belum ada data OPD yang memiliki rencana kerja (Renja) tahun ini.</td>
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
        const totalTarget = {{ $skpds->sum('target') }};
        const totalRealisasi = {{ $skpds->sum('realisasi') }};
        const sisaTarget = Math.max(totalTarget - totalRealisasi, 0);
        
        if (totalTarget > 0 || totalRealisasi > 0) {
            var options = {
                series: [totalRealisasi, sisaTarget],
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'Inter, sans-serif'
                },
                labels: ['Realisasi', 'Sisa Target/Pagu'],
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
                                    label: 'Total Pagu',
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

            var chart = new ApexCharts(document.querySelector("#overall-renja-chart"), options);
            chart.render();
        } else {
            document.querySelector("#overall-renja-chart").innerHTML = "<p class='text-center text-[#5b403d] mt-10'>Data tidak tersedia</p>";
        }
    });
</script>
