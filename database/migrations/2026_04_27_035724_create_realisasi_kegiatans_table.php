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
        Schema::create('realisasi_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renja_skpd_id')->constrained('renja_skpds')->cascadeOnDelete();
            $table->tinyInteger('triwulan')->comment('isi dengan 1, 2, 3, atau 4');
            $table->unsignedBigInteger('realisasi_keuangan')->default(0);
            $table->decimal('realisasi_fisik', 5, 2)->default(0)->comment('Persentase 0.00 - 100.00');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->unique(['renja_skpd_id', 'triwulan'], 'realisasi_renja_triwulan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_kegiatans');
    }
};
