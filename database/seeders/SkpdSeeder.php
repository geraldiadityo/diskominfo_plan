<?php

namespace Database\Seeders;

use App\Models\ProgramBidang;
use App\Models\Skpd;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkpdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = database_path('seeders/data/skpd_data.json');

        if (file_exists($file)) {
            $items = json_decode(file_get_contents($file), true);
        } else {
            $items = $this->getDefaultData();
        }

        if (empty($items)) {
            $this->command->error("Data SKPD kosong atau format file tidak valid.");
            return;
        }

        $this->command->info("Memulai seeder SKPD dan Mapping Urusan/Bidang...");

        // Map kode_lengkap bidang ke ID ProgramBidang
        $bidangMap = ProgramBidang::pluck('id', 'kode_lengkap')->toArray();

        if (empty($bidangMap)) {
            $this->command->warn("Peringatan: Tabel program_bidangs masih kosong. Pastikan NomenklaturSeeder sudah dijalankan jika ingin mapping bidang/urusan terisi otomatis.");
        }

        $now = now();
        $payload = [];
        $mappedCount = 0;

        foreach ($items as $item) {
            $kodeSkpd = trim($item['kode_skpd'] ?? '');
            $namaSkpd = trim($item['nama_skpd'] ?? '');
            $kodeBidang = isset($item['kode_bidang']) && trim((string)$item['kode_bidang']) !== ''
                ? trim((string)$item['kode_bidang'])
                : null;

            if ($kodeSkpd === '' || $namaSkpd === '') {
                continue;
            }

            $bidangId = ($kodeBidang && isset($bidangMap[$kodeBidang]))
                ? $bidangMap[$kodeBidang]
                : null;

            if ($bidangId !== null) {
                $mappedCount++;
            }

            $payload[] = [
                'kode_skpd'  => $kodeSkpd,
                'nama_skpd'  => $namaSkpd,
                'bidang_id'  => $bidangId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($payload)) {
            $this->command->warn("Tidak ada data SKPD valid yang dapat diproses.");
            return;
        }

        // Lakukan Upsert berdasarkan kolom unik 'kode_skpd'
        // Jika sudah ada, update 'nama_skpd', 'bidang_id', dan 'updated_at'
        DB::transaction(function () use ($payload) {
            foreach (array_chunk($payload, 100) as $chunk) {
                Skpd::upsert(
                    $chunk,
                    ['kode_skpd'],
                    ['nama_skpd', 'bidang_id', 'updated_at']
                );
            }
        });

        $total = count($payload);
        $this->command->info("Selesai! Berhasil melakukan upsert {$total} SKPD ({$mappedCount} SKPD ter-mapping ke bidang urusan).");
    }

    /**
     * Fallback data SKPD jika file database/seeders/data/skpd_data.json tidak tersedia.
     */
    protected function getDefaultData(): array
    {
        return [
            ['kode_skpd' => 'DISKOMINFO', 'nama_skpd' => 'DInas Komunikasi, Infomatika Dan Statistik', 'kode_bidang' => '2.16'],
            ['kode_skpd' => 'BPS', 'nama_skpd' => 'Badan Pusat Statistik', 'kode_bidang' => '2.20'],
            ['kode_skpd' => 'BAPPERIDA', 'nama_skpd' => 'BAPPERIDA', 'kode_bidang' => '5.1'],
            ['kode_skpd' => 'SETDA', 'nama_skpd' => 'Sekretariat Daerah', 'kode_bidang' => '4.1'],
            ['kode_skpd' => 'SETDPR', 'nama_skpd' => 'Sekretaria DPRD', 'kode_bidang' => '4.2'],
            ['kode_skpd' => 'Inpektorat', 'nama_skpd' => 'Inspektorat Daerah', 'kode_bidang' => '6.1'],
            ['kode_skpd' => 'DISDIK', 'nama_skpd' => 'Dinas Pendidikan', 'kode_bidang' => '1.1'],
            ['kode_skpd' => 'DINKES', 'nama_skpd' => 'Dinas Kesehatan', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PUPR', 'nama_skpd' => 'Dinas Pekerjaan Umum dan Perumahan Rakyat', 'kode_bidang' => '1.3'],
            ['kode_skpd' => 'SATPOLPPDAMKAR', 'nama_skpd' => 'Satuan Polisi Pamong Praja dan Pemadam Kebakaran', 'kode_bidang' => '1.5'],
            ['kode_skpd' => 'DINSOS', 'nama_skpd' => 'Dinas Sosial', 'kode_bidang' => '1.6'],
            ['kode_skpd' => 'DPPPAPPKB', 'nama_skpd' => 'Dinas Pemberdayaan Perempuan Perlindungan Anak dan Pengendalian Penduduk KB', 'kode_bidang' => '2.8'],
            ['kode_skpd' => 'DPKPP', 'nama_skpd' => 'Dinas Pertanian Ketahanan Pangan dan Perikanan', 'kode_bidang' => '3.27'],
            ['kode_skpd' => 'DLH', 'nama_skpd' => 'Dinas Lingkungan Hidup', 'kode_bidang' => '2.11'],
            ['kode_skpd' => 'DISKDUKCAPIL', 'nama_skpd' => 'Dinas Kependudukan dan Pencatatan Sipil', 'kode_bidang' => '2.12'],
            ['kode_skpd' => 'DPMD', 'nama_skpd' => 'Dinas Pemberdayaan Masyarakat dan Desa', 'kode_bidang' => '2.13'],
            ['kode_skpd' => 'DISHUB', 'nama_skpd' => 'Dinas Perhubungan', 'kode_bidang' => '2.15'],
            ['kode_skpd' => 'DISKOPNAKER', 'nama_skpd' => 'Dinas Koperasi Usaha Kecil Menengah dan Tenaga Kerja', 'kode_bidang' => '2.17'],
            ['kode_skpd' => 'DPMPTSP', 'nama_skpd' => 'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu', 'kode_bidang' => '2.18'],
            ['kode_skpd' => 'DISPERPUSIP', 'nama_skpd' => 'Dinas Perpustakaan dan Kearsipan', 'kode_bidang' => '2.23'],
            ['kode_skpd' => 'DISBUDPAR', 'nama_skpd' => 'Dinas Kebudayaan, Pariwisata, Kepemudaan dan Olahraga', 'kode_bidang' => '2.22'],
            ['kode_skpd' => 'DISPERINDAG', 'nama_skpd' => 'Dinas Perindustrian dan Perdagangan', 'kode_bidang' => '3.30'],
            ['kode_skpd' => 'BAKEUDA', 'nama_skpd' => 'Badan Keuangan dan Aset Daerah', 'kode_bidang' => '5.2'],
            ['kode_skpd' => 'BAPENDA', 'nama_skpd' => 'Badan Pendapatan Daerah', 'kode_bidang' => '5.2'],
            ['kode_skpd' => 'BKPSDM', 'nama_skpd' => 'Badan Kepegawaian dan Pengembangan SDM', 'kode_bidang' => '5.3'],
            ['kode_skpd' => 'BPBD', 'nama_skpd' => 'Badan Penanggulangan Bencana Daerah', 'kode_bidang' => '1.5'],
            ['kode_skpd' => 'KESBANGPOL', 'nama_skpd' => 'Badan Kesatuan Bangsa dan Politik', 'kode_bidang' => '8.1'],
            ['kode_skpd' => 'KECHR', 'nama_skpd' => 'Kecamatan Hamparan Rawang', 'kode_bidang' => '7.1'],
            ['kode_skpd' => 'KECPB', 'nama_skpd' => 'Kecamatan Pesisir Bukit', 'kode_bidang' => '7.1'],
            ['kode_skpd' => 'KECKD', 'nama_skpd' => 'Kecamatan Kumun Debai', 'kode_bidang' => '7.1'],
            ['kode_skpd' => 'KECTK', 'nama_skpd' => 'Kecamatan Tanah Kampung', 'kode_bidang' => '7.1'],
            ['kode_skpd' => 'KECPT', 'nama_skpd' => 'Kecamatan Pondok Tinggi', 'kode_bidang' => '7.1'],
            ['kode_skpd' => 'KECSB', 'nama_skpd' => 'Kecamatan Sungai Bungkal', 'kode_bidang' => '7.1'],
            ['kode_skpd' => 'KECSPN', 'nama_skpd' => 'Kecamatan Sungai Penuh', 'kode_bidang' => '7.1'],
            ['kode_skpd' => 'KECKB', 'nama_skpd' => 'Kecamatan Koto Baru', 'kode_bidang' => '7.1'],
            ['kode_skpd' => 'RSMAYJEN', 'nama_skpd' => 'Rumah Sakit Mayjen H. Thalib Kota Sungai Penuh', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'RSBAKRI', 'nama_skpd' => 'Rumah Sakit H. Bakri Kota Sungai Penuh', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PKMTR', 'nama_skpd' => 'Puskesmas Tanjung Rawang', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PKMTK', 'nama_skpd' => 'Puskesmas Tanah Kampung', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PKMSPN', 'nama_skpd' => 'Puskesmas Sungai Penuh', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PKMSL', 'nama_skpd' => 'Puskesmas Sungai Liuk', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PKMSB', 'nama_skpd' => 'Puskesmas Sungai Bungkal', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PKMRWG', 'nama_skpd' => 'Puskesmas Rawang', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PKMPT', 'nama_skpd' => 'Puskesmas Pondo Tinggi', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PKMKD', 'nama_skpd' => 'Puskesmas Kumun Debai', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PKMKL', 'nama_skpd' => 'Puskesmas Koto Lolo', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PKMKB', 'nama_skpd' => 'Puskesmas Koto Baru', 'kode_bidang' => '1.2'],
            ['kode_skpd' => 'PKMDG', 'nama_skpd' => 'Puskesmas Desa Gedang', 'kode_bidang' => '1.2'],
        ];
    }
}

