<x-filament-panels::page>
    <style>
        .report-table {
            border-collapse: collapse !important;
            width: 100%;
            font-size: 10px;
            /* Font dikecilkan */
            color: #1e293b;
            background-color: white;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #475569 !important;
            /* Border Slate-600 (lebih gelap) */
            padding: 4px 6px !important;
            line-height: 1.2;
        }

        .report-table thead th {
            background-color: #1e40af !important;
            /* Blue-800 */
            color: white !important;
            text-align: center;
            vertical-align: middle;
            text-transform: uppercase;
        }

        .row-program {
            background-color: #dbeafe !important;
            font-weight: bold;
        }

        /* Blue-100 */
        .row-kegiatan {
            background-color: #f1f5f9 !important;
            font-weight: 600;
        }

        /* Slate-100 */
        .row-sub {
            background-color: #ffffff;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .font-bold {
            font-weight: bold;
        }

        .negative-dev {
            color: #dc2626;
            font-weight: bold;
        }

        /* Red-600 */
    </style>

    <div class="overflow-x-auto shadow-xl rounded-lg">
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
                    $data = $this->getTableData();
                    $noProg = 1;
                @endphp

                @forelse($data as $program)
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
                            <tr class="row-sub">
                                <td></td>
                                <td style="padding-left: 30px !important; color: #475569;">{{ $sub['nama'] }}</td>
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
                                <td class="text-right">{{ number_format($sub['realisasi_keuangan'], 0, ',', '.') }}
                                </td>
                                <td class="text-center">
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
                        <td colspan="11" class="text-center py-4 text-gray-400 italic">Data Renja belum tersedia.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
