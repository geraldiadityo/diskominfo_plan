<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-center items-center">
            <h2 class="text-xl font-bold text-center text-red-600">Indikator Kinerja Utama</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-900 border-collapse">
                <thead class="text-xs text-white bg-red-600 font-semibold">
                    <tr>
                        <th rowspan="3" class="px-3 py-3 border border-red-700 text-center w-12 align-middle">No</th>
                        <th rowspan="3" class="px-4 py-3 border border-red-700 text-center align-middle">Indikator</th>
                        <th rowspan="3" class="px-3 py-3 border border-red-700 text-center align-middle">Satuan</th>
                        <th colspan="{{ count($years) * 2 }}" class="px-4 py-2 border border-red-700 text-center">Tahun</th>
                    </tr>
                    <tr>
                        @foreach ($years as $year)
                            <th colspan="2" class="px-2 py-2 border border-red-700 text-center">{{ $year }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($years as $year)
                            <th class="px-2 py-2 border border-red-700 text-center">Target</th>
                            <th class="px-2 py-2 border border-red-700 text-center">Realisasi</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ikuData as $kategori)
                        @if (count($kategori['indikators']) > 0)
                            <!-- Category Row -->
                            <tr class="bg-blue-100 font-medium text-red-700">
                                <td colspan="{{ 3 + (count($years) * 2) }}" class="px-4 py-3 border border-gray-300">
                                    {{ $kategori['nama_kategori'] }}
                                </td>
                            </tr>
                            <!-- Indicator Rows -->
                            @foreach ($kategori['indikators'] as $index => $indikator)
                                <tr class="bg-white hover:bg-gray-50 transition-colors">
                                    <td class="px-3 py-2 border border-gray-300 text-center">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 border border-gray-300">{{ $indikator['nama_indikator'] }}</td>
                                    <td class="px-3 py-2 border border-gray-300 text-center">{{ $indikator['satuan'] }}</td>
                                    @foreach ($years as $year)
                                        <td class="px-2 py-2 border border-gray-300 text-center">{{ is_numeric($indikator['years'][$year]['target']) ? floatval($indikator['years'][$year]['target']) : ($indikator['years'][$year]['target'] ?? '') }}</td>
                                        <td class="px-2 py-2 border border-gray-300 text-center">{{ is_numeric($indikator['years'][$year]['realisasi']) ? floatval($indikator['years'][$year]['realisasi']) : ($indikator['years'][$year]['realisasi'] ?? '') }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ 3 + (count($years) * 2) }}" class="px-4 py-8 border border-gray-300 text-center text-gray-500">
                                Tidak ada data Indikator Kinerja Utama yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
