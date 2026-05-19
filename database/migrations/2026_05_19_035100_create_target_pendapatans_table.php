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
        Schema::create('target_pendapatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skpd_id')->constrained('skpds')->restrictOnDelete();
            $table->foreignId('rekening_id')->constrained('rekenings')->restrictOnDelete();
            $table->year('tahun');
            $table->unsignedBigInteger('target_anggaran')->default(0);
            $table->string('keterangan_target')->nullable();
            $table->timestamps();

            $table->unique(['skpd_id', 'rekening_id', 'tahun'], 'target_pendapatan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_pendapatans');
    }
};
