<?php

namespace Database\Seeders;

use App\Models\KategoriIndikator;
use App\Models\MasterIndikator;
use App\Models\SatuanIndikator;
use App\Models\Skpd;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndikatorIkuSeeder extends Seeder
{
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

        $this->command->info("Memulai seeder Master Indikator Kinerja Utama (IKU)...");

        // Upsert kategori "IKU"
        $kategori = KategoriIndikator::firstOrCreate(
            ['nama_kategori' => 'IKU'],
            []
        );

        $totalIndikator = 0;
        $skippedSkpd    = 0;

        DB::transaction(function () use ($data, $kategori, &$totalIndikator, &$skippedSkpd) {
            foreach ($data as $item) {
                $namaSkpd = trim($item['skpd'] ?? '');
                $targetNamaSkpd = $this->skpdAliases[$namaSkpd] ?? $namaSkpd;

                $skpd = Skpd::whereRaw('LOWER(TRIM(nama_skpd)) = ?', [strtolower($targetNamaSkpd)])->first();
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
                    $satuanId = null;

                    if ($namaSatuan) {
                        $satuan = SatuanIndikator::firstOrCreate(
                            ['nama_satuan' => $namaSatuan],
                            []
                        );
                        $satuanId = $satuan->id;
                    }

                    MasterIndikator::updateOrCreate(
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

                    $totalIndikator++;
                }
            }
        });

        $this->command->info("Selesai! {$totalIndikator} Master Indikator IKU berhasil di-seed.");
        if ($skippedSkpd > 0) {
            $this->command->warn("Dilewati: {$skippedSkpd} SKPD.");
        }
    }
}

