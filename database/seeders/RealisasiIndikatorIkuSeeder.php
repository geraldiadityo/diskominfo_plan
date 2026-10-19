<?php

namespace Database\Seeders;

use App\Enums\StatusRealisasi;
use App\Models\IndikatorRealisasi;
use App\Models\IndikatorTarget;
use App\Models\KategoriIndikator;
use App\Models\MasterIndikator;
use App\Models\SatuanIndikator;
use App\Models\Skpd;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealisasiIndikatorIkuSeeder extends Seeder
{
    const TAHUN_RANGE = [2025, 2026, 2027, 2028, 2029, 2030];

    /**
     * Pemetaan nama SKPD di file JSON ke nama SKPD yang ada di database.
     */
    protected array $skpdAliases = [
        'Dinas Komunikasi, Informatika dan Statistik'  => 'DInas Komunikasi, Infomatika Dan Statistik',
        'Dinas Komunikasi, Informatika, dan Statistik' => 'DInas Komunikasi, Infomatika Dan Statistik',
        'Badan Penanggulangan Bencana Daerah (BPBD)'   => 'Badan Penanggulangan Bencana Daerah',
    ];

    public function run(): void
    {
        $file = database_path('seeders/data/realisasi_indikator_kinerja_utama.json');

        if (!file_exists($file)) {
            $this->command->error("File tidak ditemukan: {$file}");
            return;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!$data) {
            $this->command->error("Gagal membaca JSON.");
            return;
        }

        $this->command->info("Memulai seeder Realisasi Indikator Kinerja Utama (IKU)...");

        // Ambil atau buat kategori IKU
        $kategori = KategoriIndikator::firstOrCreate(
            ['nama_kategori' => 'IKU'],
            []
        );

        $totalMaster    = 0;
        $totalTarget    = 0;
        $totalRealisasi = 0;
        $skippedSkpd    = 0;

        DB::transaction(function () use ($data, $kategori, &$totalMaster, &$totalTarget, &$totalRealisasi, &$skippedSkpd) {
            foreach ($data as $item) {
                $namaSkpd = trim($item['skpd'] ?? '');
                $targetNamaSkpd = $this->skpdAliases[$namaSkpd] ?? $namaSkpd;

                // 1. Cari exact match di tabel skpds
                $skpd = Skpd::whereRaw('LOWER(TRIM(nama_skpd)) = ?', [strtolower($targetNamaSkpd)])->first();

                // 2. Fallback LIKE query jika belum ketemu
                if (!$skpd) {
                    $skpd = Skpd::where('nama_skpd', 'LIKE', '%' . $targetNamaSkpd . '%')->first();
                }

                if (!$skpd) {
                    $this->command->warn("SKPD tidak ditemukan: '{$namaSkpd}' — dilewati.");
                    $skippedSkpd++;
                    continue;
                }

                foreach ($item['indikator'] as $ind) {
                    $namaIndikator = trim($ind['nama_indikator'] ?? '');
                    if ($namaIndikator === '') {
                        continue;
                    }

                    $namaSatuan = isset($ind['satuan']) && trim($ind['satuan']) !== '' ? trim($ind['satuan']) : null;
                    $satuanId   = null;

                    if ($namaSatuan) {
                        $satuan = SatuanIndikator::firstOrCreate(
                            ['nama_satuan' => $namaSatuan],
                            []
                        );
                        $satuanId = $satuan->id;
                    }

                    // Upsert Master Indikator kategori IKU
                    $masterIndikator = MasterIndikator::updateOrCreate(
                        [
                            'nama_indikator' => $namaIndikator,
                            'kategori_id'    => $kategori->id,
                        ],
                        [
                            'satuan_id'     => $satuanId,
                            'skpd_id'       => $skpd->id,
                            'is_measurable' => true,
                            'parent_id'     => null,
                        ]
                    );

                    $totalMaster++;

                    // Upsert baseline 2024 jika tersedia di JSON
                    if (isset($ind['baseline_2024']) && $ind['baseline_2024'] !== null && $ind['baseline_2024'] !== '') {
                        $baselineVal = (string) $ind['baseline_2024'];
                        $masterIndikator->updateQuietly(['kondisi_awal' => $baselineVal]);

                        IndikatorRealisasi::updateOrCreate(
                            [
                                'indikator_id' => $masterIndikator->id,
                                'skpd_id'      => $skpd->id,
                                'tahun'        => 2024,
                            ],
                            [
                                'nilai_realisasi' => $baselineVal,
                                'status'          => StatusRealisasi::APPROVED,
                                'is_verif'        => true,
                                'is_tercapai'     => true,
                            ]
                        );
                    }

                    // Upsert target dan record realisasi per tahun
                    foreach (self::TAHUN_RANGE as $tahun) {
                        $nilaiTarget = $ind["target_{$tahun}"] ?? null;

                        if ($nilaiTarget !== null) {
                            IndikatorTarget::updateOrCreate(
                                [
                                    'indikator_id' => $masterIndikator->id,
                                    'tahun'        => $tahun,
                                ],
                                [
                                    'nilai_target' => (string) $nilaiTarget,
                                ]
                            );
                            $totalTarget++;
                        }

                        // Buat record realisasi awal agar SKPD memiliki daftar yang perlu diisi
                        $existingRealisasi = IndikatorRealisasi::where('indikator_id', $masterIndikator->id)
                            ->where('skpd_id', $skpd->id)
                            ->where('tahun', $tahun)
                            ->first();

                        if (!$existingRealisasi) {
                            IndikatorRealisasi::create([
                                'indikator_id'    => $masterIndikator->id,
                                'skpd_id'         => $skpd->id,
                                'tahun'           => $tahun,
                                'nilai_realisasi' => '-',
                                'status'          => StatusRealisasi::DRAF,
                                'is_tercapai'     => false,
                                'is_verif'        => false,
                            ]);
                            $totalRealisasi++;
                        }
                    }
                }
            }
        });

        $this->command->info("Selesai! {$totalMaster} Master Indikator, {$totalTarget} target, dan {$totalRealisasi} record realisasi baru berhasil di-seed.");
        if ($skippedSkpd > 0) {
            $this->command->warn("Dilewati: {$skippedSkpd} SKPD.");
        }
    }
}

