<?php

namespace Database\Seeders;

use App\Models\ProgulIndikator;
use App\Models\ProgulIndikatorUrusan;
use App\Models\ProgulProgram;
use App\Models\ProgulRealisasi;
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
        'ketentraman' => 'Satuan Polisi Pamong Praja dan Pemadam Kebakaran',
        'keuangan' => 'Badan Pendapatan Daerah',
        'pad' => 'Badan Pendapatan Daerah',
        'kepegawaian' => 'Badan Kepegawaian dan Pengembangan SDM',
    ];

    protected function resolveSkpd(array $urusanList): ?Skpd
    {
        foreach ($urusanList as $urusan) {
            $uLower = strtolower(trim($urusan));
            foreach ($this->skpdKeywords as $keyword => $targetSkpd) {
                if (str_contains($uLower, $keyword)) {
                    $skpd = Skpd::where('nama_skpd', 'LIKE', '%' . $targetSkpd . '%')->first();
                    if ($skpd) {
                        return $skpd;
                    }
                }
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

        $this->command->info("Memulai seeder Program Prioritas / Unggulan...");

        $years = [2025, 2026, 2027, 2028, 2029];
        $totalRealisasi = 0;

        DB::transaction(function () use ($data, $years, &$totalRealisasi) {
            foreach ($data as $urutan => $item) {
                $namaProgram = trim($item['program'] ?? '');
                if ($namaProgram === '') {
                    continue;
                }

                // Upsert Program
                $program = ProgulProgram::updateOrCreate(
                    ['nama_program' => $namaProgram],
                    ['urutan' => $urutan + 1]
                );

                $this->command->line("  ✓ Program: {$namaProgram}");

                foreach (($item['indikator_kinerja'] ?? []) as $urutanInd => $ind) {
                    $namaIndikator = trim($ind['indikator_kinerja'] ?? '');
                    $detail        = trim($ind['detail'] ?? '');
                    $urusanList    = $ind['urusan'] ?? [];

                    if ($namaIndikator === '') {
                        continue;
                    }

                    // Upsert Indikator
                    $indikator = ProgulIndikator::updateOrCreate(
                        [
                            'program_id'     => $program->id,
                            'nama_indikator' => $namaIndikator,
                        ],
                        [
                            'detail' => $detail ?: null,
                            'urutan' => $urutanInd + 1,
                        ]
                    );

                    // Sync Urusan
                    ProgulIndikatorUrusan::where('indikator_id', $indikator->id)->delete();

                    foreach ($urusanList as $namaUrusan) {
                        $namaUrusan = trim($namaUrusan);
                        if ($namaUrusan === '') {
                            continue;
                        }

                        ProgulIndikatorUrusan::create([
                            'indikator_id' => $indikator->id,
                            'nama_urusan'  => $namaUrusan,
                        ]);
                    }

                    // Resolve SKPD Penanggung Jawab
                    $skpd = $this->resolveSkpd($urusanList);

                    // Seed 5 Years Target & Realisasi
                    foreach ($years as $index => $year) {
                        $target = '100%';
                        $realisasi = $year === 2025 ? '100%' : null;
                        $status = $year === 2025 ? 'disetujui' : 'draf';

                        ProgulRealisasi::updateOrCreate(
                            [
                                'indikator_id' => $indikator->id,
                                'skpd_id'      => $skpd?->id,
                                'tahun'        => $year,
                            ],
                            [
                                'target_tahunan'  => $target,
                                'nilai_realisasi' => $realisasi,
                                'status'          => $status,
                            ]
                        );

                        $totalRealisasi++;
                    }

                    $this->command->line("    → Indikator: {$namaIndikator} (" . ($skpd?->nama_skpd ?? 'Lintas SKPD') . ")");
                }
            }
        });

        $totalProgram   = ProgulProgram::count();
        $totalIndikator = ProgulIndikator::count();

        $this->command->info("Selesai! {$totalProgram} program, {$totalIndikator} indikator, dan {$totalRealisasi} data tahunan berhasil di-seed.");
    }
}
