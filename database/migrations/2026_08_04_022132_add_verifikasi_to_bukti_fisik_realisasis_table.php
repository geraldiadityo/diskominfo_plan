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
        Schema::table('bukti_fisik_realisasis', function (Blueprint $table) {
            $table->string('status_verifikasi')->default('belum_diupload'); // belum_diupload, menunggu_verifikasi, disetujui, ditolak
            $table->text('catatan_verifikasi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bukti_fisik_realisasis', function (Blueprint $table) {
            $table->dropColumn(['status_verifikasi', 'catatan_verifikasi']);
        });
    }
};
