<?php

namespace Database\Seeders;

use App\Models\Rekening;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RekeningSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan nama file sesuai dengan file CSV bersih Anda
        $csvPath = database_path('seeders/data/akun_pendapatan_belanja_cleaned.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("File CSV tidak ditemukan di: {$csvPath}");
            return;
        }

        $csvFile = fopen($csvPath, 'r');

        // Lewati baris pertama karena itu adalah header ("kode,uraian")
        fgetcsv($csvFile);

        $rekeningCache = []; // Cache untuk menyimpan ID parent (mempercepat proses seeding)

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($csvFile)) !== false) {
                // Pastikan baris memiliki minimal 2 kolom
                if (!isset($row[0]) || !isset($row[1])) continue;

                $kode = trim($row[0]);

                // MENGATASI BUG CSV: Bersihkan enter/newline (Ganti \r atau \n dengan spasi)
                // agar tampilan di Filament tidak berantakan
                $uraian = trim(str_replace(["\r", "\n"], ' ', $row[1]));

                if (empty($kode) || empty($uraian)) continue;

                // 1. Tentukan Level berdasarkan jumlah Titik (.)
                $level = substr_count($kode, '.') + 1;

                // 2. Cari Parent ID
                $parentId = null;
                if ($level > 1) {
                    $segments = explode('.', $kode);
                    array_pop($segments); // Buang ekornya untuk mendapatkan kode parent
                    $parentCode = implode('.', $segments);

                    // Ambil ID Parent dari Cache (Jika root/parent teratas tidak ada di CSV, set null)
                    $parentId = $rekeningCache[$parentCode] ?? null;
                }

                // 3. Simpan ke Database
                $rekening = Rekening::create([
                    'parent_id' => $parentId,
                    'kode'      => $kode,
                    'uraian'    => $uraian,
                    'level'     => $level,
                ]);

                // 4. Simpan ke Cache Array untuk anak-anaknya nanti
                $rekeningCache[$kode] = $rekening->id;
            }

            DB::commit();
            $this->command->info('Data Rekening berhasil di-seed dengan sempurna!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Gagal seeding: ' . $e->getMessage());
        }

        fclose($csvFile);
    }
}
