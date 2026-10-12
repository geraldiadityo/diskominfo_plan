<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, Calibri, sans-serif;
            font-size: 10pt;
            color: #000000;
        }
        th, td {
            border: 1px solid #000000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .title {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            border: none !important;
        }
        .header-cell {
            background-color: #FFFFFF;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        .col-num-cell {
            background-color: #FFFFFF;
            font-size: 9pt;
            text-align: center;
            vertical-align: middle;
        }
        .row-prog td {
            background-color: #D9EAD3;
            font-weight: bold;
        }
        .row-keg td {
            background-color: #BFBFBF;
            font-weight: bold;
        }
        .text-r {
            text-align: right;
            mso-number-format: "#,##0.00";
        }
        .text-dec {
            text-align: right;
            mso-number-format: "0.00";
        }
        .text-c {
            text-align: center;
        }
        .text-l {
            text-align: left;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="18" class="title">LAPORAN REALISASI FISIK DAN KEUANGAN TRIWULAN {{ $triwulanRomawi }}</td>
        </tr>
        <tr>
            <td colspan="18" class="title">{{ strtoupper($skpd?->nama_skpd ?? 'PERANGKAT DAERAH') }}</td>
        </tr>
        <tr><td colspan="18" style="border:none;"></td></tr>

        <thead>
            {{-- Header Row 1 --}}
            <tr>
                <th rowspan="4" class="header-cell">Program, Kegiatan dan Sub Kegiatan</th>
                <th rowspan="4" class="header-cell">Indikator Kinerja</th>
                <th rowspan="4" class="header-cell">Satuan</th>
                <th rowspan="4" class="header-cell">Pagu (Rp)</th>
                <th colspan="14" class="header-cell">Triwulan {{ $triwulan }}</th>
            </tr>

            {{-- Header Row 2 --}}
            <tr>
                <th colspan="7" class="header-cell">Kinerja Fisik</th>
                <th colspan="5" class="header-cell">Keuangan</th>
                <th rowspan="3" class="header-cell">Permasalahan</th>
                <th rowspan="3" class="header-cell">Upaya mengatasi permasalahan</th>
            </tr>

            {{-- Header Row 3 --}}
            <tr>
                <th rowspan="2" class="header-cell">Indikator</th>
                <th rowspan="2" class="header-cell">Target</th>
                <th colspan="3" class="header-cell">Realisasi</th>
                <th colspan="2" class="header-cell">Akumulasi Tahunan</th>
                <th rowspan="2" class="header-cell">Target (Rp)</th>
                <th rowspan="2" class="header-cell">Realisasi (Rp)</th>
                <th rowspan="2" class="header-cell">Tingkat Capaian (%)</th>
                <th colspan="2" class="header-cell">Akumulasi Tahunan</th>
            </tr>

            {{-- Header Row 4 --}}
            <tr>
                <th class="header-cell">Realisasi</th>
                <th class="header-cell">Capaian (%)</th>
                <th class="header-cell">Tingkat Capaian (%)</th>
                <th class="header-cell">Target (%)</th>
                <th class="header-cell">Realisasi (%)</th>
                <th class="header-cell">Target (%)</th>
                <th class="header-cell">Realisasi (%)</th>
            </tr>

            {{-- Header Row 5: Nomor Kolom --}}
            <tr>
                @for($n = 1; $n <= 18; $n++)
                    <th class="col-num-cell">{{ $n }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $prog)
                @php
                    $tcKeuProg = $prog['target_keuangan_tw'] > 0 ? round(($prog['realisasi_keuangan_tw'] / $prog['target_keuangan_tw']) * 100, 2) : 0;
                    $akTgKeuProg = $prog['pagu'] > 0 ? round(($prog['target_keuangan_kum'] / $prog['pagu']) * 100, 0) : 0;
                    $akRlKeuProg = $prog['pagu'] > 0 ? round($prog['realisasi_keuangan_kum'] / $prog['pagu'], 2) : 0;
                    $tcFisProg = $prog['sub_count'] > 0 ? round($prog['tingkat_capaian_fisik_sum'] / $prog['sub_count'], 0) : 0;
                    $akTgFisProg = $prog['sub_count'] > 0 ? round($prog['target_fisik_kum_sum'] / $prog['sub_count'], 0) : 0;
                    $akRlFisProg = $prog['sub_count'] > 0 ? round($prog['realisasi_fisik_kum_sum'] / $prog['sub_count'], 0) : 0;
                @endphp
                <tr class="row-prog">
                    <td class="text-l">{{ $prog['nama'] }}</td>
                    <td></td><td></td>
                    <td class="text-r">{{ $prog['pagu'] }}</td>
                    <td></td><td></td><td></td><td></td>
                    <td class="text-c">{{ $tcFisProg }}%</td>
                    <td class="text-c">{{ $akTgFisProg }}%</td>
                    <td class="text-c">{{ $akRlFisProg }}%</td>
                    <td class="text-r">{{ $prog['target_keuangan_tw'] }}</td>
                    <td class="text-r">{{ $prog['realisasi_keuangan_tw'] }}</td>
                    <td class="text-c">{{ number_format($tcKeuProg, 2, '.', ',') }}%</td>
                    <td class="text-c">{{ $akTgKeuProg }}%</td>
                    <td class="text-dec">{{ $akRlKeuProg }}</td>
                    <td></td><td></td>
                </tr>

                @foreach($prog['kegiatans'] as $keg)
                    @php
                        $tcKeuKeg = $keg['target_keuangan_tw'] > 0 ? round(($keg['realisasi_keuangan_tw'] / $keg['target_keuangan_tw']) * 100, 2) : 0;
                        $akTgKeuKeg = $keg['pagu'] > 0 ? round(($keg['target_keuangan_kum'] / $keg['pagu']) * 100, 0) : 0;
                        $akRlKeuKeg = $keg['pagu'] > 0 ? round($keg['realisasi_keuangan_kum'] / $keg['pagu'], 2) : 0;
                        $tcFisKeg = $keg['sub_count'] > 0 ? round($keg['tingkat_capaian_fisik_sum'] / $keg['sub_count'], 0) : 0;
                        $akTgFisKeg = $keg['sub_count'] > 0 ? round($keg['target_fisik_kum_sum'] / $keg['sub_count'], 0) : 0;
                        $akRlFisKeg = $keg['sub_count'] > 0 ? round($keg['realisasi_fisik_kum_sum'] / $keg['sub_count'], 0) : 0;
                    @endphp
                    <tr class="row-keg">
                        <td class="text-l">{{ $keg['nama'] }}</td>
                        <td></td><td></td>
                        <td class="text-r">{{ $keg['pagu'] }}</td>
                        <td></td><td></td><td></td><td></td>
                        <td class="text-c">{{ $tcFisKeg }}%</td>
                        <td class="text-c">{{ $akTgFisKeg }}%</td>
                        <td class="text-c">{{ $akRlFisKeg }}%</td>
                        <td class="text-r">{{ $keg['target_keuangan_tw'] }}</td>
                        <td class="text-r">{{ $keg['realisasi_keuangan_tw'] }}</td>
                        <td class="text-c">{{ number_format($tcKeuKeg, 2, '.', ',') }}%</td>
                        <td class="text-c">{{ $akTgKeuKeg }}%</td>
                        <td class="text-dec">{{ $akRlKeuKeg }}</td>
                        <td></td><td></td>
                    </tr>

                    @foreach($keg['sub_kegiatans'] as $sub)
                        @php
                            $rowCount = count($sub['bukti_list']);
                            $firstBf = $sub['bukti_list'][0];
                        @endphp
                        <tr>
                            <td rowspan="{{ $rowCount }}" class="text-c">{{ $sub['nama'] }}</td>
                            <td rowspan="{{ $rowCount }}" class="text-c">{{ $sub['indikator_kinerja'] }}</td>
                            <td rowspan="{{ $rowCount }}" class="text-c">{{ $sub['satuan'] }}</td>
                            <td rowspan="{{ $rowCount }}" class="text-r">{{ $sub['pagu'] }}</td>

                            <td class="text-l">{{ $firstBf['nama_indikator'] }}</td>
                            <td class="text-l">{{ $firstBf['target'] }}</td>
                            <td class="text-r">{{ $firstBf['realisasi'] }}</td>
                            <td class="text-r">{{ $firstBf['capaian'] }}</td>

                            <td rowspan="{{ $rowCount }}" class="text-c">
                                {{ $sub['has_bukti'] || $sub['tingkat_capaian_fisik'] > 0 ? $sub['tingkat_capaian_fisik'] . '%' : '' }}
                            </td>

                            <td class="text-r">{{ $firstBf['target_akumulasi'] }}</td>
                            <td class="text-r">{{ $firstBf['realisasi_akumulasi'] }}</td>

                            <td rowspan="{{ $rowCount }}" class="text-r">{{ $sub['target_keuangan_tw'] }}</td>
                            <td rowspan="{{ $rowCount }}" class="text-r">{{ $sub['realisasi_keuangan_tw'] }}</td>
                            <td rowspan="{{ $rowCount }}" class="text-c">{{ number_format($sub['tingkat_capaian_keu'], 2, '.', ',') }}%</td>
                            <td rowspan="{{ $rowCount }}" class="text-c">{{ $sub['persen_target_keu_kum'] }}%</td>
                            <td rowspan="{{ $rowCount }}" class="text-dec">{{ $sub['rasio_real_keu_kum'] }}</td>

                            <td rowspan="{{ $rowCount }}" class="text-l">{!! nl2br(e($sub['alasan'])) !!}</td>
                            <td rowspan="{{ $rowCount }}" class="text-l">{!! nl2br(e($sub['solusi'])) !!}</td>
                        </tr>

                        @for($i = 1; $i < $rowCount; $i++)
                            @php $extraBf = $sub['bukti_list'][$i]; @endphp
                            <tr>
                                <td class="text-l">{{ $extraBf['nama_indikator'] }}</td>
                                <td class="text-l">{{ $extraBf['target'] }}</td>
                                <td class="text-r">{{ $extraBf['realisasi'] }}</td>
                                <td class="text-r">{{ $extraBf['capaian'] }}</td>
                                <td class="text-r">{{ $extraBf['target_akumulasi'] }}</td>
                                <td class="text-r">{{ $extraBf['realisasi_akumulasi'] }}</td>
                            </tr>
                        @endfor
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
