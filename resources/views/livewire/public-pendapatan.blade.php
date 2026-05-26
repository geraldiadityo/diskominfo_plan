<div>
    <div class="max-w-[1400px] mx-auto px-4 py-8 sm:px-6 lg:px-8">
        
        <!-- Page Title -->
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-[#191c1e] text-center w-full" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                PENDAPATAN KESELURUHAN
            </h1>
        </div>

        <!-- Info Banner -->
        <div class="bg-[#f2f4f7] border border-[#e0e3e6] rounded-[8px] p-3 mb-6 flex items-center text-sm" style="font-family: 'Inter', sans-serif;">
            <div class="mr-2 text-[#ba1a1a]">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
            </div>
            <span class="text-[#191c1e] font-semibold mr-1">data yang ditampilkan terakhir update</span> 
            <span class="text-[#ba1a1a]">{{ $lastUpdate }}</span>
            
            <!-- Link Back -->
            <a href="{{ route('pendapatan.index') }}" wire:navigate class="ml-auto text-[#005faf] hover:underline font-semibold flex items-center text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Rekapitulasi
            </a>
        </div>

        <!-- Table Container -->
        <div class="bg-white border border-[#e0e3e6] rounded-[8px] overflow-hidden shadow-sm overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse" style="font-family: 'Inter', sans-serif;">
                <thead class="text-white text-center font-semibold bg-[#d32f2f]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    <tr>
                        <th rowspan="2" class="px-4 py-3 border border-[#ba1a20] w-[8%]">No</th>
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
                <tbody class="text-[#191c1e]">
                    @foreach($pendapatanData as $index => $row)
                        @php
                            // Background color based on level
                            if ($row['level'] == 1) {
                                $bgClass = 'bg-[#cce0ff]';
                            } elseif ($row['level'] == 2) {
                                $bgClass = 'bg-[#e6f0ff]';
                            } elseif ($row['level'] == 3) {
                                $bgClass = 'bg-[#f2f7ff]';
                            } else {
                                // Alternating colors based on index for level 4
                                $bgClass = $index % 2 === 0 ? 'bg-[#f7f9fc]' : 'bg-white';
                            }
                            
                            // Define padding based on level (level 1 = 0, level 2 = 1.5rem, etc)
                            // We can use style attribute for dynamic padding
                            $paddingLeft = max(0, ($row['level'] - 1) * 20);
                            
                            // Bold text for higher levels (1, 2, 3)
                            $textClass = $row['level'] <= 3 ? 'font-bold' : '';
                        @endphp
                        <tr class="{{ $bgClass }} hover:bg-[#e6e8eb] transition-colors">
                            <td class="px-4 py-2 border border-[#e0e3e6] text-center font-semibold">{{ $row['kode'] }}</td>
                            <td class="px-4 py-2 border border-[#e0e3e6] {{ $textClass }}" style="padding-left: {{ 16 + $paddingLeft }}px;">
                                {{ $row['uraian'] }}
                            </td>
                            <td class="px-4 py-2 border border-[#e0e3e6] text-right">
                                {{ number_format($row['target'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 border border-[#e0e3e6] text-right">
                                {{ number_format($row['realisasi'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 border border-[#e0e3e6] text-right">
                                {{ number_format($row['selisih'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 border border-[#e0e3e6] text-center font-semibold">
                                {{ number_format($row['persentase'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            @if(count($pendapatanData) === 0)
            <div class="p-8 text-center text-[#5b403d]">
                Tidak ada data pendapatan untuk tahun {{ $tahun }}.
            </div>
            @endif
        </div>

    </div>
</div>
