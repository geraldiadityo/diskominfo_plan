<?php

namespace Database\Seeders;

use App\Models\KategoriIndikator;
use App\Models\MasterIndikator;
use App\Models\SatuanIndikator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndikatorPenyelenggaraanSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/indikator_penyelenggaraan_pemda.json');

        if (!file_exists($file)) {
            $this->command->error("File tidak ditemukan: {$file}");
            return;
        }

        $data = json_decode(file_get_contents($file), true);

        if (!$data) {
            $this->command->error("Gagal membaca JSON.");
            return;
        }

        $this->command->info("Memulai seeder Indikator Penyelenggaraan Pemda...");

        // 1. Upsert kategori "Penyelenggaraan"
        $kategori = KategoriIndikator::updateOrCreate(
            ['nama_kategori' => 'Penyelenggaraan'],
            []
        );

        $totalParent = 0;
        $totalChild  = 0;

        DB::transaction(function () use ($data, $kategori, &$totalParent, &$totalChild) {
            foreach ($data as $item) {
                $namaParent = trim($item['indikator_master'] ?? '');
                if ($namaParent === '') {
                    continue;
                }

                // 2. Upsert parent (tanpa satuan, is_measurable = false)
                $parent = MasterIndikator::updateOrCreate(
                    [
                        'nama_indikator' => $namaParent,
                        'kategori_id'    => $kategori->id,
                        'parent_id'      => null,
                    ],
                    [
                        'satuan_id'     => null,
                        'is_measurable' => false,
                        'skpd_id'       => null,
                    ]
                );

                $totalParent++;

                // 3. Upsert setiap child
                if (!empty($item['indikator_anak']) && is_array($item['indikator_anak'])) {
                    foreach ($item['indikator_anak'] as $anak) {
                        $namaAnak = trim($anak['indikator'] ?? '');
                        if ($namaAnak === '') {
                            continue;
                        }

                        $namaSatuan = isset($anak['satuan']) && trim($anak['satuan']) !== '' ? trim($anak['satuan']) : null;

                        // Upsert satuan jika ada
                        $satuanId = null;
                        if ($namaSatuan) {
                            $satuan = SatuanIndikator::updateOrCreate(
                                ['nama_satuan' => $namaSatuan],
                                []
                            );
                            $satuanId = $satuan->id;
                        }

                        // Upsert child
                        MasterIndikator::updateOrCreate(
                            [
                                'nama_indikator' => $namaAnak,
                                'parent_id'      => $parent->id,
                                'kategori_id'    => $kategori->id,
                            ],
                            [
                                'satuan_id'     => $satuanId,
                                'is_measurable' => true,
                                'skpd_id'       => null,
                            ]
                        );

                        $totalChild++;
                    }
                }
            }
        });

        $this->command->info("Selesai! {$totalParent} parent dan {$totalChild} child berhasil di-seed.");
    }
}

