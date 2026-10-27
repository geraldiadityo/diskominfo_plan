<?php

namespace Database\Seeders;

use App\Models\ProgramSubKegiatan;
use App\Models\ProgulIndikator;
use App\Models\ProgulIndikatorUrusan;
use App\Models\ProgulProgram;
use App\Models\ProgulRealisasi;
use App\Models\RenjaSkpd;
use App\Models\Skpd;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgulSeeder extends Seeder
{
    protected array $skpdKeywords = [
        'perdagangan' => 'Dinas Perindustrian dan Perdagangan',
        'perindustrian' => 'Dinas Perindustrian dan Perdagangan',
        'koperasi' => 'Dinas Koperasi Usaha Kecil Menengah dan Tenaga Kerja',
        'umkm' => 'Dinas Koperasi Usaha Kecil Menengah dan Tenaga Kerja',
        'tenaga kerja' => 'Dinas Koperasi Usaha Kecil Menengah dan Tenaga Kerja',
        'pariwisata' => 'Dinas Kebudayaan, Pariwisata, Kepemudaan dan Olahraga',
        'kepemudaan' => 'Dinas Kebudayaan, Pariwisata, Kepemudaan dan Olahraga',
        'kebudayaan' => 'Dinas Kebudayaan, Pariwisata, Kepemudaan dan Olahraga',
        'pertanian' => 'Dinas Pertanian Ketahanan Pangan dan Perikanan',
        'perikanan' => 'Dinas Pertanian Ketahanan Pangan dan Perikanan',
        'ketahanan pangan' => 'Dinas Pertanian Ketahanan Pangan dan Perikanan',
        'pendidikan' => 'Dinas Pendidikan',
        'kesejahteraan rakyat' => 'Sekretariat Daerah',
        'pemberdayaan masyarakat' => 'Dinas Pemberdayaan Masyarakat dan Desa',
        'desa' => 'Dinas Pemberdayaan Masyarakat dan Desa',
        'kesehatan' => 'Dinas Kesehatan',
        'lingkungan hidup' => 'Dinas Lingkungan Hidup',
        'pekerjaan umum' => 'Dinas Pekerjaan Umum dan Perumahan Rakyat',
        'penataan ruang' => 'Dinas Pekerjaan Umum dan Perumahan Rakyat',
        'perumahan' => 'Dinas Pekerjaan Umum dan Perumahan Rakyat',
        'permukiman' => 'Dinas Pekerjaan Umum dan Perumahan Rakyat',
        'perhubungan' => 'Dinas Perhubungan',
        'komunikasi' => 'DInas Komunikasi, Infomatika Dan Statistik',
        'informatika' => 'DInas Komunikasi, Infomatika Dan Statistik',
        'statistik' => 'DInas Komunikasi, Infomatika Dan Statistik',
        'sekretariat daerah' => 'Sekretariat Daerah',
        'pengendalian penduduk' => 'Dinas Pemberdayaan Perempuan Perlindungan Anak dan Pengendalian Penduduk KB',
        'keluarga berencana' => 'Dinas Pemberdayaan Perempuan Perlindungan Anak dan Pengendalian Penduduk KB',
        'perempuan' => 'Dinas Pemberdayaan Perempuan Perlindungan Anak dan Pengendalian Penduduk KB',
        'perlindungan anak' => 'Dinas Pemberdayaan Perempuan Perlindungan Anak dan Pengendalian Penduduk KB',
        'penanaman modal' => 'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu',
        'ptsp' => 'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu',
        'pendapatan daerah' => 'Badan Pendapatan Daerah',
        'pad' => 'Badan Pendapatan Daerah',
    ];

    protected function resolveSkpd(?string $namaSkpd): ?Skpd
    {
        if (empty($namaSkpd)) {
            return null;
        }

        $sLower = strtolower(trim($namaSkpd));

        // 1. Direct search by keyword
        foreach ($this->skpdKeywords as $keyword => $targetSkpd) {
            if (str_contains($sLower, $keyword)) {
                $skpd = Skpd::where('nama_skpd', 'LIKE', '%' . $targetSkpd . '%')->first();
                if ($skpd) {
                    return $skpd;
                }
            }
        }

        // 2. Direct search by string
        return Skpd::where('nama_skpd', 'LIKE', '%' . trim($namaSkpd) . '%')->first();
    }

    protected function resolveSubKegiatan(?string $kodeRekening, string $namaSubKegiatan): ?ProgramSubKegiatan
    {
        // 1. Try by exact kode_lengkap if provided
        if (!empty($kodeRekening)) {
            $sub = ProgramSubKegiatan::where('kode_lengkap', trim($kodeRekening))->first();
            if ($sub) {
                return $sub;
            }
        }

        $clean = trim(preg_replace('/^[0-9]+(\.[0-9]+)*\.?\s*/', '', $namaSubKegiatan));
        $clean = preg_replace('/\s+/', ' ', $clean);

        if (empty($clean) || strlen($clean) < 4) {
            return null;
        }

        // 2. Exact nomenklatur match
        $sub = ProgramSubKegiatan::where('nomenklatur', $clean)->first();
        if ($sub) {
            return $sub;
        }

        // 3. Normalized without spaces
        $sub = ProgramSubKegiatan::whereRaw('LOWER(REPLACE(nomenklatur, " ", "")) = ?', [strtolower(str_replace(' ', '', $clean))])->first();
        if ($sub) {
            return $sub;
        }

        // 4. Keyword search
        $words = explode(' ', $clean);
        $filteredWords = array_values(array_filter($words, fn($w) => strlen($w) > 3 && !in_array(strtolower($w), ['yang', 'pada', 'dalam', 'dan', 'atau', 'untuk', 'kabupaten/kota', 'daerah'])));

        if (count($filteredWords) >= 2) {
            $q = ProgramSubKegiatan::query();
            foreach (array_slice($filteredWords, 0, 4) as $w) {
                $q->where('nomenklatur', 'like', "%{$w}%");
            }
            $sub = $q->first();
            if ($sub) {
                return $sub;
            }
        }

        return null;
    }

    public function run(): void
    {
        $file = database_path('seeders/data/data_progul.json');

        if (!file_exists($file)) {
            $this->command->error("File tidak ditemukan: {$file}");
            return;
        }

        $data = json_decode(file_get_contents($file), true);

        if (!$data) {
            $this->command->error("Gagal membaca JSON data_progul.json");
            return;
        }

        $this->command->info("Memulai seeder Program Prioritas / Unggulan (Level Sub Kegiatan & Triwulan)...");

        $totalSubKegiatan = 0;
        $totalRealisasi = 0;

        DB::transaction(function () use ($data, &$totalSubKegiatan, &$totalRealisasi) {
            // Remove any legacy indicators that don't have triwulan realisasis
            ProgulIndikator::doesntHave('realisasis')->delete();

            foreach ($data as $urutanProg => $item) {
                $namaProgram = trim($item['program_unggulan'] ?? '');
                if ($namaProgram === '') {
                    continue;
                }

                // Upsert Program Unggulan
                $program = ProgulProgram::updateOrCreate(
                    ['nama_program' => $namaProgram],
                    ['urutan' => $urutanProg + 1]
                );

                $this->command->line("  ✓ Program Unggulan: {$namaProgram}");

                $subList = $item['sub_kegiatan'] ?? [];

                foreach ($subList as $urutanSub => $sub) {
                    $namaSubKeg = trim($sub['nama_sub_kegiatan'] ?? '');
                    if ($namaSubKeg === '') {
                        continue;
                    }

                    $subKegiatan = $this->resolveSubKegiatan($sub['kode_rekening'] ?? null, $namaSubKeg);
                    $skpd = $this->resolveSkpd($sub['penanggung_jawab'] ?? null);

                    // Upsert Indikator (Level Sub Kegiatan)
                    $indikator = ProgulIndikator::updateOrCreate(
                        [
                            'program_id'     => $program->id,
                            'nama_indikator' => $subKegiatan ? $subKegiatan->nomenklatur : $namaSubKeg,
                        ],
                        [
                            'sub_kegiatan_id' => $subKegiatan?->id,
                            'rincian'         => $sub['rincian'] ?? null,
                            'detail'          => $sub['penjelasan'] ?? null,
                            'urutan'          => $urutanSub + 1,
                        ]
                    );

                    // Sync Urusan
                    ProgulIndikatorUrusan::where('indikator_id', $indikator->id)->delete();
                    if (!empty($sub['penanggung_jawab'])) {
                        ProgulIndikatorUrusan::create([
                            'indikator_id' => $indikator->id,
                            'nama_urusan'  => $sub['penanggung_jawab'],
                        ]);
                    }

                    // Seed Realisasi per Tahun & Triwulan
                    $realisasiTahunan = $sub['realisasi_tahunan'] ?? [];

                    foreach ($realisasiTahunan as $tahunan) {
                        $tahun = (int) ($tahunan['tahun'] ?? 2025);

                        // Check if RenjaSkpd exists for this sub_kegiatan & tahun
                        $renja = null;
                        if ($subKegiatan) {
                            $renjaQuery = RenjaSkpd::with('realisasi_kegiatans')
                                ->where('sub_kegiatan_id', $subKegiatan->id)
                                ->where('tahun', $tahun);

                            if ($skpd) {
                                $renjaQuery->where('skpd_id', $skpd->id);
                            }
                            $renja = $renjaQuery->first();
                        }

                        $triwulanList = $tahunan['triwulan'] ?? [];

                        foreach ($triwulanList as $tw) {
                            $twNum = (int) ($tw['triwulan'] ?? 1);

                            // Default values from JSON (null if not in Renja)
                            $targetFisik = $tw['target_fisik'] ?? null;
                            $targetKeuangan = $tw['target_keuangan'] ?? null;
                            $realisasiFisik = $tw['realisasi_fisik'] ?? null;
                            $realisasiKeuangan = $tw['realisasi_keuangan'] ?? null;
                            $status = 'draf';

                            // If RenjaSkpd has realisasi for this triwulan, sync from Renja
                            if ($renja && $renja->realisasi_kegiatans) {
                                $rk = $renja->realisasi_kegiatans->firstWhere('triwulan', $twNum);
                                if ($rk) {
                                    if ($rk->target_fisik !== null) $targetFisik = (float) $rk->target_fisik;
                                    if ($rk->target_keuangan !== null) $targetKeuangan = (float) $rk->target_keuangan;
                                    if ($rk->realisasi_fisik !== null) $realisasiFisik = (float) $rk->realisasi_fisik;
                                    if ($rk->realisasi_keuangan !== null) $realisasiKeuangan = (float) $rk->realisasi_keuangan;
                                    if ($rk->is_verified) $status = 'disetujui';
                                }
                            }

                            ProgulRealisasi::updateOrCreate(
                                [
                                    'indikator_id' => $indikator->id,
                                    'skpd_id'      => $skpd?->id,
                                    'tahun'        => $tahun,
                                    'triwulan'     => $twNum,
                                ],
                                [
                                    'target_fisik'       => $targetFisik,
                                    'target_keuangan'    => $targetKeuangan,
                                    'realisasi_fisik'    => $realisasiFisik,
                                    'realisasi_keuangan' => $realisasiKeuangan,
                                    'status'             => $status,
                                ]
                            );

                            $totalRealisasi++;
                        }
                    }

                    $totalSubKegiatan++;
                }
            }
        });

        $totalProgram = ProgulProgram::count();
        $totalIndikator = ProgulIndikator::count();

        $this->command->info("Selesai! {$totalProgram} program unggulan, {$totalIndikator} sub-kegiatan indikator, dan {$totalRealisasi} data triwulan berhasil di-seed.");
    }
}
