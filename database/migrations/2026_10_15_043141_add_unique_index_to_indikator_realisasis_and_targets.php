<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('indikator_realisasis', function (Blueprint $table) {
            $table->unique(['indikator_id', 'skpd_id', 'tahun'], 'indikator_skpd_tahun_unique');
        });

        Schema::table('indikator_targets', function (Blueprint $table) {
            $table->unique(['indikator_id', 'tahun'], 'indikator_tahun_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indikator_realisasis', function (Blueprint $table) {
            $table->dropUnique('indikator_skpd_tahun_unique');
        });

        Schema::table('indikator_targets', function (Blueprint $table) {
            $table->dropUnique('indikator_tahun_unique');
        });
    }
};
