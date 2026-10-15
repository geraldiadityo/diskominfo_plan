<?php

namespace Database\Seeders;

use App\Enums\StatusRealisasi;
use App\Models\IndikatorRealisasi;
use App\Models\IndikatorTarget;
use App\Models\KategoriIndikator;
use App\Models\MasterIndikator;
use App\Models\Skpd;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealisasiIndikatorPenyelenggaraanSeeder extends Seeder
{
    const TAHUN_RANGE = [2025, 2026, 2027, 2028, 2029, 2030];

    /**
     * Pemetaan nama SKPD di file JSON ke nama SKPD yang ada di database.
     */
    protected array $skpdAliases = [
        'Dinas Pertanian, Ketahanan Pangan dan Perikanan' => 'Dinas Pertanian Ketahanan Pangan dan Perikanan',
        'Badan Penanggulangan Bencana Daerah (BPBD)' => 'Badan Penanggulangan Bencana Daerah',
        'Dinas Komunikasi, Informatika dan Statistik' => 'DInas Komunikasi, Infomatika Dan Statistik',
        'Dinas Koperasi, Usaha Kecil Menengah dan Tenaga Kerja' => 'Dinas Koperasi Usaha Kecil Menengah dan Tenaga Kerja',
        'Dinas Pemberdayaan Perempuan, Perlindungan Anak, Pengendalian Penduduk dan KB' => 'Dinas Pemberdayaan Perempuan Perlindungan Anak dan Pengendalian Penduduk KB',
        'Badan Perencanaan Pembangunan, Riset dan Inovasi Daerah' => 'BAPPERIDA',
        'Dinas Perdagangan dan Perindustrian' => 'Dinas Perindustrian dan Perdagangan',
        'Sekretariat DPRD' => 'Sekretaria DPRD',
    ];

    public function run(): void
    {
        $file = database_path('seeders/data/realisasi_indikator_penyelenggaraan.json');

        if (!file_exists($file)) {
            $this->command->error("File tidak ditemukan: {$file}");
            return;
        }

        $data = json_decode(file_get_contents($file), true);
        if (!$data) {
            $this->command->error("Gagal membaca JSON.");
            return;
        }

        $this->command->info("Memulai seeder Realisasi Indikator Penyelenggaraan...");

        // Ambil kategori Penyelenggaraan
        $kategori = KategoriIndikator::where('nama_kategori', 'Penyelenggaraan')->first();
        if (!$kategori) {
            $this->command->error("Kategori 'Penyelenggaraan' tidak ditemukan. Jalankan IndikatorPenyelenggaraanSeeder terlebih dahulu.");
            return;
        }

        $totalTarget    = 0;
        $totalRealisasi = 0;
        $skippedSkpd    = 0;
        $skippedInd     = 0;

        DB::transaction(function () use ($data, $kategori, &$totalTarget, &$totalRealisasi, &$skippedSkpd, &$skippedInd) {
            foreach ($data as $item) {
                $namaSkpd = trim($item['skpd'] ?? '');

                // Lewati "Seluruh Kecamatan" (bukan satu SKPD)
                if (strtolower($namaSkpd) === 'seluruh kecamatan') {
                    continue;
                }

                // Cek alias mapping terlebih dahulu
                $targetNamaSkpd = $this->skpdAliases[$namaSkpd] ?? $namaSkpd;

                // 1. Cari exact match di tabel skpds
                $skpd = Skpd::whereRaw('LOWER(TRIM(nama_skpd)) = ?', [strtolower($targetNamaSkpd)])->first();

                // 2. Fallback jika belum ketemu: coba LIKE query
                if (!$skpd) {
                    $skpd = Skpd::where('nama_skpd', 'LIKE', '%' . $targetNamaSkpd . '%')->first();
                }

                if (!$skpd) {
                    $this->command->warn("SKPD tidak ditemukan di database: '{$namaSkpd}' — dilewati.");
                    $skippedSkpd++;
                    continue;
                }

                foreach ($item['indikator'] as $ind) {
                    // Lewati indikator non-measurable (tanpa satuan)
                    if (empty($ind['satuan'])) {
                        continue;
                    }

                    $namaIndikator = trim($ind['nama_indikator'] ?? '');
                    if ($namaIndikator === '') {
                        continue;
                    }

                    // Cari di tabel master_indikators (kategori Penyelenggaraan, harus child / memiliki parent)
                    $masterIndikator = MasterIndikator::where('kategori_id', $kategori->id)
                        ->whereNotNull('parent_id')
                        ->whereRaw('LOWER(TRIM(nama_indikator)) = ?', [strtolower($namaIndikator)])
                        ->first();

                    if (!$masterIndikator) {
                        $this->command->warn("Indikator tidak ditemukan: '{$namaIndikator}' — dilewati.");
                        $skippedInd++;
                        continue;
                    }

                    // Update skpd_id pada master_indikator jika masih kosong
                    if (!$masterIndikator->skpd_id) {
                        $masterIndikator->updateQuietly(['skpd_id' => $skpd->id]);
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

                        // Buat/update record realisasi agar SKPD langsung memiliki daftar yang perlu diisi
                        // Jika sudah ada record dengan nilai realisasi selain '-', pertahankan nilainya
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
                            ]);
                            $totalRealisasi++;
                        }
                    }
                }
            }
        });

        $this->command->info("Selesai! {$totalTarget} target dan {$totalRealisasi} record realisasi baru berhasil di-seed.");
        if ($skippedSkpd > 0 || $skippedInd > 0) {
            $this->command->warn("Dilewati: {$skippedSkpd} SKPD, {$skippedInd} Indikator.");
        }
    }
}

