<x-filament-panels::page>
    <style>
        /* Toolbar Filter & Action Bar - Explicit CSS (Aman untuk Light & Dark Mode) */
        .lr-toolbar {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
            font-family: Arial, Calibri, sans-serif;
        }

        .lr-toolbar-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
            justify-content: space-between;
        }

        .lr-filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
            flex: 1 1 auto;
        }

        .lr-field {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }

        .lr-field-skpd {
            display: flex;
            flex-direction: column;
            min-width: 280px;
            flex: 1 1 280px;
            max-width: 460px;
        }

        .lr-label {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b !important;
            margin-bottom: 6px;
            display: block;
        }

        .lr-select,
        .lr-input {
            background-color: #f8fafc !important;
            color: #0f172a !important;
            border: 1px solid #94a3b8 !important;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
            width: 100%;
            outline: none;
            box-sizing: border-box;
        }

        .lr-select:focus {
            border-color: #2563eb !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
        }

        .lr-input[disabled] {
            background-color: #e2e8f0 !important;
            color: #334155 !important;
            cursor: not-allowed;
        }

        .lr-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .lr-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.15s ease, transform 0.1s ease;
            white-space: nowrap;
        }

        .lr-btn:hover {
            opacity: 0.92;
        }

        .lr-btn:active {
            transform: translateY(1px);
        }

        .lr-btn-excel {
            background-color: #15803d !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px rgba(21, 128, 61, 0.3);
        }

        .lr-btn-print {
            background-color: #1e293b !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.3);
        }

        .lr-btn svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        /* Container Lembar Kerja Excel */
        .excel-sheet-container {
            background-color: #ffffff !important;
            color: #000000 !important;
            border: 1px solid #94a3b8;
            border-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .excel-sheet-body {
            padding: 18px 14px 20px 14px;
            overflow-x: auto;
            background-color: #ffffff !important;
        }

        .excel-title-block {
            text-align: center;
            margin-bottom: 14px;
            font-family: Arial, Calibri, sans-serif;
            color: #000000 !important;
        }

        .excel-title-block h2 {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.35;
            color: #000000 !important;
        }

        .excel-title-block h3 {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.35;
            color: #000000 !important;
        }

        /* Tabel Matriks 18 Kolom */
        .excel-table {
            border-collapse: collapse !important;
            width: 100%;
            min-width: 1550px;
            font-family: Arial, Calibri, sans-serif;
            font-size: 11px;
            color: #000000 !important;
            background-color: #ffffff !important;
            table-layout: fixed;
        }

        .excel-table th,
        .excel-table td {
            border: 1px solid #000000 !important;
            padding: 4px 6px !important;
            line-height: 1.3;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
            color: #000000 !important;
        }

        .excel-table thead th {
            background-color: #ffffff !important;
            color: #000000 !important;
            text-align: center;
            font-weight: 700;
            vertical-align: middle;
        }

        .excel-col-num th {
            background-color: #ffffff !important;
            font-size: 10px;
            font-weight: 400 !important;
            padding: 2px 4px !important;
            text-align: center;
        }

        .row-program td {
            background-color: #d9ead3 !important;
            font-weight: 700 !important;
            color: #000000 !important;
        }

        .row-kegiatan td {
            background-color: #bfbfbf !important;
            font-weight: 700 !important;
            color: #000000 !important;
        }

        .row-sub td {
            background-color: #ffffff !important;
            color: #000000 !important;
        }

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .valign-middle { vertical-align: middle !important; }
        .valign-top { vertical-align: top !important; }

        /* Bottom Excel Sheet Tabs */
        .excel-tabs-bar {
            display: flex;
            align-items: center;
            background-color: #e2e8f0 !important;
            border-top: 1px solid #94a3b8;
            padding: 0 10px;
            gap: 4px;
            overflow-x: auto;
            font-family: Arial, Calibri, sans-serif;
            font-size: 12px;
        }

        .excel-tab-btn {
            padding: 7px 16px;
            background-color: #cbd5e1 !important;
            color: #1e293b !important;
            border: 1px solid #94a3b8;
            border-top: none;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
        }

        .excel-tab-btn:hover {
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }

        .excel-tab-btn.active {
            background-color: #ffffff !important;
            color: #15803d !important;
            border-bottom: 3px solid #16a34a !important;
            font-weight: 700 !important;
        }

        .bukti-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 9px;
            padding: 1px 5px;
            border-radius: 4px;
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
            border: 1px solid #bfdbfe !important;
            text-decoration: none;
            margin-left: 4px;
            font-weight: 700;
        }

        .bukti-badge:hover {
            background-color: #dbeafe !important;
        }

        @media print {
            body, html {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print,
            .fi-topbar,
            .fi-sidebar,
            .fi-header,
            .lr-toolbar,
            .excel-tabs-bar {
                display: none !important;
            }
            .excel-sheet-container {
                border: none !important;
                box-shadow: none !important;
            }
            .excel-sheet-body {
                padding: 0 !important;
                overflow: visible !important;
            }
            .excel-table {
                min-width: 100% !important;
                font-size: 8.5px !important;
            }
            .excel-table th,
            .excel-table td {
                padding: 2px 3px !important;
            }
            .row-program td {
                background-color: #d9ead3 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .row-kegiatan td {
                background-color: #bfbfbf !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: landscape;
                margin: 8mm;
            }
        }
    </style>

    @php
        $triwulanRomawi = ['I', 'II', 'III', 'IV'][$selectedTriwulan - 1] ?? $selectedTriwulan;
        $activeSkpdName = $this->activeSkpd?->nama_skpd ?? 'PERANGKAT DAERAH';
        $data = $this->getLaporanData();
    @endphp

    {{-- Toolbar Filter & Tombol Download / Cetak --}}
    <div class="lr-toolbar no-print">
        <div class="lr-toolbar-grid">
            <div class="lr-filter-group">
                {{-- Filter Tahun --}}
                <div class="lr-field">
                    <label class="lr-label">Tahun Anggaran</label>
                    <select wire:model.live="selectedTahun" class="lr-select">
                        @foreach(range(date('Y') - 2, date('Y') + 4) as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Pilih Triwulan --}}
                <div class="lr-field">
                    <label class="lr-label">Pilih Triwulan</label>
                    <select wire:model.live="selectedTriwulan" class="lr-select">
                        <option value="1">Triwulan I</option>
                        <option value="2">Triwulan II</option>
                        <option value="3">Triwulan III</option>
                        <option value="4">Triwulan IV</option>
                    </select>
                </div>

                {{-- Filter SKPD --}}
                <div class="lr-field-skpd">
                    <label class="lr-label">Perangkat Daerah (SKPD)</label>
                    @if(auth()->user()->role === 'SKPD')
                        <input type="text" value="{{ $activeSkpdName }}" disabled class="lr-input">
                    @else
                        <select wire:model.live="selectedSkpdId" class="lr-select">
                            @foreach($this->skpdList as $id => $nama)
                                <option value="{{ $id }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            {{-- Tombol Download Excel & Cetak PDF --}}
            <div class="lr-actions">
                <button wire:click="downloadExcel" type="button" class="lr-btn lr-btn-excel">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    <span>Download Excel</span>
                </button>
                <button onclick="window.print()" type="button" class="lr-btn lr-btn-print">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Cetak / PDF</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Container Lembar Kerja Excel --}}
    <div class="excel-sheet-container">
        <div class="excel-sheet-body">
            {{-- Kop Laporan 2 Baris Persis Gambar --}}
            <div class="excel-title-block">
                <h2>LAPORAN REALISASI FISIK DAN KEUANGAN TRIWULAN {{ $triwulanRomawi }}</h2>
                <h3>{{ strtoupper($activeSkpdName) }}</h3>
            </div>

            {{-- Tabel 18 Kolom --}}
            <table class="excel-table">
                <colgroup>
                    <col style="width: 190px;"> {{-- 1. Program, Kegiatan dan Sub Kegiatan --}}
                    <col style="width: 145px;"> {{-- 2. Indikator Kinerja --}}
                    <col style="width: 65px;">  {{-- 3. Satuan --}}
                    <col style="width: 105px;"> {{-- 4. Pagu (Rp) --}}
                    <col style="width: 185px;"> {{-- 5. Indikator (Fisik) --}}
                    <col style="width: 65px;">  {{-- 6. Target (Fisik) --}}
                    <col style="width: 58px;">  {{-- 7. Realisasi (Fisik) --}}
                    <col style="width: 55px;">  {{-- 8. Capaian (%) --}}
                    <col style="width: 62px;">  {{-- 9. Tingkat Capaian (%) --}}
                    <col style="width: 55px;">  {{-- 10. Akumulasi Target (%) --}}
                    <col style="width: 55px;">  {{-- 11. Akumulasi Realisasi (%) --}}
                    <col style="width: 100px;"> {{-- 12. Target (Rp) --}}
                    <col style="width: 100px;"> {{-- 13. Realisasi (Rp) --}}
                    <col style="width: 62px;">  {{-- 14. Tingkat Capaian (%) Keu --}}
                    <col style="width: 55px;">  {{-- 15. Akumulasi Target (%) Keu --}}
                    <col style="width: 58px;">  {{-- 16. Akumulasi Realisasi (%) Keu --}}
                    <col style="width: 140px;"> {{-- 17. Permasalahan --}}
                    <col style="width: 140px;"> {{-- 18. Upaya mengatasi permasalahan --}}
                </colgroup>
                <thead>
                    {{-- Header Row 1 --}}
                    <tr>
                        <th rowspan="4">Program, Kegiatan dan Sub Kegiatan</th>
                        <th rowspan="4">Indikator Kinerja</th>
                        <th rowspan="4">Satuan</th>
                        <th rowspan="4">Pagu (Rp)</th>
                        <th colspan="14">Triwulan {{ $selectedTriwulan }}</th>
                    </tr>

                    {{-- Header Row 2 --}}
                    <tr>
                        <th colspan="7">Kinerja Fisik</th>
                        <th colspan="5">Keuangan</th>
                        <th rowspan="3">Permasalahan</th>
                        <th rowspan="3">Upaya mengatasi permasalahan</th>
                    </tr>

                    {{-- Header Row 3 --}}
                    <tr>
                        <th rowspan="2">Indikator</th>
                        <th rowspan="2">Target</th>
                        <th colspan="3">Realisasi</th>
                        <th colspan="2">Akumulasi Tahunan</th>
                        <th rowspan="2">Target (Rp)</th>
                        <th rowspan="2">Realisasi (Rp)</th>
                        <th rowspan="2">Tingkat Capaian (%)</th>
                        <th colspan="2">Akumulasi Tahunan</th>
                    </tr>

                    {{-- Header Row 4 --}}
                    <tr>
                        <th>Realisasi</th>
                        <th>Capaian (%)</th>
                        <th>Tingkat Capaian (%)</th>
                        <th>Target (%)</th>
                        <th>Realisasi (%)</th>
                        <th>Target (%)</th>
                        <th>Realisasi (%)</th>
                    </tr>

                    {{-- Header Row 5: Nomor Kolom 1 - 18 --}}
                    <tr class="excel-col-num">
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                        <th>6</th>
                        <th>7</th>
                        <th>8</th>
                        <th>9</th>
                        <th>10</th>
                        <th>11</th>
                        <th>12</th>
                        <th>13</th>
                        <th>14</th>
                        <th>15</th>
                        <th>16</th>
                        <th>17</th>
                        <th>18</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $prog)
                        {{-- ================= BARIS PROGRAM (HIJAU) ================= --}}
                        @php
                            $tcKeuProg = $prog['target_keuangan_tw'] > 0 ? round(($prog['realisasi_keuangan_tw'] / $prog['target_keuangan_tw']) * 100, 2) : 0;
                            $akTgKeuProg = $prog['pagu'] > 0 ? round(($prog['target_keuangan_kum'] / $prog['pagu']) * 100, 0) : 0;
                            $akRlKeuProg = $prog['pagu'] > 0 ? round($prog['realisasi_keuangan_kum'] / $prog['pagu'], 2) : 0;
                            $tcFisProg = $prog['sub_count'] > 0 ? round($prog['tingkat_capaian_fisik_sum'] / $prog['sub_count'], 0) : 0;
                            $akTgFisProg = $prog['sub_count'] > 0 ? round($prog['target_fisik_kum_sum'] / $prog['sub_count'], 0) : 0;
                            $akRlFisProg = $prog['sub_count'] > 0 ? round($prog['realisasi_fisik_kum_sum'] / $prog['sub_count'], 0) : 0;
                        @endphp
                        <tr class="row-program">
                            <td class="text-left">{{ $prog['nama'] }}</td>
                            <td></td>
                            <td></td>
                            <td class="text-right">{{ number_format($prog['pagu'], 2, '.', ',') }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-center">{{ $tcFisProg }}%</td>
                            <td class="text-center">{{ $akTgFisProg }}%</td>
                            <td class="text-center">{{ $akRlFisProg }}%</td>
                            <td class="text-right">{{ number_format($prog['target_keuangan_tw'], 2, '.', ',') }}</td>
                            <td class="text-right">{{ number_format($prog['realisasi_keuangan_tw'], 2, '.', ',') }}</td>
                            <td class="text-center">{{ number_format($tcKeuProg, 2, '.', ',') }}%</td>
                            <td class="text-center">{{ $akTgKeuProg }}%</td>
                            <td class="text-right">{{ number_format($akRlKeuProg, 2, '.', ',') }}</td>
                            <td></td>
                            <td></td>
                        </tr>

                        {{-- ================= BARIS KEGIATAN (ABU-ABU) ================= --}}
                        @foreach($prog['kegiatans'] as $keg)
                            @php
                                $tcKeuKeg = $keg['target_keuangan_tw'] > 0 ? round(($keg['realisasi_keuangan_tw'] / $keg['target_keuangan_tw']) * 100, 2) : 0;
                                $akTgKeuKeg = $keg['pagu'] > 0 ? round(($keg['target_keuangan_kum'] / $keg['pagu']) * 100, 0) : 0;
                                $akRlKeuKeg = $keg['pagu'] > 0 ? round($keg['realisasi_keuangan_kum'] / $keg['pagu'], 2) : 0;
                                $tcFisKeg = $keg['sub_count'] > 0 ? round($keg['tingkat_capaian_fisik_sum'] / $keg['sub_count'], 0) : 0;
                                $akTgFisKeg = $keg['sub_count'] > 0 ? round($keg['target_fisik_kum_sum'] / $keg['sub_count'], 0) : 0;
                                $akRlFisKeg = $keg['sub_count'] > 0 ? round($keg['realisasi_fisik_kum_sum'] / $keg['sub_count'], 0) : 0;
                            @endphp
                            <tr class="row-kegiatan">
                                <td class="text-left">{{ $keg['nama'] }}</td>
                                <td></td>
                                <td></td>
                                <td class="text-right">{{ number_format($keg['pagu'], 2, '.', ',') }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-center">{{ $tcFisKeg }}%</td>
                                <td class="text-center">{{ $akTgFisKeg }}%</td>
                                <td class="text-center">{{ $akRlFisKeg }}%</td>
                                <td class="text-right">{{ number_format($keg['target_keuangan_tw'], 2, '.', ',') }}</td>
                                <td class="text-right">{{ number_format($keg['realisasi_keuangan_tw'], 2, '.', ',') }}</td>
                                <td class="text-center">{{ number_format($tcKeuKeg, 2, '.', ',') }}%</td>
                                <td class="text-center">{{ $akTgKeuKeg }}%</td>
                                <td class="text-right">{{ number_format($akRlKeuKeg, 2, '.', ',') }}</td>
                                <td></td>
                                <td></td>
                            </tr>

                            {{-- ================= BARIS SUB KEGIATAN & INDIKATOR BUKTI FISIK ================= --}}
                            @foreach($keg['sub_kegiatans'] as $sub)
                                @php
                                    $rowCount = count($sub['bukti_list']);
                                    $firstBf = $sub['bukti_list'][0];
                                @endphp
                                <tr class="row-sub">
                                    {{-- Kolom 1-4 (Sub Kegiatan, Indikator Kinerja, Satuan, Pagu) di-rowspan --}}
                                    <td rowspan="{{ $rowCount }}" class="text-center valign-middle">
                                        {{ $sub['nama'] }}
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="text-center valign-middle">
                                        {{ $sub['indikator_kinerja'] }}
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="text-center valign-middle">
                                        {{ $sub['satuan'] }}
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="text-right valign-middle">
                                        {{ number_format($sub['pagu'], 2, '.', ',') }}
                                    </td>

                                    {{-- Kolom 5-8: Indikator Bukti Fisik Pertama (Hanya dari Triwulan Terpilih) --}}
                                    <td class="text-left">
                                        <span>{{ $firstBf['nama_indikator'] }}</span>
                                        @if(!empty($firstBf['file_bukti']))
                                            <a href="{{ $firstBf['file_bukti'] }}" target="_blank" class="bukti-badge no-print" title="Lihat Dokumen Bukti Fisik">
                                                Bukti PDF
                                            </a>
                                        @endif
                                    </td>
                                    <td class="text-left">{{ $firstBf['target'] }}</td>
                                    <td class="text-right">{{ $firstBf['realisasi'] }}</td>
                                    <td class="text-right">{{ $firstBf['capaian'] }}</td>

                                    {{-- Kolom 9: Tingkat Capaian Fisik (%) di-rowspan per Sub Kegiatan --}}
                                    <td rowspan="{{ $rowCount }}" class="text-center valign-middle">
                                        {{ $sub['has_bukti'] || $sub['tingkat_capaian_fisik'] > 0 ? $sub['tingkat_capaian_fisik'] . '%' : '' }}
                                    </td>

                                    {{-- Kolom 10-11: Akumulasi Tahunan Fisik --}}
                                    <td class="text-right">{{ $firstBf['target_akumulasi'] }}</td>
                                    <td class="text-right">{{ $firstBf['realisasi_akumulasi'] }}</td>

                                    {{-- Kolom 12-16: Keuangan di-rowspan per Sub Kegiatan --}}
                                    <td rowspan="{{ $rowCount }}" class="text-right valign-middle">
                                        {{ number_format($sub['target_keuangan_tw'], 2, '.', ',') }}
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="text-right valign-middle">
                                        {{ number_format($sub['realisasi_keuangan_tw'], 2, '.', ',') }}
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="text-center valign-middle">
                                        {{ number_format($sub['tingkat_capaian_keu'], 2, '.', ',') }}%
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="text-center valign-middle">
                                        {{ $sub['persen_target_keu_kum'] }}%
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="text-right valign-middle">
                                        {{ $sub['rasio_real_keu_kum'] }}
                                    </td>

                                    {{-- Kolom 17-18: Permasalahan & Solusi di-rowspan --}}
                                    <td rowspan="{{ $rowCount }}" class="text-left valign-middle">
                                        {!! nl2br(e($sub['alasan'])) !!}
                                    </td>
                                    <td rowspan="{{ $rowCount }}" class="text-left valign-middle">
                                        {!! nl2br(e($sub['solusi'])) !!}
                                    </td>
                                </tr>

                                {{-- Baris Indikator Bukti Fisik ke-2 dst. pada Triwulan Terpilih --}}
                                @for($i = 1; $i < $rowCount; $i++)
                                    @php $extraBf = $sub['bukti_list'][$i]; @endphp
                                    <tr class="row-sub">
                                        <td class="text-left">
                                            <span>{{ $extraBf['nama_indikator'] }}</span>
                                            @if(!empty($extraBf['file_bukti']))
                                                <a href="{{ $extraBf['file_bukti'] }}" target="_blank" class="bukti-badge no-print" title="Lihat Dokumen Bukti Fisik">
                                                    Bukti PDF
                                                </a>
                                            @endif
                                        </td>
                                        <td class="text-left">{{ $extraBf['target'] }}</td>
                                        <td class="text-right">{{ $extraBf['realisasi'] }}</td>
                                        <td class="text-right">{{ $extraBf['capaian'] }}</td>
                                        <td class="text-right">{{ $extraBf['target_akumulasi'] }}</td>
                                        <td class="text-right">{{ $extraBf['realisasi_akumulasi'] }}</td>
                                    </tr>
                                @endfor
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="18" class="text-center" style="padding: 24px !important; color: #64748b !important; font-style: italic;">
                                Belum ada data Renja atau Realisasi untuk SKPD dan Tahun Anggaran terpilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tab Sheet Bawah ala Excel untuk Ganti Triwulan --}}
        <div class="excel-tabs-bar no-print">
            @foreach([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'] as $twNum => $twRom)
                <button
                    type="button"
                    wire:click="selectTriwulan({{ $twNum }})"
                    class="excel-tab-btn {{ (int) $selectedTriwulan === $twNum ? 'active' : '' }}"
                >
                    Evaluasi Triwulan {{ $twRom }}
                </button>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
