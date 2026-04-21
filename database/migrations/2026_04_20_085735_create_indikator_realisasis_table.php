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
        Schema::create('indikator_realisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('master_indikators')->cascadeOnDelete();
            $table->foreignId('skpd_id')->constrained('skpds')->cascadeOnDelete();
            $table->integer('tahun');
            $table->text('nilai_realisasi');
            $table->text('bukti_dokument')->nullable();
            $table->enum('status', ['DRAF', 'SUBMITTED', 'REJECTED', 'APPROVED'])->default('DRAF');
            $table->text('catatan_bappeda')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verfied_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indikator_realisasis');
    }
};
