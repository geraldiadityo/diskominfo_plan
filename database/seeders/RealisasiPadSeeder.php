<?php

namespace Database\Seeders;

use App\Models\Rekening;
use App\Models\Skpd;
use App\Models\TargetPendapatan;
use App\Models\TransaksiPendapatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RealisasiPadSeeder extends Seeder
{
    /**
     * Tahun anggaran yang di-seed
     */
    const TAHUN = 2026;

    /**
     * Tanggal realisasi per laporan (23 Oktober 2026)
     */
    const TANGGAL_REALISASI = '2026-10-23';

    /**
     * Prefix kode rekening untuk pendapatan transfer dan lain-lain
     */
    const PREFIX_TRANSFER = '4.2';
    const PREFIX_LAIN_LAIN = '4.3';

    /**
     * Kode SKPD Badan Keuangan dan Aset Daerah
     */
    const KODE_BAKEUDA = 'BAKEUDA';

    public function run(): void
    {
        $xlsxPath = database_path('seeders/data/pendapatan_daerah.xlsx');

        if (!file_exists($xlsxPath)) {
            $this->command->error("File Excel tidak ditemukan di: {$xlsxPath}");
            $this->command->warn("Pastikan file pendapatan_daerah.xlsx sudah disalin ke database/seeders/data/");
            return;
        }

        $this->command->info('Memulai seeding Realisasi PAD Kota Sungai Penuh (Tahun ' . self::TAHUN . ')...');

        $rows = $this->parseExcel($xlsxPath);
        if (empty($rows)) {
            $this->command->error("Gagal membaca atau worksheet Excel kosong.");
            return;
        }

        // Cache lookup data
        $skpdMap = Skpd::pluck('id', 'kode_skpd')->toArray(); // [kode_skpd => id]
        $rekeningL6Map = Rekening::where('level', 6)->pluck('id', 'kode')->toArray(); // [kode => id]
        $rekeningAllMap = Rekening::pluck('id', 'kode')->toArray(); // [kode => id]
        $rekeningL6ByUraian = Rekening::where('level', 6)
            ->get()
            ->mapWithKeys(fn($r) => [strtolower(trim($r->uraian)) => $r->id])
            ->toArray();

        $stats = [
            'seeded'          => 0,
            'skipped_dup'     => 0,
            'skipped_no_data' => 0,
            'skipped_no_match'=> 0,
        ];

        DB::beginTransaction();

        try {
            $lastKodeL6 = null;
            $lastUraianL6 = null;

            foreach ($rows as $row) {
                $kodeRekening = trim($row['kode'] ?? '');
                $uraian       = trim($row['uraian'] ?? '');
                $targetRaw    = $row['target'] ?? null;
                $realisasiRaw = $row['realisasi'] ?? null;
                $dinasRaw     = trim($row['dinas'] ?? '');

                // Tentukan level dari kode rekening
                $level = $kodeRekening !== '' ? (substr_count($kodeRekening, '.') + 1) : 0;

                // Deteksi baris BLUD (kode di kolom A kosong, tetapi ada uraian, dinas, dan nilai)
                $isBludRow = (
                    $kodeRekening === ''
                    && $dinasRaw !== ''
                    && ($targetRaw !== null || $realisasiRaw !== null)
                    && $lastKodeL6 !== null
                );

                if ($isBludRow) {
                    $kodeRekening = $lastKodeL6;
                    $level = 6;
                } elseif ($level !== 6) {
                    continue; // Skip baris bukan level 6
                } else {
                    // Jika baris level 6 ini adalah header agregat BLUD (dinas kosong dan di bawah 4.1),
                    // simpan kode untuk sub-unit di bawahnya dan skip baris agregat ini
                    if ($dinasRaw === '' && !str_starts_with($kodeRekening, self::PREFIX_TRANSFER) && !str_starts_with($kodeRekening, self::PREFIX_LAIN_LAIN)) {
                        $lastKodeL6 = $kodeRekening;
                        $lastUraianL6 = $uraian;
                        continue;
                    }
                    $lastKodeL6 = $kodeRekening;
                    $lastUraianL6 = $uraian;
                }

                // Cek apakah target dan realisasi keduanya kosong
                $targetVal    = $this->parseNumber($targetRaw);
                $realisasiVal = $this->parseNumber($realisasiRaw);

                if ($targetVal === null && $realisasiVal === null) {
                    $stats['skipped_no_data']++;
                    continue;
                }

                // Tentukan SKPD pengelola
                $skpdId = $this->resolveSkpdId($kodeRekening, $dinasRaw, $skpdMap);
                if ($skpdId === null) {
                    $this->command->warn("  [SKIP] Dinas tidak ditemukan: '{$dinasRaw}' untuk rekening {$kodeRekening}");
                    $stats['skipped_no_match']++;
                    continue;
                }

                // Cari atau buat rekening di DB
                $rekeningId = $this->resolveRekeningId(
                    $kodeRekening,
                    $uraian,
                    $rekeningL6Map,
                    $rekeningAllMap,
                    $rekeningL6ByUraian
                );

                if ($rekeningId === null) {
                    $this->command->warn("  [SKIP] Rekening tidak ditemukan di DB: {$kodeRekening} ({$uraian})");
                    $stats['skipped_no_match']++;
                    continue;
                }

                // Cek idempoten: apakah TargetPendapatan sudah ada?
                $existingTarget = TargetPendapatan::where('skpd_id', $skpdId)
                    ->where('rekening_id', $rekeningId)
                    ->where('tahun', self::TAHUN)
                    ->first();

                if ($existingTarget) {
                    $stats['skipped_dup']++;
                    continue;
                }

                // Buat TargetPendapatan baru
                $target = TargetPendapatan::create([
                    'skpd_id'           => $skpdId,
                    'rekening_id'       => $rekeningId,
                    'tahun'             => self::TAHUN,
                    'target_anggaran'   => (int) round($targetVal ?? 0),
                    'keterangan_target' => 'Seed awal dari laporan realisasi PAD 23 Oktober 2026',
                ]);

                // Buat TransaksiPendapatan jika ada nilai realisasi
                if ($realisasiVal !== null && $realisasiVal > 0) {
                    TransaksiPendapatan::create([
                        'target_pendapatan_id' => $target->id,
                        'tanggal_setor'        => self::TANGGAL_REALISASI,
                        'jumlah_setor'         => (int) round($realisasiVal),
                        'bukti_dukung'         => null,
                        'keterangan'           => 'Seed awal dari laporan realisasi PAD 23 Oktober 2026',
                        'is_verif'             => true,
                    ]);
                }

                $stats['seeded']++;
                $skpdKode = array_search($skpdId, $skpdMap) ?: "ID:{$skpdId}";
                $this->command->line(
                    "  [OK] Rek: {$kodeRekening} | SKPD: {$skpdKode} | Target: Rp "
                    . number_format($targetVal ?? 0, 0, ',', '.')
                    . " | Real: Rp " . number_format($realisasiVal ?? 0, 0, ',', '.')
                );
            }

            DB::commit();

            // Clear cache dashboard agar data ter-refresh
            $this->clearDashboardCache();

            $this->command->info('');
            $this->command->info('=== HASIL SEEDING REALISASI PAD ===');
            $this->command->info("  Berhasil di-seed : {$stats['seeded']} rekening");
            $this->command->info("  Skip duplikat    : {$stats['skipped_dup']} rekening");
            $this->command->info("  Skip tanpa data  : {$stats['skipped_no_data']} rekening");
            $this->command->info("  Skip no match    : {$stats['skipped_no_match']} rekening");

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error("Gagal melakukan seeding: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse file Excel .xlsx langsung tanpa library pihak ketiga.
     */
    private function parseExcel(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException("Tidak dapat membuka file: {$path}");
        }

        // Shared strings
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $xml = simplexml_load_string($ssXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // Worksheet sheet1.xml
        $wsXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($wsXml === false) {
            throw new \RuntimeException("Worksheet sheet1.xml tidak ditemukan di dalam arsip Excel.");
        }

        $xml = simplexml_load_string($wsXml);
        if (!$xml || !isset($xml->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowIndex = (int) $row['r'];
            if ($rowIndex < 6) continue; // Skip header baris 1 s/d 5

            $cells = [];
            foreach ($row->c as $c) {
                $ref  = (string) $c['r'];
                $type = (string) $c['t'];
                $val  = isset($c->v) ? (string) $c->v : null;

                if ($val !== null && $type === 's') {
                    $val = $sharedStrings[(int) $val] ?? $val;
                }

                preg_match('/^([A-Z]+)/', $ref, $m);
                $cells[$m[1] ?? ''] = $val;
            }

            $rows[] = [
                'r'         => $rowIndex,
                'kode'      => trim($cells['A'] ?? ''),
                'uraian'    => trim($cells['B'] ?? ''),
                'target'    => $cells['C'] ?? null,
                'realisasi' => $cells['D'] ?? null,
                'dinas'     => trim($cells['G'] ?? ''),
            ];
        }

        return $rows;
    }

    /**
     * Parse nilai angka dari string Excel.
     */
    private function parseNumber(?string $val): ?float
    {
        if ($val === null || $val === '' || $val === '#DIV/0!') {
            return null;
        }
        $num = (float) $val;
        return is_nan($num) ? null : $num;
    }

    /**
     * Resolusi SKPD ID dari kode rekening dan nama dinas.
     */
    private function resolveSkpdId(string $kodeRekening, string $dinasRaw, array $skpdMap): ?int
    {
        // Khusus pendapatan transfer (4.2.*) dan lain-lain pendapatan (4.3.*) dinasnya selalu BAKEUDA
        if (
            str_starts_with($kodeRekening, self::PREFIX_TRANSFER) ||
            str_starts_with($kodeRekening, self::PREFIX_LAIN_LAIN)
        ) {
            return $skpdMap[self::KODE_BAKEUDA] ?? null;
        }

        $kodeSkpd = $this->mapDinasToSkpdKode($dinasRaw);
        if ($kodeSkpd === null) {
            return null;
        }

        return $skpdMap[$kodeSkpd] ?? null;
    }

    /**
     * Mapping nama dinas dari Excel ke kode_skpd.
     */
    private function mapDinasToSkpdKode(string $dinas): ?string
    {
        $dinas = strtoupper(trim($dinas));

        $mapping = [
            'BADAN PENDAPATAN DAERAH'                                        => 'BAPENDA',
            'BADAN KEUANGAN DAN ASET DAERAH'                                 => 'BAKEUDA',
            'DINAS KESEHATAN'                                                 => 'DINKES',
            'DINAS LINGKUNGAN HIDUP'                                          => 'DLH',
            'DINAS PERHUBUNGAN'                                               => 'DISHUB',
            'DINAS PEKERJAAN UMUM DAN PERUMAHAN RAKYAT'                      => 'PUPR',
            'DINAS PERDAGANGAN DAN PERINDUSTRIAN'                            => 'DISPERINDAG',
            'DINAS PERTANIAN, KETAHANAN PANGAN DAN PERIKANAN'                => 'DPKPP',
            'DINAS PERTANIAN KETAHANAN PANGAN DAN PERIKANAN'                 => 'DPKPP',
            'SETDA BAGIAN UMUM'                                               => 'SETDA',
            'DISBUDPARPORA'                                                   => 'DISBUDPAR',
            'RUMAH SAKIT UMUM H.A THALIB'                                    => 'RSMAYJEN',
            'RUMAH SAKIT UMUM HA THALIB'                                     => 'RSMAYJEN',
            'PUSKESMAS TANAH KAMPUNG'                                         => 'PKMTK',
            'PUSKESMAS KOTO BARU'                                             => 'PKMKB',
            'PUSKESMAS DESA GEDANG'                                           => 'PKMDG',
            'PUSKESMAS RAWANG'                                                => 'PKMRWG',
            'PUSKESMAS KUMUN'                                                 => 'PKMKD',
            'PUSKESMAS SUNGAI PENUH'                                          => 'PKMSPN',
        ];

        return $mapping[$dinas] ?? null;
    }

    /**
     * Resolusi Rekening ID:
     * 1. Exact match di DB (level 6)
     * 2. Normalisasi format digit (misal padding 3 digit pada segmen ke-5)
     * 3. Match berdasarkan nama uraian rekening
     * 4. Jika belum ada di DB tapi parent ada, buat rekening baru
     */
    private function resolveRekeningId(
        string $kode,
        string $uraian,
        array &$rekeningL6Map,
        array &$rekeningAllMap,
        array &$rekeningL6ByUraian
    ): ?int {
        $kode = rtrim(trim($kode), '.');

        // 1. Exact match
        if (isset($rekeningL6Map[$kode])) {
            return $rekeningL6Map[$kode];
        }

        // 2. Normalisasi segment
        $parts = explode('.', $kode);
        if (count($parts) === 6) {
            // Padding 3 digit pada segmen ke-5: 4.1.01.09.01.00001 -> 4.1.01.09.001.00001
            $c1 = sprintf('%s.%s.%s.%s.%03d.%s', $parts[0], $parts[1], $parts[2], $parts[3], (int)$parts[4], $parts[5]);
            if (isset($rekeningL6Map[$c1])) {
                return $rekeningL6Map[$c1];
            }

            // Padding 4 digit pada segmen ke-5
            $c2 = sprintf('%s.%s.%s.%s.%04d.%s', $parts[0], $parts[1], $parts[2], $parts[3], (int)$parts[4], $parts[5]);
            if (isset($rekeningL6Map[$c2])) {
                return $rekeningL6Map[$c2];
            }

            // Padding 3 digit segmen ke-5 dan 4 digit segmen ke-6
            $c3 = sprintf('%s.%s.%s.%s.%03d.%04d', $parts[0], $parts[1], $parts[2], $parts[3], (int)$parts[4], (int)$parts[5]);
            if (isset($rekeningL6Map[$c3])) {
                return $rekeningL6Map[$c3];
            }
        }

        // 3. Match berdasarkan Uraian (Level 6)
        $cleanUraian = strtolower(trim($uraian));
        if ($cleanUraian !== '' && isset($rekeningL6ByUraian[$cleanUraian])) {
            return $rekeningL6ByUraian[$cleanUraian];
        }

        // 4. Jika belum ada di tabel rekenings, cari parent-nya dan buat otomatis
        if (count($parts) === 6) {
            array_pop($parts);
            $parentCode = implode('.', $parts);

            // Coba cari parent dengan kode exact atau normalisasi
            $parentId = $rekeningAllMap[$parentCode] ?? null;
            if (!$parentId && count($parts) === 5) {
                $normParent = sprintf('%s.%s.%s.%s.%03d', $parts[0], $parts[1], $parts[2], $parts[3], (int)$parts[4]);
                $parentId = $rekeningAllMap[$normParent] ?? null;
            }

            if ($parentId) {
                $newRekening = Rekening::create([
                    'parent_id' => $parentId,
                    'kode'      => $kode,
                    'uraian'    => $uraian,
                    'level'     => 6,
                ]);

                // Update cache lokal
                $rekeningL6Map[$kode] = $newRekening->id;
                $rekeningAllMap[$kode] = $newRekening->id;
                $rekeningL6ByUraian[$cleanUraian] = $newRekening->id;

                return $newRekening->id;
            }
        }

        return null;
    }

    /**
     * Hapus cache terkait dashboard pendapatan.
     */
    private function clearDashboardCache(): void
    {
        $tahun = self::TAHUN;
        Cache::forget("dashboard:target_pendapatan_total:{$tahun}");
        Cache::forget("dashboard:chart_pendapatan:{$tahun}");
        Cache::forget("dashboard:skpd_pendapatan_summary:{$tahun}");
        Cache::forget("dashboard:skpd_pad_summary:{$tahun}");
        Cache::forget("dashboard:pendapatan_tree:{$tahun}");
        Cache::forget('rekenings:all');
    }
}

