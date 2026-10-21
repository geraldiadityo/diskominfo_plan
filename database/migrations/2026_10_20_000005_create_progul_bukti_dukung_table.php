<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progul_bukti_dukung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_id')
                  ->constrained('progul_realisasis')
                  ->cascadeOnDelete();
            $table->string('keterangan')->nullable();
            $table->string('file_bukti');
            $table->enum('status_verifikasi', [
                'belum_diupload',
                'menunggu_verifikasi',
                'disetujui',
                'ditolak',
            ])->default('menunggu_verifikasi');
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progul_bukti_dukung');
    }
};

