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
        Schema::create('bukti_fisik_realisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_kegiatan_id')->constrained('realisasi_kegiatans')->cascadeOnDelete();
            $table->string('nama_indikator');
            $table->string('target');
            $table->string('realisasi')->nullable();
            $table->string('file_bukti')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bukti_fisik_realisasis');
    }
};
