<?php

namespace Database\Seeders;

use App\Models\ProgramBidang;
use App\Models\ProgramKegiatan;
use App\Models\ProgramProgram;
use App\Models\ProgramSubKegiatan;
use App\Models\ProgramUrusan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NomenklaturSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $file = database_path('seeders/data/nomenklatur_clean.csv');

        if (!file_exists($file)) {
            $this->command->error("File CSV tidak di temukan di: {$file}");
            return;
        }

        $this->command->info("Memulai import data Nomenklatur");

        $handle = fopen($file, "r");

        $isHeader = true;

        $lastUrusanId = null;
        $lastBidangId = null;
        $lastProgramId = null;
        $lastKegiatanId = null;

        $rowCount = 0;

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle, 4000, ',')) !== FALSE) {
                if ($isHeader) {
                    $isHeader = false;
                    continue;
                }

                $kodeUrusan = trim($data[0] ?? '');
                $kodeBidang = trim($data[1] ?? '');
                $kodeProgram = trim($data[2] ?? '');
                $kodeKegiatan = trim($data[3] ?? '');
                $kodeSubKeg = trim($data[4] ?? '');
                $nomenklatur = trim($data[5] ?? '');
                $kinerja = trim($data[6] ?? '') !== '' ? trim($data[6]) : null;
                $indikator = trim($data[7] ?? '') !== '' ? trim($data[7]) : null;
                $satuan = trim($data[8] ?? '') !== '' ? trim($data[8]) : null;

                if ($kodeUrusan === '' && $nomenklatur === '') {
                    continue;
                }

                $level = 0;
                if ($kodeUrusan !== '') $level = 1;
                if ($kodeBidang !== '') $level = 2;
                if ($kodeProgram !== '') $level = 3;
                if ($kodeKegiatan !== '') $level = 4;
                if ($kodeSubKeg !== '') $level = 5;

                if ($level === 1) {
                    $urusan = ProgramUrusan::firstOrCreate(
                        ['kode' => $kodeUrusan],
                        ['nomenklatur' => $nomenklatur]
                    );
                    $lastUrusanId = $urusan->id;
                } else if ($level === 2 && $lastUrusanId) {
                    $bidang = ProgramBidang::firstOrCreate(
                        ['urusan_id' => $lastUrusanId, 'kode' => $kodeBidang],
                        ['nomenklatur' => $nomenklatur]
                    );

                    $lastBidangId = $bidang->id;
                } else if ($level === 3 && $lastBidangId) {
                    $program = ProgramProgram::firstOrCreate(
                        ['bidang_id' => $lastBidangId, 'kode' => $kodeProgram],
                        ['nomenklatur' => $nomenklatur]
                    );
                    $lastProgramId = $program->id;
                } else if ($level === 4 && $lastProgramId) {
                    $kegiatan = ProgramKegiatan::firstOrCreate(
                        ['program_id' => $lastProgramId, 'kode' => $kodeKegiatan],
                        ['nomenklatur' => $nomenklatur]
                    );
                    $lastKegiatanId = $kegiatan->id;
                } else if ($level === 5 && $lastKegiatanId) {
                    $subKeg = ProgramSubKegiatan::firstOrCreate(
                        ['kegiatan_id' => $lastKegiatanId, 'kode' => $kodeSubKeg],
                        [
                            'nomenklatur' => $nomenklatur,
                            'kinerja' => $kinerja,
                            'indikator' => $indikator,
                            'satuan' => $satuan
                        ]
                    );
                }

                $rowCount++;

                if ($rowCount % 500 === 0) {
                    $this->command->info("Berhasil Memproses {$rowCount} baris...");
                }
            }

            fclose($handle);
            DB::commit();

            $this->command->info("Selesai! Total {$rowCount} baris data berhasil di import.");
        } catch (\Exception $e) {
            DB::rollBack();
            if (is_resource($handle)) {
                fclose($handle);
            }

            $this->command->error('Terjadi Error saat import: ' . $e->getMessage());
        }
    }
}
