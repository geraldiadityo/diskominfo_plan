<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Truncate existing seeded records so unique constraint change succeeds cleanly
        if (Schema::hasTable('progul_bukti_dukung')) {
            DB::table('progul_bukti_dukung')->delete();
        }
        if (Schema::hasTable('progul_realisasis')) {
            DB::table('progul_realisasis')->delete();
        }

        // 1. Add index on indikator_id so foreign key requirement remains satisfied
        Schema::table('progul_realisasis', function (Blueprint $table) {
            if (!Schema::hasIndex('progul_realisasis', 'progul_realisasis_indikator_id_index')) {
                $table->index('indikator_id');
            }
        });

        // 2. Drop old unique and columns, add triwulan and metrics
        Schema::table('progul_realisasis', function (Blueprint $table) {
            if (Schema::hasIndex('progul_realisasis', 'progul_realisasis_indikator_id_skpd_id_tahun_unique')) {
                $table->dropUnique('progul_realisasis_indikator_id_skpd_id_tahun_unique');
            }

            $columnsToDrop = [];
            if (Schema::hasColumn('progul_realisasis', 'target_tahunan')) {
                $columnsToDrop[] = 'target_tahunan';
            }
            if (Schema::hasColumn('progul_realisasis', 'nilai_realisasi')) {
                $columnsToDrop[] = 'nilai_realisasi';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            if (!Schema::hasColumn('progul_realisasis', 'triwulan')) {
                $table->unsignedTinyInteger('triwulan')->default(1)->after('tahun');
            }
            if (!Schema::hasColumn('progul_realisasis', 'target_fisik')) {
                $table->decimal('target_fisik', 8, 2)->nullable()->after('triwulan');
            }
            if (!Schema::hasColumn('progul_realisasis', 'target_keuangan')) {
                $table->decimal('target_keuangan', 20, 2)->nullable()->after('target_fisik');
            }
            if (!Schema::hasColumn('progul_realisasis', 'realisasi_fisik')) {
                $table->decimal('realisasi_fisik', 8, 2)->nullable()->after('target_keuangan');
            }
            if (!Schema::hasColumn('progul_realisasis', 'realisasi_keuangan')) {
                $table->decimal('realisasi_keuangan', 20, 2)->nullable()->after('realisasi_fisik');
            }

            if (!Schema::hasIndex('progul_realisasis', 'progul_realisasis_indikator_id_skpd_id_tahun_triwulan_unique')) {
                $table->unique(['indikator_id', 'skpd_id', 'tahun', 'triwulan']);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('progul_realisasis')) {
            DB::table('progul_realisasis')->delete();
        }

        Schema::table('progul_realisasis', function (Blueprint $table) {
            if (Schema::hasIndex('progul_realisasis', 'progul_realisasis_indikator_id_skpd_id_tahun_triwulan_unique')) {
                $table->dropUnique(['indikator_id', 'skpd_id', 'tahun', 'triwulan']);
            }

            $cols = ['triwulan', 'target_fisik', 'target_keuangan', 'realisasi_fisik', 'realisasi_keuangan'];
            $dropCols = array_filter($cols, fn ($c) => Schema::hasColumn('progul_realisasis', $c));
            if (!empty($dropCols)) {
                $table->dropColumn($dropCols);
            }

            if (!Schema::hasColumn('progul_realisasis', 'target_tahunan')) {
                $table->string('target_tahunan')->nullable()->after('tahun');
            }
            if (!Schema::hasColumn('progul_realisasis', 'nilai_realisasi')) {
                $table->string('nilai_realisasi')->nullable()->after('target_tahunan');
            }

            if (!Schema::hasIndex('progul_realisasis', 'progul_realisasis_indikator_id_skpd_id_tahun_unique')) {
                $table->unique(['indikator_id', 'skpd_id', 'tahun']);
            }

            if (Schema::hasIndex('progul_realisasis', 'progul_realisasis_indikator_id_index')) {
                $table->dropIndex(['indikator_id']);
            }
        });
    }
};
