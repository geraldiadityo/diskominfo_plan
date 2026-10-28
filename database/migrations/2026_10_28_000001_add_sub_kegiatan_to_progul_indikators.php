<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progul_indikators', function (Blueprint $table) {
            if (!Schema::hasColumn('progul_indikators', 'sub_kegiatan_id')) {
                $table->foreignId('sub_kegiatan_id')
                      ->nullable()
                      ->after('program_id')
                      ->constrained('program_sub_kegiatans')
                      ->nullOnDelete();
            }

            if (!Schema::hasColumn('progul_indikators', 'rincian')) {
                $table->text('rincian')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('progul_indikators', function (Blueprint $table) {
            if (Schema::hasColumn('progul_indikators', 'sub_kegiatan_id')) {
                $table->dropForeign(['sub_kegiatan_id']);
                $table->dropColumn('sub_kegiatan_id');
            }

            if (Schema::hasColumn('progul_indikators', 'rincian')) {
                $table->dropColumn('rincian');
            }
        });
    }
};
