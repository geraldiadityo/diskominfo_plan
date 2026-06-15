<div class="min-h-screen bg-[#eaf0f6] py-8">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        
        @if (!$selectedBidangId)
            <!-- ACCORDION VIEW -->
            <div wire:key="accordion-view" class="bg-white rounded-lg shadow-sm overflow-hidden pb-12">
                <div class="px-6 py-6 border-b border-gray-100 flex flex-col justify-center items-center">
                    <h2 class="text-2xl font-bold text-center text-red-600 mb-1">Indikator Kinerja Program</h2>
                    <p class="text-sm text-gray-400">pilih Layanan dan Urusan di bawah untuk melihat indikator kinerja program</p>
                </div>
                
                <div class="max-w-4xl mx-auto mt-8 px-4 space-y-4" x-data="{ activeAccordion: null }">
                    @forelse ($kinerjaData as $urusan)
                        <!-- Accordion Item -->
                        <div wire:key="urusan-{{ $urusan['id'] }}" class="rounded-md overflow-hidden">
                            <!-- Accordion Header -->
                            <button 
                                type="button"
                                @click="activeAccordion = activeAccordion === {{ $urusan['id'] }} ? null : {{ $urusan['id'] }}"
                                :class="{ 'border-l-4 border-red-500': activeAccordion === {{ $urusan['id'] }}, 'border-l-4 border-green-500': activeAccordion !== {{ $urusan['id'] }} }"
                                class="w-full flex items-center px-4 py-4 bg-[#c8dceb] hover:bg-[#b5cce0] transition-colors duration-200 border border-gray-300 rounded-md focus:outline-none"
                            >
                                <div class="bg-white rounded-full p-1 mr-4 flex-shrink-0">
                                    <svg x-show="activeAccordion !== {{ $urusan['id'] }}" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    <svg x-show="activeAccordion === {{ $urusan['id'] }}" style="display: none;" class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </div>
                                <span class="font-bold text-gray-800 text-sm uppercase text-left">{{ $urusan['nama_urusan'] }}</span>
                            </button>
                            
                            <!-- Accordion Content -->
                            <div 
                                x-show="activeAccordion === {{ $urusan['id'] }}" 
                                x-collapse 
                                style="display: none;"
                                class="bg-[#f7f9fb] border-l-4 border-r border-b border-red-500 rounded-b-md"
                            >
                                <ul class="py-4 px-6 space-y-4">
                                    @foreach ($urusan['bidangs'] as $bidang)
                                        <li wire:key="bidang-{{ $bidang['id'] }}">
                                            <a href="?bidang={{ $bidang['id'] }}" wire:navigate class="flex items-center text-left w-full group hover:bg-gray-100 p-2 rounded-md transition-colors cursor-pointer relative z-10">
                                                <span class="text-blue-500 mr-3 font-bold group-hover:text-blue-700">></span>
                                                <span class="text-xs font-semibold text-gray-700 uppercase group-hover:text-blue-600">{{ $bidang['nama_bidang'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                    @if(count($urusan['bidangs']) === 0)
                                        <li class="text-sm text-gray-500 italic px-2">Tidak ada bidang untuk urusan ini.</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-8">Data tidak tersedia.</div>
                    @endforelse
                </div>
            </div>
            
        @else
            <!-- TABLE VIEW -->
            <div wire:key="table-view" class="bg-white rounded-lg shadow-sm overflow-hidden">
                <!-- Breadcrumbs -->
                <div class="px-6 py-4 bg-[#eaf0f6] border-b border-gray-200">
                    <nav class="flex text-sm font-medium text-gray-600">
                        <a href="#" class="text-red-600 hover:underline">Home</a>
                        <span class="mx-2">></span>
                        <a href="?" wire:navigate class="hover:underline">Indikator Kinerja Program</a>
                        <span class="mx-2">></span>
                        <span class="uppercase">{{ $selectedBidangName }}</span>
                    </nav>
                </div>
                
                <div class="px-6 py-6 flex flex-col justify-center items-center">
                    <p class="text-sm text-red-500 mb-1">Indikator Kinerja Program</p>
                    <h2 class="text-xl font-bold text-center text-red-600 uppercase">{{ $selectedBidangName }}</h2>
                </div>
                
                @php
                    // Find the selected bidang data
                    $selectedData = collect();
                    foreach ($kinerjaData as $urusan) {
                        foreach ($urusan['bidangs'] as $bidang) {
                            if ($bidang['id'] == $selectedBidangId) {
                                $selectedData = $bidang['programs'];
                                break 2;
                            }
                        }
                    }
                @endphp

                <div class="overflow-x-auto px-4 pb-8">
                    <table class="w-full text-xs text-left text-gray-900 border-collapse whitespace-nowrap">
                        <thead class="text-white bg-[#e6192b] font-semibold">
                            <tr>
                                <th rowspan="3" class="px-3 py-3 border border-red-700 text-center align-middle">No</th>
                                <th rowspan="3" class="px-4 py-3 border border-red-700 text-center align-middle min-w-[250px]">Indikator</th>
                                <th rowspan="3" class="px-3 py-3 border border-red-700 text-center align-middle">Satuan</th>
                                <th rowspan="3" class="px-3 py-3 border border-red-700 text-center align-middle">Kondisi Awal</th>
                                <th colspan="{{ count($years) * 2 }}" class="px-4 py-2 border border-red-700 text-center">Tahun</th>
                                <th rowspan="3" class="px-3 py-3 border border-red-700 text-center align-middle">Kondisi Akhir</th>
                                <th rowspan="3" class="px-3 py-3 border border-red-700 text-center align-middle">Perangkat Daerah</th>
                            </tr>
                            <tr>
                                @foreach ($years as $year)
                                    <th colspan="2" class="px-2 py-2 border border-red-700 text-center">{{ $year }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach ($years as $year)
                                    <th class="px-2 py-2 border border-red-700 text-center">Target</th>
                                    <th class="px-2 py-2 border border-red-700 text-center">Capaian</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($selectedData as $program)
                                <!-- Program Row -->
                                <tr class="bg-[#c8dceb] font-bold text-gray-800">
                                    <td colspan="{{ 6 + (count($years) * 2) }}" class="px-4 py-3 border border-gray-300 uppercase">
                                        {{ $program['nama_program'] }}
                                    </td>
                                </tr>
                                
                                <!-- Indicator Rows -->
                                @forelse ($program['indikators'] as $index => $indikator)
                                    <tr class="bg-white hover:bg-gray-50 transition-colors">
                                        <td class="px-3 py-2 border border-gray-300 text-center">{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 border border-gray-300 whitespace-normal">{{ $indikator['nama_indikator'] }}</td>
                                        <td class="px-3 py-2 border border-gray-300 text-center">{{ $indikator['satuan'] }}</td>
                                        <td class="px-3 py-2 border border-gray-300 text-center">{{ $indikator['kondisi_awal'] ?? '-' }}</td>
                                        
                                        @foreach ($years as $year)
                                            <td class="px-2 py-2 border border-gray-300 text-center">{{ is_numeric($indikator['years'][$year]['target']) ? floatval($indikator['years'][$year]['target']) : ($indikator['years'][$year]['target'] ?? '') }}</td>
                                            <td class="px-2 py-2 border border-gray-300 text-center">{{ is_numeric($indikator['years'][$year]['realisasi']) ? floatval($indikator['years'][$year]['realisasi']) : ($indikator['years'][$year]['realisasi'] ?? '') }}</td>
                                        @endforeach
                                        
                                        <td class="px-3 py-2 border border-gray-300 text-center">{{ $indikator['kondisi_akhir'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border border-gray-300 text-center uppercase whitespace-normal">{{ $indikator['perangkat_daerah'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 6 + (count($years) * 2) }}" class="px-4 py-4 border border-gray-300 text-center text-gray-500">
                                            Tidak ada indikator untuk program ini.
                                        </td>
                                    </tr>
                                @endforelse
                            @empty
                                <tr>
                                    <td colspan="{{ 6 + (count($years) * 2) }}" class="px-4 py-8 border border-gray-300 text-center text-gray-500">
                                        Tidak ada data program yang tersedia untuk bidang ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
