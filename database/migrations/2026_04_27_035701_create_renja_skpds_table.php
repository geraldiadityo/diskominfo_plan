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
        Schema::create('renja_skpds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skpd_id')->constrained('skpds')->restrictOnDelete();
            $table->foreignId('sub_kegiatan_id')->constrained('program_sub_kegiatans')->restrictOnDelete();
            $table->year('tahun');
            $table->unsignedBigInteger('pagu_anggaran')->default(0);
            $table->string('target_kinerja')->nullable();
            $table->timestamps();

            $table->unique(['skpd_id', 'sub_kegiatan_id', 'tahun'], 'renja_skpd_tahun_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renja_skpds');
    }
};
