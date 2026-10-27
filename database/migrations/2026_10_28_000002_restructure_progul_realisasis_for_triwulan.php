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
        DB::table('progul_bukti_dukung')->delete();
        DB::table('progul_realisasis')->delete();

        // 1. Add index on indikator_id so foreign key requirement remains satisfied
        Schema::table('progul_realisasis', function (Blueprint $table) {
            $table->index('indikator_id');
        });

        // 2. Drop old unique and columns, add triwulan and metrics
        Schema::table('progul_realisasis', function (Blueprint $table) {
            $table->dropUnique('progul_realisasis_indikator_id_skpd_id_tahun_unique');
            $table->dropColumn(['target_tahunan', 'nilai_realisasi']);
            
            $table->unsignedTinyInteger('triwulan')->default(1)->after('tahun');
            $table->decimal('target_fisik', 8, 2)->nullable()->after('triwulan');
            $table->decimal('target_keuangan', 20, 2)->nullable()->after('target_fisik');
            $table->decimal('realisasi_fisik', 8, 2)->nullable()->after('target_keuangan');
            $table->decimal('realisasi_keuangan', 20, 2)->nullable()->after('realisasi_fisik');

            $table->unique(['indikator_id', 'skpd_id', 'tahun', 'triwulan']);
        });
    }

    public function down(): void
    {
        Schema::table('progul_realisasis', function (Blueprint $table) {
            $table->dropUnique(['indikator_id', 'skpd_id', 'tahun', 'triwulan']);
            $table->dropColumn(['triwulan', 'target_fisik', 'target_keuangan', 'realisasi_fisik', 'realisasi_keuangan']);
            
            $table->string('target_tahunan')->nullable()->after('tahun');
            $table->string('nilai_realisasi')->nullable()->after('target_tahunan');
            $table->unique(['indikator_id', 'skpd_id', 'tahun']);
            $table->dropIndex(['indikator_id']);
        });
    }
};

